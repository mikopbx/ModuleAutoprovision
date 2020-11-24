<?php
/**
 * Copyright (C) MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Nikolay Beketov, 10 2018
 *
 */

namespace Modules\ModuleAutoprovision\Models;

use MikoPBX\Common\Models\Extensions;
use MikoPBX\Modules\Models\ModulesModelsBase;
use Phalcon\Mvc\Model\Relation;

class ModuleAutoprovisionBLF extends ModulesModelsBase
{

    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    public $id;

    /**
     * Link to ModuleAutoprovisionDevice
     *
     * @Column(type="integer", default="0", nullable=true)
     */
    public $device_id;

    /**
     * BLF label
     *
     * @Column(type="string", nullable=true)
     */
    public $label;

    /**
     * White MAC lists
     *
     * @Column(type="string", nullable=true)
     */
    public $extension;

    /**
     * BLF list sorting priority
     *
     * @Column(type="integer", nullable=true)
     */
    public $priority;

    /**
     * Returns dynamic relations between module models and common models
     * MikoPBX check it in ModelsBase after every call to keep data consistent
     *
     * There is example to describe the relation between Providers and ModuleTemplate models
     *
     * It is important to duplicate the relation alias on message field after Models\ word
     *
     * @param $calledModelObject
     *
     * @return void
     */
    public static function getDynamicRelations(&$calledModelObject): void
    {
        if (is_a($calledModelObject, Extensions::class)) {
            $calledModelObject->hasMany(
                'number',
                __CLASS__,
                'extension',
                [
                    'alias'      => 'ModuleAutoprovisionBLF',
                    'foreignKey' => [
                        'allowNulls' => 0,
                        'message'    => __CLASS__,
                        'action'     => Relation::ACTION_CASCADE,
                    ],
                ]
            );
        }
    }


    public function initialize(): void
    {
        $this->setSource('m_ModuleAutoprovisionBLF');
        parent::initialize();
        $this->belongsTo(
            'extension',
            Extensions::class,
            'number',
            [
                'alias'      => 'Extensions',
                'foreignKey' => [
                    'allowNulls' => true,
                    'action'     => Relation::NO_ACTION,
                    // ModuleAutoprovisionUsers удаляем через его Users, и естественно не удаляем
                    // Users при удалениии ModuleAutoprovisionUsers
                ],
            ]
        );
        $this->belongsTo(
            'device_id',
            ModuleAutoprovisionDevice::class,
            'id',
            [
                'alias'      => 'ModuleAutoprovisionDevice',
                'foreignKey' => [
                    'allowNulls' => false,
                    'action'     => Relation::NO_ACTION,
                    // ModuleAutoprovisionUsers удаляем через его ModuleAutoprovisionDevice, и естественно не удаляем
                    // ModuleAutoprovisionDevice при удалениии ModuleAutoprovisionUsers
                ],
            ]
        );
    }


}