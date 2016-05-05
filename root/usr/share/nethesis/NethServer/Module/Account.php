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
class Account extends \Nethgui\Controller\CompositeController
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
        $this->addChild(new \NethServer\Module\Account\Type());
        $this->addChild(new \NethServer\Module\Account\AuthProvider());        
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        $provider = $this->getPlatform()->getDatabase('configuration')->getProp('sssd', 'Provider');
        if ($provider === 'none') {
            $this->sortChildren(function ($a, $b) {
                if ($a->getIdentifier() === 'AuthProvider') {
                    return -1;
                }
                return 0;
            });
        }

        parent::prepareView($view);
    }

}
