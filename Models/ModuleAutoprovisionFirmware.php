<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

namespace Modules\ModuleAutoprovision\Models;

use MikoPBX\Modules\Models\ModulesModelsBase;

/**
 * Firmware blob registered with the module's HTTP/TFTP firmware repository.
 *
 * Rows describe a single on-disk file under <moduleDir>/firmware/<vendor>/<filename>.
 * The DB row is the source of truth: list views, the {FIRMWARE_URL} template lookup,
 * and the disk-space accounting all start here.
 *
 * (vendor, filename) is the natural key — two rows with the same pair would point at
 * the same path on disk, so the upload action rejects collisions with HTTP 409 and
 * (model = NULL) is the "any model from this vendor" fallback for the template lookup.
 */
class ModuleAutoprovisionFirmware extends ModulesModelsBase
{
    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    public $id;

    /**
     * Vendor key — one of yealink, snom, fanvil, grandstream, htek.
     *
     * @Column(type="string", nullable=false)
     */
    public $vendor;

    /**
     * Phone model identifier (e.g. "T46S"). NULL means "any model from this vendor"
     * and is used as the template-lookup fallback when the device's exact model is
     * not registered.
     *
     * @Column(type="string", nullable=true)
     */
    public $model;

    /**
     * On-disk filename. Sanitised at upload time (no path separators, no NUL bytes,
     * lowercase extension). Unique per vendor.
     *
     * @Column(type="string", nullable=false)
     */
    public $filename;

    /**
     * Vendor-reported firmware version string, e.g. "54.85.0.125".
     *
     * @Column(type="string", nullable=true)
     */
    public $version;

    /**
     * File size in bytes, captured at upload time.
     *
     * @Column(type="integer", nullable=false)
     */
    public $size;

    /**
     * SHA-256 hex digest computed before the file is moved into the repository.
     *
     * @Column(type="string", nullable=false)
     */
    public $sha256;

    /**
     * ISO-8601 timestamp captured at upload time (stored as plain string for portability
     * with Phalcon's sqlite/mysql adapters).
     *
     * @Column(type="string", nullable=true)
     */
    public $uploaded_at;

    /**
     * Free-form admin notes (release notes, internal ticket links, etc.).
     *
     * @Column(type="string", nullable=true)
     */
    public $notes;

    public function initialize(): void
    {
        $this->setSource('m_ModuleAutoprovisionFirmware');
        parent::initialize();
    }
}
