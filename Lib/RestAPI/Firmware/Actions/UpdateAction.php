<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

namespace Modules\ModuleAutoprovision\Lib\RestAPI\Firmware\Actions;

use MikoPBX\Core\System\Util;
use MikoPBX\PBXCoreREST\Lib\PBXApiResult;
use Modules\ModuleAutoprovision\Lib\RestAPI\Firmware\Repository;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovisionFirmware;

/**
 * Metadata-only update — vendor/model/version/notes. The file blob and its
 * sha256 are not touched here; use ReplaceAction to swap the bytes on disk.
 *
 * Changing `vendor` would orphan the file under the old vendor dir, so we
 * rename it (and its row) atomically: move the on-disk file into the new
 * vendor folder, then save the row. If the move fails we leave both the
 * file and the row alone and return 500.
 */
class UpdateAction
{
    public static function main(array $data): PBXApiResult
    {
        $res = new PBXApiResult();
        $id  = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            $res->httpCode            = 400;
            $res->messages['error'][] = 'firmware id is required';
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

        $oldVendor   = (string)$row->vendor;
        $oldFilename = (string)$row->filename;
        $oldPath     = Repository::vendorDir($oldVendor) . '/' . $oldFilename;

        $newVendor = $oldVendor;
        if (array_key_exists('vendor', $data)) {
            $candidate = strtolower(trim((string)$data['vendor']));
            if (!isset(Repository::VENDOR_EXTENSIONS[$candidate])) {
                $res->httpCode            = 400;
                $res->messages['error'][] = "unsupported vendor '{$candidate}'";
                return $res;
            }
            $newVendor = $candidate;
        }

        if (array_key_exists('model', $data)) {
            $m         = trim((string)$data['model']);
            $row->model = $m === '' ? null : $m;
        }
        if (array_key_exists('version', $data)) {
            $v           = trim((string)$data['version']);
            $row->version = $v === '' ? null : $v;
        }
        if (array_key_exists('notes', $data)) {
            $n         = (string)$data['notes'];
            $row->notes = $n === '' ? null : $n;
        }

        // Refuse edits that would produce a second row for the same (vendor, model).
        // UploadAction blocks this on insert; without the same guard here a PATCH
        // could quietly create the ambiguity (two rows for vendor=yealink, model=T46S
        // → resolver picks the most recent one, the older one becomes a ghost).
        if ($newVendor !== '' && $row->model !== null && $row->model !== '') {
            $modelClashOnEdit = ModuleAutoprovisionFirmware::findFirst([
                'vendor = :vendor: AND LOWER(model) = :model: AND id != :id:',
                'bind' => [
                    'vendor' => $newVendor,
                    'model'  => strtolower((string)$row->model),
                    'id'     => $id,
                ],
            ]);
            if ($modelClashOnEdit !== null) {
                $res->httpCode            = 409;
                $res->messages['error'][] = sprintf(
                    "firmware for vendor '%s' / model '%s' already exists (id=%d, %s)",
                    $newVendor,
                    (string)$row->model,
                    (int)$modelClashOnEdit->id,
                    (string)$modelClashOnEdit->filename
                );
                return $res;
            }
        }

        if ($newVendor !== $oldVendor) {
            // Make sure the extension is still legal for the new vendor.
            $ext = Repository::extensionOf($oldFilename);
            if (!in_array($ext, Repository::VENDOR_EXTENSIONS[$newVendor], true)) {
                $res->httpCode            = 415;
                $res->messages['error'][] = sprintf(
                    "extension '.%s' not allowed for vendor '%s'",
                    $ext,
                    $newVendor
                );
                return $res;
            }
            // Refuse if another row already occupies the destination slot.
            $clash = ModuleAutoprovisionFirmware::findFirst([
                'vendor = :vendor: AND filename = :filename: AND id != :id:',
                'bind' => ['vendor' => $newVendor, 'filename' => $oldFilename, 'id' => $id],
            ]);
            if ($clash !== null) {
                $res->httpCode            = 409;
                $res->messages['error'][] = "another firmware row already uses '{$newVendor}/{$oldFilename}'";
                return $res;
            }

            Repository::ensureBaseDir();
            $newPath = Repository::vendorDir($newVendor) . '/' . $oldFilename;
            if (file_exists($newPath)) {
                $res->httpCode            = 409;
                $res->messages['error'][] = "file already exists at destination: {$newPath}";
                return $res;
            }
            if (file_exists($oldPath) && !@rename($oldPath, $newPath)) {
                $res->httpCode            = 500;
                $res->messages['error'][] = "failed to move firmware file to {$newPath}";
                return $res;
            }
            $row->vendor = $newVendor;
        }

        if (!$row->save()) {
            // If we already moved the file, move it back to keep DB ↔ disk consistent.
            if ($newVendor !== $oldVendor) {
                $newPath = Repository::vendorDir($newVendor) . '/' . $oldFilename;
                @rename($newPath, $oldPath);
            }
            $res->httpCode = 500;
            foreach ($row->getMessages() as $msg) {
                $res->messages['error'][] = (string)$msg;
            }
            if (empty($res->messages['error'])) {
                $res->messages['error'][] = 'failed to persist firmware row';
            }
            return $res;
        }

        Util::sysLogMsg(
            'autoprovision-firmware',
            sprintf('update id=%d vendor=%s model=%s file=%s', $id, $row->vendor, $row->model ?? '-', $oldFilename),
            LOG_NOTICE
        );

        $res->success  = true;
        $res->httpCode = 200;
        $res->data     = [
            'id'          => (int)$row->id,
            'vendor'      => (string)$row->vendor,
            'model'       => $row->model,
            'filename'    => (string)$row->filename,
            'version'     => $row->version,
            'size'        => (int)$row->size,
            'sha256'      => (string)$row->sha256,
            'uploaded_at' => $row->uploaded_at,
            'notes'       => $row->notes,
        ];
        return $res;
    }
}
