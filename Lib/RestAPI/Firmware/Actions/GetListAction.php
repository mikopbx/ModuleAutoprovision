<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

namespace Modules\ModuleAutoprovision\Lib\RestAPI\Firmware\Actions;

use MikoPBX\PBXCoreREST\Lib\PBXApiResult;
use Modules\ModuleAutoprovision\Lib\AutoprovisionConf;
use Modules\ModuleAutoprovision\Lib\RestAPI\Firmware\Repository;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovision;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovisionFirmware;

/**
 * Returns every firmware entry plus the totals row the admin UI needs to render
 * the "X MB of 300 MB used" footer without a separate round trip.
 */
class GetListAction
{
    public static function main(array $data): PBXApiResult
    {
        $res = new PBXApiResult();

        $rows  = ModuleAutoprovisionFirmware::find(['order' => 'vendor, model']);
        $items = [];
        foreach ($rows as $row) {
            $items[] = self::serialize($row);
        }

        $used = Repository::totalUsedBytes();
        $res->success  = true;
        $res->httpCode = 200;
        $res->data     = [
            'items' => $items,
            'totals' => [
                'count'     => count($items),
                'used'      => $used,
                'used_cap'  => Repository::MAX_TOTAL_BYTES,
                'file_cap'  => Repository::MAX_FILE_BYTES,
            ],
        ];
        return $res;
    }

    /**
     * @param ModuleAutoprovisionFirmware $row
     * @return array<string, mixed>
     */
    private static function serialize($row): array
    {
        $settings = ModuleAutoprovision::findFirst();
        $host     = trim((string)($settings->pbx_host ?? ''));
        $port     = AutoprovisionConf::getHttpPort();

        $url = '';
        if ($host !== '' && $row->vendor !== '' && $row->filename !== '') {
            $url = sprintf('http://%s:%d/firmware/%s/%s', $host, $port, $row->vendor, $row->filename);
        }

        return [
            'id'          => (int)$row->id,
            'vendor'      => (string)$row->vendor,
            'model'       => $row->model,
            'filename'    => (string)$row->filename,
            'version'     => $row->version,
            'size'        => (int)$row->size,
            'sha256'      => (string)$row->sha256,
            'uploaded_at' => $row->uploaded_at,
            'notes'       => $row->notes,
            'url'         => $url,
        ];
    }
}
