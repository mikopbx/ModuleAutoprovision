<?php
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */
namespace Modules\ModuleAutoprovision\App\Controllers;
use MikoPBX\AdminCabinet\Controllers\BaseController;
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
    private $moduleUniqueID =  'ModuleAutoprovision';
    private $moduleDir;

    public function initialize(): void
    {
        $this->moduleDir           = PbxExtensionUtils::getModuleDir($this->moduleUniqueID);
        $this->view->logoImagePath = "{$this->url->get()}assets/img/cache/{$this->moduleUniqueID}/logo.png";
        $this->view->submitMode    = null;
        parent::initialize();

    }

    /**
     * Форма настроек модуля
     */
    public function indexAction(): void
    {
        $footerCollection = $this->assets->collection('footerJS');
        $footerCollection->addJs('js/pbx/main/form.js', true);
        $footerCollection->addJs("js/cache/{$this->moduleUniqueID}/module-autoprovision-index.js", true);
        $footerCollection->addJs('js/vendor/semantic/modal.min.js', true);
        $footerCollection->addJs('js/vendor/jquery.tablednd.min.js', true);


        $headerCollectionCSS = $this->assets->collection('headerCSS');
        $headerCollectionCSS->addCss('css/vendor/semantic/modal.min.css', true);

        $settings = ModuleAutoprovision::findFirst();
        if ($settings === null) {
            $settings = new ModuleAutoprovision();
        }

        $this->view->form      = new ModuleAutoprovisionForm($settings);

        $templates = Templates::find()->toArray();
        array_unshift($templates, ['id' => 'emptyTemplateRow']);
        $this->view->templates = $templates;

        $templatesUsers = TemplatesUsers::find()->toArray();
        array_unshift($templatesUsers, ['id' => 'emptyTemplateRow']);
        $this->view->templatesUsers = $templatesUsers;

        $templatesUri = TemplatesUri::find()->toArray();
        array_unshift($templatesUri, ['id' => 'emptyTemplateRow']);
        $this->view->templatesUri = $templatesUri;

        $otherPBX = OtherPBX::find()->toArray();
        array_unshift($otherPBX, ['id' => 'emptyTemplateRow']);
        $this->view->otherPBX = $otherPBX;

        $this->view->users  = Extensions::find(["type = 'SIP'", 'columns' => ['number', 'callerid', 'userid']])->toArray();

        $this->view->pick("{$this->moduleDir}/App/Views/index");
    }

    /**
     * Сохранение настроек
     */
    public function saveAction():void
    {
        if ( ! $this->request->isPost()) {
            return;
        }
        $data   = $this->request->getPost();
        $record = ModuleAutoprovision::findFirst();
        if ($record === null) {
            $record = new ModuleAutoprovision();
        }

        $extension = $record->Extensions;
        $this->db->begin();
        foreach ($record as $key => $value) {
            switch ($key) {
                case 'id':
                    break;
                case 'extension':
                    $record->$key      = $data[$key];
                    $extension->number = $data[$key];
                    break;
                default:
                    if ( ! array_key_exists($key, $data)) {
                        $record->$key = '';
                    } else {
                        $record->$key = $data[$key];
                    }
            }
        }
        if ($record->save() === false || $extension->save() === false) {
            $errors = $record->getMessages();
            $this->flash->error(implode('<br>', $errors));
            $errors = $extension->getMessages();
            $this->flash->error(implode('<br>', $errors));
            $this->view->success = false;

            $this->db->rollback();

            return;
        }


        $resultSaveTables = $this->saveAdditionalTables($data);

        $this->flash->success($this->translation->_('ms_SuccessfulSaved') . $record->additional_params);
        $this->view->success = true;
        $this->view->resultSaveTables = $resultSaveTables;

        $this->db->commit();
    }

    private function saveAdditionalTables($data):array
    {
        $results = [];
        $additionalTables = [
            'templates'     => Templates::class,
            'templates_uri' => TemplatesUri::class,
            'phone_settings'=> TemplatesUsers::class,
            'other_pbx'     => OtherPBX::class,
        ];

        $tablesData = [];
        foreach ($data as $key => $value) {
            [$table, $column, $id] = explode('-', $key);
            if(!array_key_exists($table, $additionalTables)){
                continue;
            }
            $tablesData[$table][$id][$column] = $value;
        }
        foreach ($tablesData as $table => $dataForWrite){
            foreach ($dataForWrite as $id => $rowData){
                if('emptyTemplateRow' === $id){
                    continue;
                }
                $class = $additionalTables[$table];
                /** @var Templates $class */
                /** @var Templates $dbRowData */
                $dbRowData = $class::findFirst("id='$id'");
                if(!$dbRowData){
                    $dbRowData = new $class();
                }
                foreach ($rowData as $key => $value){
                    $dbRowData->$key = $value;
                }
                $dbRowData->save();
                $results[$table][$id] = $dbRowData->id;
            }
        }

        return $results;
    }

    /**
     * Delete phonebook record
     */
    public function deleteAction(): void
    {
        $table     = $this->request->get('table');
        $className = $this->getClassName($table);
        if(empty($className)) {
            $this->view->success = false;
            return;
        }
        $id     = $this->request->get('id');
        $record = $className::findFirstById($id);
        if ($record !== null && ! $record->delete()) {
            $this->flash->error(implode('<br>', $record->getMessages()));
            $this->view->success = false;
            return;
        }
        $this->view->success = true;
    }

    /**
     * Получение имени класса по имени таблицы
     * @param $tableName
     * @return string
     */
    private function getClassName($tableName):string
    {
        if(empty($tableName)){
            return '';
        }
        $className = "Modules\ModuleAutoprovision\Models\\$tableName";
        if(!class_exists($className)){
            $className = '';
        }
        return $className;
    }
}