<?php
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 11 2018
 */

namespace Modules\ModuleAutoprovision\Models;

use MikoPBX\Modules\Models\ModulesModelsBase;

/**
 * Шаблоны конфиг файлов можно найти по ссылке.
 */
class TemplatesUsers extends ModulesModelsBase
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
    public $userId;

    /**
     *
     * @Column(type="string", nullable=true)
     */
    public $mac;

    /**
     *
     * @Column(type="string", nullable=true)
     */
    public $templateId;

    public static function getDynamicRelations(&$calledModelObject): void
    {
    }

    public function initialize(): void
    {
        $this->setSource('m_TemplatesUsers');
        parent::initialize();
    }
}