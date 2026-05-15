<?php

declare(strict_types=1);
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */
namespace Modules\ModuleAutoprovision\App\Forms;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Form;


class ModuleAutoprovisionForm extends Form
{

    public function initialize($entity = null, $options = null)
    {
        $this->add(new Text('extension'));
        $this->add(new Text('pbx_host'));
        $this->add(new Text('http_port'));
        $this->add(new TextArea('mac_black'));
        $this->add(new TextArea('mac_white'));
        $this->add(new TextArea('additional_params'));

        $tftp = new Check('tftp_enabled', ['value' => '1']);
        // Pre-tick the checkbox when the persisted value is the truthy string '1'.
        // Phalcon's Check element renders `checked` based on the value attribute,
        // so we toggle the element's value (not its default) to drive the state.
        if ($entity !== null && (string)($entity->tftp_enabled ?? '') === '1') {
            $tftp->setAttribute('checked', 'checked');
        }
        $this->add($tftp);

        unset($entity);
        unset($options);
    }
}