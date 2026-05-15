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
 * Removes a firmware row plus its on-disk blob.
 *
 * Two firmware rows can legitimately share `(vendor, filename)` — for example a
 * vendor-wide row (`model = ''`) plus a model-specific row both pointing at the
 * same `.rom`. Unconditionally unlinking on delete would nuke the file the
 * surviving row still serves over HTTP/TFTP, returning 404 to phones. So:
 *   1. Hold the upload flock (LOCK_EX) — uploads/replaces grab the same lock,
 *      so a concurrent upload that would point a new row at this filename
 *      cannot race us between the count and the unlink.
 *   2. Count other rows referencing `(vendor, filename)` excluding our id.
 *   3. Only unlink when the count is zero.
 *
 * The DB row is dropped unconditionally — a dangling DB entry pointing at a
 * missing file would keep handing out 404 URLs to phones, worse than just
 * missing the file the admin can re-upload.
 */
class DeleteAction
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

        $vendor   = (string)$row->vendor;
        $filename = (string)$row->filename;
        $target   = Repository::vendorDir($vendor) . '/' . $filename;

        // Take the same exclusive lock UploadAction/ReplaceAction use so a
        // concurrent upload pointing a fresh row at this same filename can't
        // squeeze in between the reference-count and the unlink.
        Repository::ensureBaseDir();
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
            $otherRefs = ModuleAutoprovisionFirmware::count([
                'vendor = :vendor: AND filename = :filename: AND id != :id:',
                'bind' => ['vendor' => $vendor, 'filename' => $filename, 'id' => $id],
            ]);

            $fileRemoved = false;
            if ((int)$otherRefs === 0 && file_exists($target)) {
                $fileRemoved = @unlink($target);
            }

            if (!$row->delete()) {
                $res->httpCode = 500;
                foreach ($row->getMessages() as $msg) {
                    $res->messages['error'][] = (string)$msg;
                }
                if (empty($res->messages['error'])) {
                    $res->messages['error'][] = 'failed to delete firmware row';
                }
                return $res;
            }

            SystemMessages::sysLogMsg(
                'autoprovision-firmware',
                sprintf(
                    'delete id=%d vendor=%s file=%s other_refs=%d file_unlinked=%d',
                    $id,
                    $vendor,
                    $filename,
                    (int)$otherRefs,
                    (int)$fileRemoved
                ),
                LOG_NOTICE
            );

            $res->success  = true;
            $res->httpCode = 200;
            $res->data     = [
                'id'             => $id,
                'deleted'        => true,
                'file_unlinked'  => $fileRemoved,
                'other_refs'     => (int)$otherRefs,
            ];
            return $res;
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }
}
