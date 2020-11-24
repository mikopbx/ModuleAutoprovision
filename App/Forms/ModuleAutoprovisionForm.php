<?php
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */
namespace Modules\ModuleAutoprovision\App\Forms;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Form;


class ModuleAutoprovisionForm extends Form
{

    public function initialize($entity = null, $options = null)
    {
        $this->add(new Text('extension'));
        $this->add(new Text('pbx_host'));
        $this->add(new TextArea('mac_black'));
        $this->add(new TextArea('mac_white'));
        $this->add(new TextArea('additional_params'));

        unset($entity);
        unset($options);
    }
}