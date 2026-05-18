<?php

declare(strict_types=1);
/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 11 2018
 */

namespace Modules\ModuleAutoprovision\Models;

use MikoPBX\Modules\Models\ModulesModelsBase;

/**
 * Vendor-supplied provisioning template — name + raw template body.
 *
 * Example community-maintained templates:
 * https://github.com/fusionpbx/fusionpbx/tree/master/resources/templates/provision
 */
class Templates extends ModulesModelsBase
{
    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    public $id;

    /**
     * @Column(type="string", nullable=true)
     */
    public $name;

    /**
     * @Column(type="string", nullable=true)
     */
    public $template;

    public function initialize(): void
    {
        $this->setSource('m_Templates');
        parent::initialize();
    }
}
