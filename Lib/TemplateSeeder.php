<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

namespace Modules\ModuleAutoprovision\Lib;

use MikoPBX\Core\System\SystemMessages;
use Modules\ModuleAutoprovision\Models\Templates;
use Modules\ModuleAutoprovision\Models\TemplatesUri;
use Throwable;

/**
 * Loads bundled example provisioning templates from Setup/templates/ into the DB.
 *
 * Idempotent: a seed whose `name` already exists in m_Templates is skipped, so
 * calling seed() twice never creates duplicates and is safe to expose as an
 * on-demand admin action alongside the fresh-install hook.
 */
final class TemplateSeeder
{
    private const LOG_TAG = 'ModuleAutoprovision';

    /**
     * @var list<array{name: string, file: string, uri: string}>
     */
    private const SEEDS = [
        ['name' => 'Yealink (example)',     'file' => 'yealink-example.cfg',     'uri' => '/examples/yealink-common.cfg'],
        ['name' => 'Fanvil (example)',      'file' => 'fanvil-example.txt',      'uri' => '/examples/fanvil-common.txt'],
        ['name' => 'Snom (example)',        'file' => 'snom-example.xml',        'uri' => '/examples/snom-common.xml'],
        ['name' => 'Grandstream (example)', 'file' => 'grandstream-example.xml', 'uri' => '/examples/grandstream-common.xml'],
        ['name' => 'Htek (example)',        'file' => 'htek-example.cfg',        'uri' => '/examples/htek-common.cfg'],
    ];

    /**
     * @return array{installed: list<string>, skipped: list<string>, failed: list<string>}
     */
    public static function seed(): array
    {
        $result = ['installed' => [], 'skipped' => [], 'failed' => []];

        $templatesDir = dirname(__DIR__) . '/Setup/templates';
        foreach (self::SEEDS as $seed) {
            // Skip when either the template name OR the URI is already taken: GetController
            // matches by URI alone, so a duplicate '/examples/...' row would make the served
            // template ambiguous (admins can rename a seeded template and re-click the button,
            // which would otherwise insert a second URI row pointing at the new copy).
            try {
                $existingTemplate = Templates::findFirst([
                    'name = :name:',
                    'bind' => ['name' => $seed['name']],
                ]);
                $existingUri = TemplatesUri::findFirst([
                    'uri = :uri:',
                    'bind' => ['uri' => $seed['uri']],
                ]);
                if ($existingTemplate !== null || $existingUri !== null) {
                    $result['skipped'][] = $seed['name'];
                    continue;
                }
            } catch (Throwable $e) {
                SystemMessages::sysLogMsg(self::LOG_TAG, 'Failed to look up seed template: ' . $e->getMessage());
                $result['failed'][] = $seed['name'];
                continue;
            }

            $path = $templatesDir . '/' . $seed['file'];
            if (!is_readable($path)) {
                SystemMessages::sysLogMsg(self::LOG_TAG, "Seed template not readable: {$path}");
                $result['failed'][] = $seed['name'];
                continue;
            }
            $body = file_get_contents($path);
            if ($body === false) {
                $result['failed'][] = $seed['name'];
                continue;
            }

            $template           = new Templates();
            $template->name     = $seed['name'];
            $template->template = $body;
            if (!$template->save()) {
                SystemMessages::sysLogMsg(self::LOG_TAG, "Failed to save seed template '{$seed['name']}'.");
                $result['failed'][] = $seed['name'];
                continue;
            }

            $uri             = new TemplatesUri();
            $uri->uri        = $seed['uri'];
            $uri->templateId = (string)$template->id;
            if (!$uri->save()) {
                SystemMessages::sysLogMsg(self::LOG_TAG, "Failed to save URI mapping for '{$seed['name']}'.");
                // Roll back the orphan template so the next seed attempt re-creates the pair
                // cleanly — otherwise the name-collision skip at the top would permanently
                // hide the missing URI mapping.
                $template->delete();
                $result['failed'][] = $seed['name'];
                continue;
            }

            $result['installed'][] = $seed['name'];
        }

        return $result;
    }
}
