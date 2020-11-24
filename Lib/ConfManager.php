<?php
/**
 * Copyright (C) MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Nikolay Beketov, 5 2020
 *
 */

namespace Modules\ModuleAutoprovision\Lib;


interface ConfManager
{
    /**
     * Создает конфигурационный файл для телефона.
     *
     * @param $req_data
     * @param $sip_peers
     *
     * @return string
     */
    public function generateConfig($req_data, $sip_peers): string;
}