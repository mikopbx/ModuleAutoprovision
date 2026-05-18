<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

namespace Modules\ModuleAutoprovision\Lib\RestAPI\Firmware;

use MikoPBX\Core\System\SystemMessages;
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
    private const LOG_TAG       = 'autoprovision-firmware';
    private const MUTATING      = ['upload', 'update', 'patch', 'replace', 'delete'];
    private const QUIET_ACTIONS = ['getList', 'getRecord', 'download'];

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

        // Audit log every state-changing firmware operation so the operator can
        // trace "who replaced the Yealink firmware last Tuesday" without paging
        // through nginx access logs. Read-only endpoints stay quiet to avoid
        // spamming syslog on every admin-UI refresh.
        if (in_array($action, self::MUTATING, true)) {
            $id       = (string)($data['id'] ?? '');
            $vendor   = (string)($data['vendor'] ?? '');
            $filename = (string)($data['filename'] ?? '');
            $status   = $res->success ? 'ok' : 'fail';
            $parts    = ["action={$action}", "status={$status}", "http={$res->httpCode}"];
            foreach (['id' => $id, 'vendor' => $vendor, 'filename' => $filename] as $k => $v) {
                if ($v !== '') {
                    $parts[] = "{$k}={$v}";
                }
            }
            if (!$res->success && !empty($res->messages['error'])) {
                $parts[] = 'err=' . (string)$res->messages['error'][0];
            }
            $priority = $res->success ? LOG_NOTICE : LOG_WARNING;
            SystemMessages::sysLogMsg(self::LOG_TAG, implode(' ', $parts), $priority);
        } elseif (!in_array($action, self::QUIET_ACTIONS, true) && !$res->success) {
            // Unknown or malformed actions are worth a single line.
            SystemMessages::sysLogMsg(
                self::LOG_TAG,
                "action={$action} status=fail http={$res->httpCode}",
                LOG_WARNING
            );
        }

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
