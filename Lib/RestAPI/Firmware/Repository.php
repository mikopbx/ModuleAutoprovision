<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

namespace Modules\ModuleAutoprovision\Lib\RestAPI\Firmware;

use MikoPBX\Modules\PbxExtensionUtils;
use Modules\ModuleAutoprovision\Lib\AutoprovisionConf;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovision;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovisionFirmware;

/**
 * Filesystem helpers shared between the Firmware actions.
 *
 * Centralises the on-disk layout, the size caps, and the per-vendor extension
 * whitelist so the controller actions don't reinvent paths or limits independently.
 */
final class Repository
{
    public const MODULE_UNIQUE_ID = 'ModuleAutoprovision';

    /** 80 MB per file. */
    public const MAX_FILE_BYTES = 80 * 1024 * 1024;

    /** 300 MB total across the whole repository. */
    public const MAX_TOTAL_BYTES = 300 * 1024 * 1024;

    /** Vendor → allowed lowercase extensions (no leading dot). */
    public const VENDOR_EXTENSIONS = [
        'yealink'     => ['rom', 'bin'],
        'snom'        => ['bin', 'fw'],
        'fanvil'      => ['z', 'bin'],
        'grandstream' => ['bin'],
        'htek'        => ['rom', 'bin'],
    ];

    public static function moduleDir(): string
    {
        return PbxExtensionUtils::getModuleDir(self::MODULE_UNIQUE_ID);
    }

    public static function baseDir(): string
    {
        return self::moduleDir() . '/firmware';
    }

    public static function vendorDir(string $vendor): string
    {
        return self::baseDir() . '/' . $vendor;
    }

    public static function lockFile(): string
    {
        return self::baseDir() . '/.uploadlock';
    }

    /**
     * Defensive mkdir — the module dir may move between MikoPBX upgrades.
     */
    public static function ensureBaseDir(): void
    {
        $base = self::baseDir();
        if (!is_dir($base) && !mkdir($base, 0755, true) && !is_dir($base)) {
            throw new \RuntimeException("Cannot create firmware base directory: {$base}");
        }
        foreach (array_keys(self::VENDOR_EXTENSIONS) as $vendor) {
            $dir = self::vendorDir($vendor);
            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new \RuntimeException("Cannot create vendor firmware directory: {$dir}");
            }
        }
    }

    /**
     * Strips path separators / NUL bytes and lowercases the extension.
     * Returns '' if the resulting name would be empty.
     */
    public static function sanitizeFilename(string $name): string
    {
        $name = str_replace(["\0", '/', '\\'], '', $name);
        $name = basename($name);
        $name = trim($name);
        if ($name === '') {
            return '';
        }
        $dot = strrpos($name, '.');
        if ($dot !== false) {
            $name = substr($name, 0, $dot) . strtolower(substr($name, $dot));
        }
        return $name;
    }

    public static function extensionOf(string $filename): string
    {
        $dot = strrpos($filename, '.');
        return $dot === false ? '' : strtolower(substr($filename, $dot + 1));
    }

    /**
     * Picks the actual uploaded firmware file out of Core's upload_cache directory.
     *
     * Core's merge worker also writes housekeeping artifacts into the same dir
     * (`merge_settings`, `merging_progress`, partial `.partN` chunks). A naive
     * `glob('/*')[0]` can land on one of those when the firmware filename sorts
     * after them, so we explicitly skip the known metadata names and the chunk
     * sentinel, plus anything dot-prefixed.
     *
     * Returns the absolute path or '' when no merged file is present yet.
     */
    public static function pickUploadedFile(string $cacheDir): string
    {
        $entries = glob($cacheDir . '/*');
        if ($entries === false) {
            return '';
        }
        $metadataNames = ['merge_settings', 'merging_progress'];
        foreach ($entries as $path) {
            $name = basename($path);
            if ($name === '' || $name[0] === '.') {
                continue;
            }
            if (in_array($name, $metadataNames, true)) {
                continue;
            }
            // Skip resumable.js / merge-worker chunk files: foo.bin.part1, etc.
            if (preg_match('/\.part\d+$/i', $name)) {
                continue;
            }
            if (!is_file($path)) {
                continue;
            }
            return $path;
        }
        return '';
    }

    /**
     * Resolves the {FIRMWARE_URL} placeholder for a device.
     *
     * Lookup order:
     *   1. exact (vendor, model) match
     *   2. (vendor, NULL) — "any model from this vendor" fallback
     *
     * Returns '' if no firmware is registered, no PBX host is configured, or
     * the vendor is unknown. Templates that wrap the placeholder in a vendor-
     * specific config line (e.g. "firmware.url = {FIRMWARE_URL}") will emit
     * the line with an empty value when no firmware is registered — the spec
     * trades safety here for template simplicity.
     */
    public static function resolveFirmwareUrl(string $vendor, ?string $model): string
    {
        $vendor = strtolower(trim($vendor));
        if ($vendor === '' || !isset(self::VENDOR_EXTENSIONS[$vendor])) {
            return '';
        }

        // Two firmware rows can legitimately share (vendor, model) during the
        // window between an admin uploading a newer build and deleting the old
        // one. Sort by uploaded_at desc so phones always pick the most recent
        // file. The upload action enforces uniqueness for newly inserted rows,
        // but historical data and offline edits make the runtime sort the
        // load-bearing piece of correctness here.
        //
        // PnP stores `manufacturer_model` lower-cased (the multicast NOTIFY
        // sends e.g. `yealink / t46s`), while the admin UI accepts the model
        // in whatever case the user types (`T46S`). Compare lower-cased on
        // both sides so the rows still link up.
        $row = null;
        if ($model !== null && $model !== '') {
            $row = ModuleAutoprovisionFirmware::findFirst([
                'vendor = :vendor: AND LOWER(model) = :model:',
                'bind'  => ['vendor' => $vendor, 'model' => strtolower($model)],
                'order' => 'uploaded_at DESC',
            ]);
        }
        if ($row === null) {
            $row = ModuleAutoprovisionFirmware::findFirst([
                'vendor = :vendor: AND (model IS NULL OR model = \'\')',
                'bind'  => ['vendor' => $vendor],
                'order' => 'uploaded_at DESC',
            ]);
        }
        if ($row === null) {
            return '';
        }

        $settings = ModuleAutoprovision::findFirst();
        $host     = trim((string)($settings->pbx_host ?? ''));
        if ($host === '') {
            return '';
        }
        $port = AutoprovisionConf::getHttpPort();

        return sprintf('http://%s:%d/firmware/%s/%s', $host, $port, $vendor, $row->filename);
    }

    /**
     * Sum of bytes currently on disk under the firmware tree. Excludes the lock file
     * and any dot-files so housekeeping artifacts don't push us over the cap.
     */
    public static function totalUsedBytes(): int
    {
        $total = 0;
        $base  = self::baseDir();
        if (!is_dir($base)) {
            return 0;
        }
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));
        /** @var \SplFileInfo $file */
        foreach ($rii as $file) {
            if (!$file->isFile()) {
                continue;
            }
            if (str_starts_with($file->getFilename(), '.')) {
                continue;
            }
            $total += $file->getSize();
        }
        return $total;
    }
}
