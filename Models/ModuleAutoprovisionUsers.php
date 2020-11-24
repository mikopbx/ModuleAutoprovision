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


class ModuleAutoprovisionUsers extends ModulesModelsBase
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
     * @Column(type="integer", nullable=true)
     */
    public $id_phone;

    /**
     * Line?
     *
     * @Column(type="string", nullable=true)
     */
    public $line;
    /**
     * Link to Users
     *
     * @Column(type="integer", nullable=true)
     */
    public $userid;

    /**
     * User description
     *
     * @Column(type="string", nullable=true)
     */
    public $description;

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
        if (is_a($calledModelObject, Users::class)) {
            $calledModelObject->hasMany(
                'id',
                __CLASS__,
                'userid',
                [
                    'alias'      => 'ModuleAutoprovisionUsers',
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
        $this->setSource('m_ModuleAutoprovisionUsers');
        parent::initialize();
        $this->belongsTo(
            'userid',
            Users::class,
            'id',
            [
                'alias'      => 'Users',
                'foreignKey' => [
                    'allowNulls' => false,
                    'action'     => Relation::NO_ACTION
                    // ModuleAutoprovisionUsers удаляем через его Users, и естественно не удаляем
                    // Users при удалениии ModuleAutoprovisionUsers
                ],
            ]
        );
        $this->belongsTo(
            'id_phone',
            ModuleAutoprovisionDevice::class,
            'id',
            [
                'alias'      => 'ModuleAutoprovisionDevice',
                'foreignKey' => [
                    'allowNulls' => false,
                    'action'     => Relation::NO_ACTION
                    // ModuleAutoprovisionUsers удаляем через его ModuleAutoprovisionDevice
                ],
            ]
        );
    }

}