<?php
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 8 2020
 */

namespace Modules\ModuleAutoprovision\Lib\RestAPI\Controllers;
use MikoPBX\PBXCoreREST\Controllers\Modules\ModulesControllerBase;
class GetController extends ModulesControllerBase
{
    /**
     */
    public function getConfig(): void
    {
        $this->callActionForModule('ModuleAutoprovision', 'getProvisionConfig');
        $this->response->sendRaw();
    }

    /**
     */
    public function getImg(): void
    {
        $this->callActionForModule('ModuleAutoprovision', 'getImgFile');
        $this->response->sendRaw();
    }
}