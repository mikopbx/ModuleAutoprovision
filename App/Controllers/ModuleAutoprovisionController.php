<?php
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */
namespace Modules\ModuleAutoprovision\App\Controllers;
use MikoPBX\AdminCabinet\Controllers\BaseController;
use MikoPBX\Modules\PbxExtensionUtils;
use Modules\ModuleAutoprovision\App\Forms\ModuleAutoprovisionForm;
use Modules\ModuleAutoprovision\Models\ModuleAutoprovision;

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

        $settings = ModuleAutoprovision::findFirst();
        if ($settings === null) {
            $settings = new ModuleAutoprovision();
        }

        $this->view->form = new ModuleAutoprovisionForm($settings);
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

        $this->flash->success($this->translation->_('ms_SuccessfulSaved') . $record->additional_params);
        $this->view->success = true;
        $this->db->commit();
    }


}