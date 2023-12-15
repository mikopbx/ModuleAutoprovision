<?php
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 11 2018
 */

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 21/11/2018
 * Time: 13:57
 */

namespace Modules\ModuleAutoprovision\Models;

use MikoPBX\Common\Models\Users;
use MikoPBX\Modules\Models\ModulesModelsBase;
use Phalcon\Mvc\Model\Relation;

/**
 */
class OtherPBX extends ModulesModelsBase
{

    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    public $id;

    /**
     *
     * @Column(type="string", nullable=true)
     */
    public $name;

    /**
     *
     * @Column(type="string", nullable=true)
     */
    public $address;

    public static function getDynamicRelations(&$calledModelObject): void
    {
    }

    public function initialize(): void
    {
        $this->setSource('m_OtherPBX');
        parent::initialize();
    }
}