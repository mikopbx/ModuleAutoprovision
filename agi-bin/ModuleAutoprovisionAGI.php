#!/usr/bin/php
<?php
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 11 2018
 */

use Modules\ModuleAutoprovision\Lib\Autoprovision;

require_once 'Globals.php';

$agiWorker    = new Autoprovision();
$agiWorker->StartAGIProvision();
