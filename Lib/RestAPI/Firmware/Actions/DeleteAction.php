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
 * Deletes the file first; if that fails (already gone, permission issue) we
 * still drop the DB row — a dangling DB entry would keep handing out a 404 URL
 * to phones, which is worse than a missing file the admin can re-upload.
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

        if (file_exists($target)) {
            @unlink($target);
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
            "delete id={$id} vendor={$vendor} file={$filename}",
            LOG_NOTICE
        );

        $res->success  = true;
        $res->httpCode = 200;
        $res->data     = ['id' => $id, 'deleted' => true];
        return $res;
    }
}
