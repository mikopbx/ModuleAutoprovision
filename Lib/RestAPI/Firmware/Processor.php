<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

namespace Modules\ModuleAutoprovision\Lib\RestAPI\Firmware;

use MikoPBX\PBXCoreREST\Lib\PBXApiResult;
use Modules\ModuleAutoprovision\Lib\RestAPI\Firmware\Actions\{DeleteAction,
    DownloadAction,
    GetListAction,
    GetRecordAction,
    ReplaceAction,
    UpdateAction,
    UploadAction};
use Phalcon\Di\Injectable;

/**
 * Routes Firmware controller requests to the corresponding Action class.
 *
 * Follows the Processor + Actions pattern from ModuleExampleRestAPIv3 so the HTTP
 * surface stays a thin attribute-decorated shell and all business logic lives in
 * single-purpose Action classes.
 */
class Processor extends Injectable
{
    public static function callBack(array $request): PBXApiResult
    {
        $res            = new PBXApiResult();
        $res->processor = __METHOD__;
        $action         = $request['action'] ?? '';
        $data           = $request['data'] ?? [];

        $res = match ($action) {
            'getList'   => GetListAction::main($data),
            'getRecord' => GetRecordAction::main($data),
            'upload'    => UploadAction::main($data),
            'update',
            'patch'     => UpdateAction::main($data),
            'replace'   => ReplaceAction::main($data),
            'delete'    => DeleteAction::main($data),
            'download'  => DownloadAction::main($data),
            default     => self::unknownAction($action),
        };

        $res->function = $action;
        return $res;
    }

    private static function unknownAction(string $action): PBXApiResult
    {
        $res                  = new PBXApiResult();
        $res->success         = false;
        $res->httpCode        = 400;
        $res->messages['error'][] = "Unknown action - {$action} in " . __CLASS__;
        return $res;
    }
}
