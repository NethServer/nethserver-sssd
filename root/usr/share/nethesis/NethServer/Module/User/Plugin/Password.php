<?php

namespace NethServer\Module\User\Plugin;

/*
 *
 * Copyright (C) 2015 Nethesis S.r.l.
 * http://www.nethesis.it - nethserver@nethesis.it
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License,
 * or any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see COPYING.
 */

use Nethgui\System\PlatformInterface as Validate;
use Nethgui\Controller\Table\Modify as Table;

/**
 * Password user plugin
 * 
 * @author Stephane de Labrusse <stephdl@de-labrusse.fr> 
 */
class Password extends \Nethgui\Controller\Table\RowPluginAction
{

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return \Nethgui\Module\SimpleModuleAttributesProvider::extendModuleAttributes($base, 'Service', 10);
    }

    public function initialize()
    {
        $this->setSchemaAddition(array(
            array('PassExpires', $this->createValidator()->memberOf('yes', 'no'), Table::FIELD),
        ));
        $this->setDefaultValue('PassExpires', 'yes');
        parent::initialize();
    }

}
