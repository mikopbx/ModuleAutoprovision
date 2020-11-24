<?php
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 12 2018
 */

namespace Modules\ModuleAutoprovision\Models;

use MikoPBX\Modules\Models\ModulesModelsBase;
use Phalcon\Mvc\Model\Relation;

class ModuleAutoprovisionDevice extends ModulesModelsBase
{

    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    public $id;

    /**
     * Device MAC address
     *
     * @Column(type="string", nullable=true)
     */
    public $mac;

    /**
     * Device manufacturer and model
     *
     * @Column(type="string", nullable=true)
     */
    public $manufacturer_model;

    /**
     * Device IP address
     *
     * @Column(type="string", nullable=true)
     */
    public $host;

    /**
     * Device listening port
     *
     * @Column(type="integer", nullable=true)
     */
    public $port;

    /**
     * Device description
     *
     * @Column(type="string", nullable=true)
     */
    public $description;


    public function initialize(): void
    {
        $this->setSource('m_ModuleAutoprovisionDevice');
        parent::initialize();
        $this->hasOne(
            'id',
            ModuleAutoprovisionUsers::class,
            'id_phone',
            [
                'alias'      => 'ModuleAutoprovisionUsers',
                'foreignKey' => [
                    'allowNulls' => false,
                    'action'     => Relation::ACTION_CASCADE,
                ],
            ]
        );

        $this->hasMany(
            'id',
            ModuleAutoprovisionBLF::class,
            'device_id',
            [
                'alias'      => 'ModuleAutoprovisionBLF',
                'foreignKey' => [
                    'allowNulls' => false,
                    'action'     => Relation::ACTION_CASCADE,
                ],
            ]
        );
    }


}