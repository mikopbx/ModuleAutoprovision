<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */

namespace Modules\ModuleAutoprovision\App\Controllers;

use MikoPBX\AdminCabinet\Controllers\BaseController;
use MikoPBX\AdminCabinet\Providers\AssetProvider;
use MikoPBX\Common\Models\Extensions;
use MikoPBX\Modules\PbxExtensionUtils;
use Modules\ModuleAutoprovision\App\Forms\ModuleAutoprovisionForm;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovision;
use Modules\ModuleAutoprovision\Models\OtherPBX;
use Modules\ModuleAutoprovision\Models\Templates;
use Modules\ModuleAutoprovision\Models\TemplatesUri;
use Modules\ModuleAutoprovision\Models\TemplatesUsers;

class ModuleAutoprovisionController extends BaseController
{
    private const MODULE_UNIQUE_ID = 'ModuleAutoprovision';

    /**
     * Map of form-section prefix → model class. Form input names use the prefix as a column-table key
     * (e.g. <input name="templates_uri-uri-7">). Keep this in sync with App/Views/index.volt and
     * public/assets/js/src/module-autoprovision.js → tableMap.
     */
    private const TABLE_MAP = [
        'templates'      => Templates::class,
        'templates_uri'  => TemplatesUri::class,
        'phone_settings' => TemplatesUsers::class,
        'other_pbx'      => OtherPBX::class,
    ];

    /**
     * Columns of ModuleAutoprovision that must never be writable through the admin form.
     */
    private const PROTECTED_COLUMNS = ['id', 'sip_secret'];

    private string $moduleDir;

    public function initialize(): void
    {
        $this->moduleDir           = PbxExtensionUtils::getModuleDir(self::MODULE_UNIQUE_ID);
        $this->view->logoImagePath = "{$this->url->get()}assets/img/cache/" . self::MODULE_UNIQUE_ID . '/logo.png';
        $this->view->submitMode    = null;
        parent::initialize();
    }

    /**
     * Renders the module settings page.
     */
    public function indexAction(): void
    {
        $headerCss = $this->assets->collection(AssetProvider::HEADER_CSS);
        $headerCss->addCss('css/cache/' . self::MODULE_UNIQUE_ID . '/module-autoprovision.css', true);

        $footerJs = $this->assets->collection(AssetProvider::FOOTER_JS);
        $footerJs
            ->addJs('js/pbx/main/form.js', true)
            ->addJs('js/cache/' . self::MODULE_UNIQUE_ID . '/module-autoprovision.js', true);

        $settings = ModuleAutoprovision::findFirst() ?? new ModuleAutoprovision();
        $this->view->form = new ModuleAutoprovisionForm($settings);

        $this->view->templates      = $this->prependEmptyRow(Templates::find()->toArray());
        $this->view->templatesUsers = $this->prependEmptyRow(TemplatesUsers::find()->toArray());
        $this->view->templatesUri   = $this->prependEmptyRow(TemplatesUri::find()->toArray());
        $this->view->otherPBX       = $this->prependEmptyRow(OtherPBX::find()->toArray());

        $this->view->users = Extensions::find([
            "type = 'SIP'",
            'columns' => ['number', 'callerid', 'userid'],
        ])->toArray();

        $this->view->pick("{$this->moduleDir}/App/Views/ModuleAutoprovision/index");
    }

    /**
     * Persists the module's main settings and any inline tables edited on the form.
     */
    public function saveAction(): void
    {
        if (!$this->request->isPost()) {
            return;
        }
        $data   = $this->request->getPost();
        $record = ModuleAutoprovision::findFirst() ?? new ModuleAutoprovision();

        $this->db->begin();

        // Snapshot the model's column names; iterating $record directly is unreliable in Phalcon 5.
        $columns = array_keys($record->toArray());
        foreach ($columns as $column) {
            if (in_array($column, self::PROTECTED_COLUMNS, true)) {
                continue;
            }
            if ($column === 'extension' && isset($data['extension'])) {
                $record->extension = (string)$data['extension'];
                continue;
            }
            $record->{$column} = array_key_exists($column, $data) ? $data[$column] : '';
        }

        if ($record->save() === false) {
            $this->flash->error(implode('<br>', $record->getMessages()));
            $this->view->success = false;
            $this->db->rollback();
            return;
        }

        // Keep the Extensions row's number in sync with the dialplan pattern.
        if (isset($data['extension'])) {
            $extensionRow = $record->Extensions ?? Extensions::findFirst([
                'number = :number:',
                'bind' => ['number' => (string)$data['extension']],
            ]);
            if ($extensionRow !== null) {
                $extensionRow->number = (string)$data['extension'];
                if ($extensionRow->save() === false) {
                    $this->flash->error(implode('<br>', $extensionRow->getMessages()));
                    $this->view->success = false;
                    $this->db->rollback();
                    return;
                }
            }
        }

        $resultSaveTables = $this->saveAdditionalTables($data);

        $this->flash->success(
            $this->translation->_('ms_SuccessfulSaved') . ($record->additional_params ?? '')
        );
        $this->view->success          = true;
        $this->view->resultSaveTables = $resultSaveTables;

        $this->db->commit();
    }

    /**
     * Returns the table → [oldId → newId] map used by the JS layer to re-bind inserted rows.
     *
     * @param array<string, mixed> $data Raw POST body, with keys shaped like "<table>-<column>-<id>".
     * @return array<string, array<string, string>>
     */
    private function saveAdditionalTables(array $data): array
    {
        $results    = [];
        $tablesData = [];
        foreach ($data as $key => $value) {
            $parts = explode('-', (string)$key, 3);
            if (count($parts) !== 3) {
                continue;
            }
            [$table, $column, $id] = $parts;
            if (!array_key_exists($table, self::TABLE_MAP)) {
                continue;
            }
            $tablesData[$table][$id][$column] = $value;
        }

        foreach ($tablesData as $table => $rowsByMockId) {
            foreach ($rowsByMockId as $id => $rowData) {
                if ($id === 'emptyTemplateRow') {
                    continue;
                }
                $class = self::TABLE_MAP[$table];
                /** @var \Phalcon\Mvc\Model|null $dbRow */
                $dbRow = $class::findFirst([
                    'id = :id:',
                    'bind' => ['id' => $id],
                ]);
                if ($dbRow === null) {
                    $dbRow = new $class();
                }
                foreach ($rowData as $column => $value) {
                    $dbRow->{$column} = $value;
                }
                if ($dbRow->save()) {
                    $results[$table][$id] = (string)$dbRow->id;
                }
            }
        }

        return $results;
    }

    /**
     * Deletes a single row from one of the inline tables.
     */
    public function deleteAction(): void
    {
        $table     = (string)$this->request->get('table');
        $className = $this->resolveModelClass($table);
        if ($className === '') {
            $this->view->success = false;
            return;
        }

        $id     = (string)$this->request->get('id');
        $record = $className::findFirst([
            'id = :id:',
            'bind' => ['id' => $id],
        ]);
        if ($record !== null && !$record->delete()) {
            $this->flash->error(implode('<br>', $record->getMessages()));
            $this->view->success = false;
            return;
        }
        $this->view->success = true;
    }

    /**
     * Whitelist-resolves a user-supplied short table name to a fully qualified model class.
     *
     * @return class-string<\Phalcon\Mvc\Model>|string Empty string when the name does not match.
     */
    private function resolveModelClass(string $tableName): string
    {
        $allowed = [
            'Templates'      => Templates::class,
            'TemplatesUri'   => TemplatesUri::class,
            'TemplatesUsers' => TemplatesUsers::class,
            'OtherPBX'       => OtherPBX::class,
        ];
        return $allowed[$tableName] ?? '';
    }

    /**
     * Prepends a template-row placeholder used by the JS layer to clone new rows from.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function prependEmptyRow(array $rows): array
    {
        array_unshift($rows, ['id' => 'emptyTemplateRow']);
        return $rows;
    }
}
