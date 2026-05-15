<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

namespace Modules\ModuleAutoprovision\Lib\RestAPI\Firmware;

use MikoPBX\PBXCoreREST\Attributes\{ApiResource,
    ApiOperation,
    ApiParameterRef,
    ApiResponse,
    ApiDataSchema,
    HttpMapping,
    SecurityType,
    ResourceSecurity};
use MikoPBX\PBXCoreREST\Controllers\BaseRestController;
use MikoPBX\PBXCoreREST\Lib\Common\CommonDataStructure;

/**
 * Firmware repository — HTTP CRUD for phone-firmware blobs that the module
 * later serves to phones over the dedicated provisioning HTTP port (and a
 * planned TFTP fallback).
 *
 * Upload flow: client first PUSHes the file chunks through Core's
 * /pbxcore/api/v3/files:upload (category=autoprovision-firmware) and then
 * POSTs here with the resulting `file_id` to register the blob.
 */
#[ApiResource(
    path: '/pbxcore/api/v3/module-autoprovision/firmware',
    tags: ['Module Autoprovision - Firmware'],
    description: 'Phone firmware blob repository for ModuleAutoprovision',
    processor: Processor::class
)]
#[HttpMapping(
    mapping: [
        'GET'    => ['getList', 'getRecord', 'download'],
        'POST'   => ['upload'],
        'PUT'    => ['replace'],
        'PATCH'  => ['update'],
        'DELETE' => ['delete'],
    ],
    resourceLevelMethods: ['getRecord', 'update', 'replace', 'delete', 'download'],
    collectionLevelMethods: ['getList', 'upload'],
    customMethods: ['upload', 'download'],
    idPattern: '[^/:]+'
)]
#[ResourceSecurity('module-autoprovision-firmware', requirements: [SecurityType::LOCALHOST, SecurityType::BEARER_TOKEN])]
class Controller extends BaseRestController
{
    protected string $processorClass = Processor::class;

    /**
     * Action mapping override.
     *
     * BaseRestController forwards `PUT /resource/{id}` to the `update` action by
     * default — but on this resource PATCH is the metadata update and PUT is a
     * full-blob replacement, so we rename the PUT dispatch to `replace`. PATCH
     * stays on the standard `patch` slot and the Processor folds it into
     * UpdateAction.
     *
     * @var array<string, array<string, string>>
     */
    protected array $actionMapping = [
        'GET'    => ['collection' => 'getList', 'resource' => 'getRecord'],
        'POST'   => ['collection' => 'create',  'resource' => 'create'],
        'PUT'    => ['collection' => 'replace', 'resource' => 'replace'],
        'PATCH'  => ['collection' => 'patch',   'resource' => 'patch'],
        'DELETE' => ['collection' => 'delete',  'resource' => 'delete'],
    ];

    /**
     * GET /pbxcore/api/v3/module-autoprovision/firmware
     */
    #[ApiDataSchema(schemaClass: DataStructure::class, type: 'list', isArray: true)]
    #[ApiOperation(
        summary: 'rest_firmware_GetList',
        description: 'rest_firmware_GetListDesc',
        operationId: 'getFirmwareList'
    )]
    #[ApiParameterRef('limit', dataStructure: CommonDataStructure::class)]
    #[ApiParameterRef('offset', dataStructure: CommonDataStructure::class)]
    #[ApiResponse(200, 'rest_response_200_list')]
    #[ApiResponse(401, 'rest_response_401')]
    #[ApiResponse(500, 'rest_response_500')]
    public function getList(): void
    {
    }

    /**
     * GET /pbxcore/api/v3/module-autoprovision/firmware/{id}
     */
    #[ApiDataSchema(schemaClass: DataStructure::class, type: 'detail')]
    #[ApiOperation(
        summary: 'rest_firmware_GetRecord',
        description: 'rest_firmware_GetRecordDesc',
        operationId: 'getFirmwareById'
    )]
    #[ApiResponse(200, 'rest_response_200_record')]
    #[ApiResponse(401, 'rest_response_401')]
    #[ApiResponse(404, 'rest_response_404')]
    #[ApiResponse(500, 'rest_response_500')]
    public function getRecord(): void
    {
    }

    /**
     * POST /pbxcore/api/v3/module-autoprovision/firmware:upload
     *
     * Body (JSON): { file_id, vendor, model?, version?, notes? }
     * `file_id` is the resumableIdentifier returned by /pbxcore/api/v3/files:upload.
     */
    #[ApiDataSchema(schemaClass: DataStructure::class, type: 'detail')]
    #[ApiOperation(
        summary: 'rest_firmware_Upload',
        description: 'rest_firmware_UploadDesc',
        operationId: 'uploadFirmware'
    )]
    #[ApiParameterRef('file_id', required: true)]
    #[ApiParameterRef('vendor', required: true)]
    #[ApiParameterRef('model')]
    #[ApiParameterRef('version')]
    #[ApiParameterRef('notes')]
    #[ApiResponse(201, 'rest_response_201_uploaded')]
    #[ApiResponse(400, 'rest_response_400')]
    #[ApiResponse(401, 'rest_response_401')]
    #[ApiResponse(409, 'rest_response_409_conflict')]
    #[ApiResponse(413, 'rest_response_413_too_large')]
    #[ApiResponse(415, 'rest_response_415_unsupported_media')]
    #[ApiResponse(507, 'rest_response_507_insufficient_storage')]
    public function upload(): void
    {
    }

    /**
     * PATCH /pbxcore/api/v3/module-autoprovision/firmware/{id}
     *
     * Metadata-only update. To swap the file blob use PUT (see `replace`).
     */
    #[ApiDataSchema(schemaClass: DataStructure::class, type: 'detail')]
    #[ApiOperation(
        summary: 'rest_firmware_Update',
        description: 'rest_firmware_UpdateDesc',
        operationId: 'updateFirmware'
    )]
    #[ApiParameterRef('vendor')]
    #[ApiParameterRef('model')]
    #[ApiParameterRef('version')]
    #[ApiParameterRef('notes')]
    #[ApiResponse(200, 'rest_response_200_record')]
    #[ApiResponse(400, 'rest_response_400')]
    #[ApiResponse(401, 'rest_response_401')]
    #[ApiResponse(404, 'rest_response_404')]
    #[ApiResponse(409, 'rest_response_409_conflict')]
    #[ApiResponse(415, 'rest_response_415_unsupported_media')]
    public function update(): void
    {
    }

    /**
     * PUT /pbxcore/api/v3/module-autoprovision/firmware/{id}
     *
     * Replaces the on-disk blob with a freshly uploaded file (chunked first,
     * then PUT with the resulting file_id).
     */
    #[ApiDataSchema(schemaClass: DataStructure::class, type: 'detail')]
    #[ApiOperation(
        summary: 'rest_firmware_Replace',
        description: 'rest_firmware_ReplaceDesc',
        operationId: 'replaceFirmware'
    )]
    #[ApiParameterRef('file_id', required: true)]
    #[ApiResponse(200, 'rest_response_200_record')]
    #[ApiResponse(400, 'rest_response_400')]
    #[ApiResponse(401, 'rest_response_401')]
    #[ApiResponse(404, 'rest_response_404')]
    #[ApiResponse(409, 'rest_response_409_conflict')]
    #[ApiResponse(413, 'rest_response_413_too_large')]
    #[ApiResponse(415, 'rest_response_415_unsupported_media')]
    #[ApiResponse(507, 'rest_response_507_insufficient_storage')]
    public function replace(): void
    {
    }

    /**
     * DELETE /pbxcore/api/v3/module-autoprovision/firmware/{id}
     */
    #[ApiOperation(
        summary: 'rest_firmware_Delete',
        description: 'rest_firmware_DeleteDesc',
        operationId: 'deleteFirmware'
    )]
    #[ApiResponse(200, 'rest_response_200_delete')]
    #[ApiResponse(401, 'rest_response_401')]
    #[ApiResponse(404, 'rest_response_404')]
    #[ApiResponse(500, 'rest_response_500')]
    public function delete(): void
    {
    }

    /**
     * GET /pbxcore/api/v3/module-autoprovision/firmware/{id}:download
     *
     * Admin-only sanity-check route. Phones download via the public nginx alias
     * (location ^~ /firmware/), never through PHP.
     */
    #[ApiOperation(
        summary: 'rest_firmware_Download',
        description: 'rest_firmware_DownloadDesc',
        operationId: 'downloadFirmware'
    )]
    #[ApiResponse(200, 'rest_response_200_file_download')]
    #[ApiResponse(401, 'rest_response_401')]
    #[ApiResponse(404, 'rest_response_404')]
    #[ApiResponse(500, 'rest_response_500')]
    public function download(): void
    {
    }
}
