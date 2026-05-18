<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

namespace Modules\ModuleAutoprovision\Lib\RestAPI\Firmware;

use MikoPBX\PBXCoreREST\Lib\Common\AbstractDataStructure;
use MikoPBX\PBXCoreREST\Lib\Common\OpenApiSchemaProvider;

/**
 * OpenAPI / sanitisation schema for the Firmware resource.
 *
 * The list and detail views return the same field set — firmware entries are flat,
 * there is no "summary vs full" split worth maintaining.
 */
class DataStructure extends AbstractDataStructure implements OpenApiSchemaProvider
{
    public const VENDORS = ['yealink', 'snom', 'fanvil', 'grandstream', 'htek'];

    public static function getListItemSchema(): array
    {
        $definitions = self::getParameterDefinitions();
        $properties  = [];
        foreach (($definitions['request'] ?? []) as $field => $def) {
            $properties[$field] = $def;
        }
        foreach (($definitions['response'] ?? []) as $field => $def) {
            $properties[$field] = $def;
        }
        return ['type' => 'object', 'properties' => $properties];
    }

    public static function getDetailSchema(): array
    {
        return self::getListItemSchema();
    }

    public static function getRelatedSchemas(): array
    {
        return self::getParameterDefinitions()['related'] ?? [];
    }

    public static function getParameterDefinitions(): array
    {
        $all = self::getAllFieldDefinitions();

        $writable = [];
        $readonly = [];
        foreach ($all as $name => $def) {
            if (!empty($def['readOnly'])) {
                $readonly[$name] = $def;
            } else {
                $req = $def;
                if (isset($def['description']) && is_string($def['description'])) {
                    $req['description'] = str_replace('rest_schema_', 'rest_param_', $def['description']);
                }
                $writable[$name] = $req;
            }
        }

        return [
            'request'  => $writable,
            'response' => $readonly,
            'related'  => [],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function getAllFieldDefinitions(): array
    {
        return [
            // ---- writable (request body) ----
            'vendor' => [
                'type'        => 'string',
                'description' => 'rest_schema_firmware_vendor',
                'enum'        => self::VENDORS,
                'required'    => true,
                'example'     => 'yealink',
            ],
            'model' => [
                'type'        => 'string',
                'description' => 'rest_schema_firmware_model',
                'maxLength'   => 64,
                'example'     => 'T46S',
            ],
            'version' => [
                'type'        => 'string',
                'description' => 'rest_schema_firmware_version',
                'maxLength'   => 64,
                'example'     => '66.86.0.15',
            ],
            'notes' => [
                'type'        => 'string',
                'description' => 'rest_schema_firmware_notes',
                'maxLength'   => 1000,
            ],
            'file_id' => [
                'type'        => 'string',
                'description' => 'rest_schema_firmware_file_id',
                'maxLength'   => 128,
                'required'    => true,
                'example'     => 'a1b2c3d4',
            ],
            'filename' => [
                'type'        => 'string',
                'description' => 'rest_schema_firmware_filename',
                'maxLength'   => 255,
                'example'     => 'T46S-66.86.0.15.rom',
            ],

            // ---- response-only ----
            'id' => [
                'type'        => 'integer',
                'description' => 'rest_schema_firmware_id',
                'readOnly'    => true,
            ],
            'size' => [
                'type'        => 'integer',
                'description' => 'rest_schema_firmware_size',
                'readOnly'    => true,
            ],
            'sha256' => [
                'type'        => 'string',
                'description' => 'rest_schema_firmware_sha256',
                'readOnly'    => true,
            ],
            'uploaded_at' => [
                'type'        => 'string',
                'format'      => 'date-time',
                'description' => 'rest_schema_firmware_uploaded_at',
                'readOnly'    => true,
            ],
            'url' => [
                'type'        => 'string',
                'description' => 'rest_schema_firmware_url',
                'readOnly'    => true,
            ],
        ];
    }
}
