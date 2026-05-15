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
 * Replaces the on-disk blob of an existing firmware row with a freshly uploaded
 * file (the client first pushes chunks to Core's /pbxcore/api/v3/files:upload
 * with category=autoprovision-firmware, then PUTs the resulting file_id here).
 *
 * Strategy:
 *   1. Stage the new file alongside the old one as `<filename>.new`.
 *   2. Recompute sha256 and refresh the DB metadata (size/sha256/uploaded_at,
 *      and the filename if the upload carried a different one).
 *   3. Atomic-rename the staged file over the old one — phones that hit the
 *      nginx alias during the swap either see the old bytes or the new bytes,
 *      never a truncated file.
 *
 * Concurrency uses the same flock() as UploadAction so two replaces (or a
 * replace + an upload) cannot interleave their disk-space accounting.
 */
class ReplaceAction
{
    private const UPLOAD_CACHE = '/storage/usbdisk1/mikopbx/tmp/www_cache/upload_cache/';

    public static function main(array $data): PBXApiResult
    {
        $res = new PBXApiResult();
        $id  = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            $res->httpCode            = 400;
            $res->messages['error'][] = 'firmware id is required';
            return $res;
        }
        $fileId = (string)($data['file_id'] ?? '');
        if ($fileId === '' || !preg_match('/^[A-Za-z0-9_.-]+$/', $fileId)) {
            $res->httpCode            = 400;
            $res->messages['error'][] = 'file_id is required';
            return $res;
        }

        $row = ModuleAutoprovisionFirmware::findFirst([
            'id = :id:',
            'bind' => ['id' => $id],
        ]);
        if ($row === null) {
            $res->httpCode = 404;
            return $res;
        }

        $cacheDir = self::UPLOAD_CACHE . $fileId;
        if (!is_dir($cacheDir)) {
            $res->httpCode            = 404;
            $res->messages['error'][] = 'upload cache directory not found';
            return $res;
        }
        $sourcePath = Repository::pickUploadedFile($cacheDir);
        if ($sourcePath === '') {
            $res->httpCode            = 425;
            $res->messages['error'][] = 'uploaded file not ready yet — wait for chunk merge to complete';
            return $res;
        }

        $vendor       = (string)$row->vendor;
        $rawName      = basename($sourcePath);
        $newFilename  = Repository::sanitizeFilename($rawName);
        if ($newFilename === '') {
            $newFilename = (string)$row->filename;
        }
        $ext = Repository::extensionOf($newFilename);
        if (!in_array($ext, Repository::VENDOR_EXTENSIONS[$vendor] ?? [], true)) {
            $res->httpCode            = 415;
            $res->messages['error'][] = sprintf(
                "extension '.%s' not allowed for vendor '%s'",
                $ext,
                $vendor
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
            $res->messages['error'][] = sprintf('file too large: %d bytes (max %d)', $size, Repository::MAX_FILE_BYTES);
            return $res;
        }

        $sha256 = @hash_file('sha256', $sourcePath);
        if (!is_string($sha256) || strlen($sha256) !== 64) {
            $res->httpCode            = 500;
            $res->messages['error'][] = 'failed to hash uploaded file';
            return $res;
        }

        Repository::ensureBaseDir();
        $oldFilename = (string)$row->filename;
        $oldPath     = Repository::vendorDir($vendor) . '/' . $oldFilename;
        $newPath     = Repository::vendorDir($vendor) . '/' . $newFilename;
        $stagePath   = $newPath . '.new';

        // If the upload renames the file (different filename) make sure the new
        // slot is free (or refers to the same row we are replacing).
        if ($newFilename !== $oldFilename) {
            $clash = ModuleAutoprovisionFirmware::findFirst([
                'vendor = :vendor: AND filename = :filename: AND id != :id:',
                'bind' => ['vendor' => $vendor, 'filename' => $newFilename, 'id' => $id],
            ]);
            if ($clash !== null || file_exists($newPath)) {
                $res->httpCode            = 409;
                $res->messages['error'][] = "another firmware already uses '{$vendor}/{$newFilename}'";
                return $res;
            }
        } else {
            // Same filename — overwriting in place would silently corrupt any
            // other row that points at the same (vendor, filename) blob (its
            // recorded sha256/size would no longer match the bytes on disk).
            // Refuse and let the admin clean up the dup first.
            $sharedRefs = ModuleAutoprovisionFirmware::count([
                'vendor = :vendor: AND filename = :filename: AND id != :id:',
                'bind' => ['vendor' => $vendor, 'filename' => $oldFilename, 'id' => $id],
            ]);
            if ((int)$sharedRefs > 0) {
                $res->httpCode            = 409;
                $res->messages['error'][] = sprintf(
                    "cannot replace '%s/%s' in place — %d other firmware row(s) reference the same file; delete the duplicates or upload under a new filename",
                    $vendor,
                    $oldFilename,
                    (int)$sharedRefs
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
            // Total includes the existing on-disk file we are about to replace,
            // so subtract its current size before checking the cap.
            $used  = Repository::totalUsedBytes();
            $delta = $size - (int)$row->size;
            if ($delta > 0 && $used + $delta > Repository::MAX_TOTAL_BYTES) {
                $res->httpCode            = 507;
                $res->messages['error'][] = sprintf(
                    'repository full: %d + delta %d > %d',
                    $used,
                    $delta,
                    Repository::MAX_TOTAL_BYTES
                );
                return $res;
            }

            if (!@rename($sourcePath, $stagePath)) {
                $res->httpCode            = 500;
                $res->messages['error'][] = "failed to stage new firmware at {$stagePath}";
                return $res;
            }
            @chmod($stagePath, 0644);

            // Atomic swap.
            if (!@rename($stagePath, $newPath)) {
                @unlink($stagePath);
                $res->httpCode            = 500;
                $res->messages['error'][] = "failed to swap firmware into place at {$newPath}";
                return $res;
            }

            // If the rename pointed at a different filename, remove the old one —
            // but only when no other row still references the (vendor, oldFilename)
            // blob. Otherwise the surviving rows' download URLs would 404 (the same
            // shared-reference invariant the in-place branch guards above, just on
            // the post-rename side).
            if ($newFilename !== $oldFilename && $oldPath !== $newPath && file_exists($oldPath)) {
                $sharedOldRefs = ModuleAutoprovisionFirmware::count([
                    'vendor = :vendor: AND filename = :filename: AND id != :id:',
                    'bind' => ['vendor' => $vendor, 'filename' => $oldFilename, 'id' => $id],
                ]);
                if ((int)$sharedOldRefs === 0) {
                    @unlink($oldPath);
                }
            }

            $row->filename    = $newFilename;
            $row->size        = $size;
            $row->sha256      = $sha256;
            $row->uploaded_at = date('c');

            if (!$row->save()) {
                $res->httpCode = 500;
                foreach ($row->getMessages() as $msg) {
                    $res->messages['error'][] = (string)$msg;
                }
                if (empty($res->messages['error'])) {
                    $res->messages['error'][] = 'failed to persist updated firmware row';
                }
                return $res;
            }

            SystemMessages::sysLogMsg(
                'autoprovision-firmware',
                sprintf(
                    'replace id=%d vendor=%s file=%s size=%d sha256=%s',
                    $id,
                    $vendor,
                    $newFilename,
                    $size,
                    $sha256
                ),
                LOG_NOTICE
            );

            $res->success  = true;
            $res->httpCode = 200;
            $res->data     = [
                'id'          => (int)$row->id,
                'vendor'      => $vendor,
                'model'       => $row->model,
                'filename'    => $newFilename,
                'version'     => $row->version,
                'size'        => $size,
                'sha256'      => $sha256,
                'uploaded_at' => $row->uploaded_at,
            ];
            return $res;
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
            if (is_dir($cacheDir)) {
                @rmdir($cacheDir);
            }
        }
    }
}
