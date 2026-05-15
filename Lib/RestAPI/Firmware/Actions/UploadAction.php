<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

namespace Modules\ModuleAutoprovision\Lib\RestAPI\Firmware\Actions;

use MikoPBX\Core\System\SystemMessages;
use MikoPBX\PBXCoreREST\Lib\PBXApiResult;
use Modules\ModuleAutoprovision\Lib\RestAPI\Firmware\Repository;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovisionFirmware;

/**
 * Registers a firmware blob that the client has already pushed to Core's
 * chunked /pbxcore/api/v3/files:upload endpoint (category=autoprovision-firmware).
 *
 * Why no multipart handler here: Core already does Resumable.js chunking, hash
 * checks, and the temp file lookup at /storage/usbdisk1/mikopbx/tmp/www_cache/
 * upload_cache/<file_id>/. We just sanity-check the resulting blob, hash it,
 * and move it into <moduleDir>/firmware/<vendor>/<filename>.
 *
 * Concurrency: the (compute total size → check cap → move into place → insert
 * DB row) sequence runs under flock(LOCK_EX) on Repository::lockFile() so two
 * parallel uploads can't both squeeze past the 300 MB cap. The lock is held
 * until the DB write completes — releasing earlier would re-open the race.
 */
class UploadAction
{
    private const UPLOAD_CACHE = '/storage/usbdisk1/mikopbx/tmp/www_cache/upload_cache/';

    public static function main(array $data): PBXApiResult
    {
        $res = new PBXApiResult();

        $vendor = strtolower(trim((string)($data['vendor'] ?? '')));
        $fileId = (string)($data['file_id'] ?? '');
        $model  = isset($data['model']) ? trim((string)$data['model']) : '';
        $ver    = isset($data['version']) ? trim((string)$data['version']) : '';
        $notes  = isset($data['notes']) ? (string)$data['notes'] : '';

        if (!isset(Repository::VENDOR_EXTENSIONS[$vendor])) {
            $res->httpCode            = 400;
            $res->messages['error'][] = "unsupported vendor '{$vendor}'";
            return $res;
        }
        if ($fileId === '' || !preg_match('/^[A-Za-z0-9_.-]+$/', $fileId)) {
            $res->httpCode            = 400;
            $res->messages['error'][] = 'file_id is required';
            return $res;
        }

        $cacheDir = self::UPLOAD_CACHE . $fileId;
        if (!is_dir($cacheDir)) {
            $res->httpCode            = 404;
            $res->messages['error'][] = 'upload cache directory not found — upload may still be in progress';
            return $res;
        }
        $sourcePath = Repository::pickUploadedFile($cacheDir);
        if ($sourcePath === '') {
            // Either the merge is still in progress (only .partN / merge_settings
            // present) or Core never created the merged file. Client must wait
            // on the merge-complete event before re-calling this endpoint.
            $res->httpCode            = 425; // Too Early — retry after merge finishes.
            $res->messages['error'][] = 'uploaded file not ready yet — wait for chunk merge to complete';
            return $res;
        }

        $rawName  = basename($sourcePath);
        $filename = Repository::sanitizeFilename($rawName);
        if ($filename === '') {
            $res->httpCode            = 400;
            $res->messages['error'][] = 'empty or invalid filename';
            return $res;
        }

        $ext      = Repository::extensionOf($filename);
        $allowed  = Repository::VENDOR_EXTENSIONS[$vendor];
        if (!in_array($ext, $allowed, true)) {
            $res->httpCode            = 415;
            $res->messages['error'][] = sprintf(
                "extension '.%s' not allowed for vendor '%s' (allowed: %s)",
                $ext,
                $vendor,
                implode(', ', $allowed)
            );
            return $res;
        }

        $size = @filesize($sourcePath);
        if ($size === false) {
            $res->httpCode            = 500;
            $res->messages['error'][] = 'unable to stat uploaded file';
            return $res;
        }
        if ($size > Repository::MAX_FILE_BYTES) {
            $res->httpCode            = 413;
            $res->messages['error'][] = sprintf(
                'file too large: %d bytes (max %d / 80MB)',
                $size,
                Repository::MAX_FILE_BYTES
            );
            return $res;
        }

        // Hash on the temp file — if the move fails halfway, the digest still
        // describes a real artifact the admin can re-upload.
        $sha256 = @hash_file('sha256', $sourcePath);
        if (!is_string($sha256) || strlen($sha256) !== 64) {
            $res->httpCode            = 500;
            $res->messages['error'][] = 'failed to hash uploaded file';
            return $res;
        }

        Repository::ensureBaseDir();
        $vendorDir  = Repository::vendorDir($vendor);
        $targetPath = $vendorDir . '/' . $filename;

        // Pre-flight collision check on (vendor, filename) — the on-disk path
        // would collide. We surface a clean 409 here; a unique-index would be a
        // defensive backstop.
        $existing = ModuleAutoprovisionFirmware::findFirst([
            'vendor = :vendor: AND filename = :filename:',
            'bind' => ['vendor' => $vendor, 'filename' => $filename],
        ]);
        if ($existing !== null || file_exists($targetPath)) {
            $res->httpCode            = 409;
            $res->messages['error'][] = "firmware already exists for vendor '{$vendor}' / filename '{$filename}'";
            return $res;
        }

        // Refuse a second active firmware for the same (vendor, model). One
        // matching row per pair keeps the runtime lookup deterministic — the
        // admin has to delete or replace the old one first.
        if ($model !== '') {
            // Lower-case both sides so an admin uploading 'T46S' clashes with an
            // earlier 't46s' row — they would resolve to the same firmware row
            // at runtime and the database would silently keep two of them.
            $modelClash = ModuleAutoprovisionFirmware::findFirst([
                'vendor = :vendor: AND LOWER(model) = :model:',
                'bind' => ['vendor' => $vendor, 'model' => strtolower($model)],
            ]);
            if ($modelClash !== null) {
                $res->httpCode            = 409;
                $res->messages['error'][] = sprintf(
                    "firmware for vendor '%s' / model '%s' already exists (id=%d, %s) — delete or replace it first",
                    $vendor,
                    $model,
                    (int)$modelClash->id,
                    (string)$modelClash->filename
                );
                return $res;
            }
        }

        $lockHandle = @fopen(Repository::lockFile(), 'c');
        if ($lockHandle === false || !flock($lockHandle, LOCK_EX)) {
            if ($lockHandle !== false) {
                fclose($lockHandle);
            }
            $res->httpCode            = 500;
            $res->messages['error'][] = 'failed to acquire firmware upload lock';
            return $res;
        }

        try {
            // Re-check total under the lock — another upload could have landed
            // between our pre-flight check above and lock acquisition.
            $used = Repository::totalUsedBytes();
            if ($used + $size > Repository::MAX_TOTAL_BYTES) {
                $res->httpCode            = 507;
                $res->messages['error'][] = sprintf(
                    'repository full: %d + %d > %d bytes',
                    $used,
                    $size,
                    Repository::MAX_TOTAL_BYTES
                );
                return $res;
            }

            // Re-check collision under the lock for the same race-window reason.
            if (file_exists($targetPath)) {
                $res->httpCode            = 409;
                $res->messages['error'][] = "firmware already exists on disk: {$targetPath}";
                return $res;
            }

            if (!@rename($sourcePath, $targetPath)) {
                $res->httpCode            = 500;
                $res->messages['error'][] = "failed to move firmware into repository: {$targetPath}";
                return $res;
            }
            @chmod($targetPath, 0644);

            $row              = new ModuleAutoprovisionFirmware();
            $row->vendor      = $vendor;
            $row->model       = $model === '' ? null : $model;
            $row->filename    = $filename;
            $row->version     = $ver === '' ? null : $ver;
            $row->size        = $size;
            $row->sha256      = $sha256;
            $row->uploaded_at = date('c');
            $row->notes       = $notes === '' ? null : $notes;

            if (!$row->save()) {
                // Roll back the on-disk move so the next upload can retry the same filename.
                @unlink($targetPath);
                $res->httpCode = 500;
                foreach ($row->getMessages() as $msg) {
                    $res->messages['error'][] = (string)$msg;
                }
                if (empty($res->messages['error'])) {
                    $res->messages['error'][] = 'failed to persist firmware row';
                }
                return $res;
            }

            SystemMessages::sysLogMsg(
                'autoprovision-firmware',
                sprintf(
                    'upload vendor=%s model=%s version=%s file=%s size=%d sha256=%s',
                    $vendor,
                    $model === '' ? '-' : $model,
                    $ver === '' ? '-' : $ver,
                    $filename,
                    $size,
                    $sha256
                ),
                LOG_NOTICE
            );

            $res->success  = true;
            $res->httpCode = 201;
            $res->data     = [
                'id'          => (int)$row->id,
                'vendor'      => $vendor,
                'model'       => $row->model,
                'filename'    => $filename,
                'version'     => $row->version,
                'size'        => $size,
                'sha256'      => $sha256,
                'uploaded_at' => $row->uploaded_at,
            ];
            return $res;
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
            // Best-effort cleanup of an empty cache dir — Core may also clean it.
            if (is_dir($cacheDir)) {
                @rmdir($cacheDir);
            }
        }
    }
}
