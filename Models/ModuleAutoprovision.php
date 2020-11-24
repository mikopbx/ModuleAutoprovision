<?php
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 10 2019
 */

namespace Modules\ModuleAutoprovision\Models;

use MikoPBX\Common\Models\Extensions;
use MikoPBX\Modules\Models\ModulesModelsBase;
use Phalcon\Mvc\Model\Relation;

class ModuleAutoprovision extends ModulesModelsBase
{

    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    public $id;

    /**
     * Занятый приложением внутренний номер доступный в списках выбора
     *
     * @Column(type="string", nullable=true)
     */
    public $extension;

    /**
     * Black MAC lists
     *
     * @Column(type="string", nullable=true)
     */
    public $mac_black;

    /**
     * White MAC lists
     *
     * @Column(type="string", nullable=true)
     */
    public $mac_white;

    /**
     * PBX host IP address
     *
     * @Column(type="string", nullable=true)
     */
    public $pbx_host;

    /**
     * Additional params
     *
     * @Column(type="string", nullable=true)
     */
    public $additional_params;

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
            $calledModelObject->hasOne(
                'number',
                __CLASS__,
                'extension',
                [
                    'alias'      => 'ModuleAutoprovision',
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
        $this->setSource('m_ModuleAutoprovision');
        parent::initialize();
        $this->belongsTo(
            'extension',
            Extensions::class,
            'number',
            [
                'alias'      => 'Extensions',
                'foreignKey' => [
                    'allowNulls' => false,
                    'action'     => Relation::NO_ACTION,
                ],
            ]
        );
    }

}