<?php

namespace NethServer\Module;

/*
 * Copyright (C) 2016 Nethesis Srl
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of Account
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Account extends \Nethgui\Controller\TabsController
{
    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return new \NethServer\Tool\CustomModuleAttributesProvider($base, array(
            'category' => 'Management')
        );
    }

    public function initialize()
    {
        parent::initialize();
        $this->addChild(new Account\User());
        $this->addChild(new Account\Group());
    }
    
    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if( ! $this->getRequest()->isValidated()) {
            return;
        }
        $db = $this->getPlatform()->getDatabase('configuration');
        $provider = $db->getProp('sssd', 'Provider');

        if($provider === 'none') {
            $view['domain'] = $db->getType('DomainName');
            $view->setTemplate('NethServer\Template\Account\NoConfig');
        } 
    }

}
