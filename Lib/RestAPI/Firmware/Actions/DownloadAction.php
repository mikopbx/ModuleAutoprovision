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
 * Admin-only download (sanity check on the stored blob).
 *
 * Phones never hit this — they pull firmware straight from the nginx alias on
 * the provisioning port to avoid PHP buffering a 40 MB blob. Returned via the
 * PBXApiResult.fpassthru convention used by the rest of this module.
 */
class DownloadAction
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

        $path = Repository::vendorDir((string)$row->vendor) . '/' . $row->filename;
        if (!is_file($path)) {
            $res->httpCode            = 404;
            $res->messages['error'][] = 'firmware file missing on disk';
            return $res;
        }

        Util::sysLogMsg(
            'autoprovision-firmware',
            sprintf('admin download id=%d file=%s', $id, $row->filename),
            LOG_INFO
        );

        $res->success  = true;
        $res->httpCode = 200;
        $res->data     = [
            'fpassthru' => [
                'filename'     => $path,
                'content_type' => 'application/octet-stream',
                'need_delete'  => false,
            ],
        ];
        return $res;
    }
}
