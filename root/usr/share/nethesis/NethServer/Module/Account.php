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
        $provider = $this->getPlatform()->getDatabase('configuration')->getProp('sssd', 'Provider');

        $children = array();
        if(class_exists("\NethServer\Module\Account\DomainController")) { # Samba 4 is installed
            if ($provider ==='none') { # and it's not configured, we must configure it
                $children[] = new \NethServer\Module\Account\DomainController();
                $children[] = new \NethServer\Module\Account\Type();
                $children[] = new \NethServer\Module\Account\AuthProvider();
            } else { # already configured, display users
                $children[] = new \NethServer\Module\Account\Type();
                $children[] = new \NethServer\Module\Account\AuthProvider();
                $children[] = new \NethServer\Module\Account\DomainController();
            }
        } else {
            if ($provider ==='none') { # nothing configured, display form for remote provider
                $children[] = new \NethServer\Module\Account\AuthProvider();
                $children[] = new \NethServer\Module\Account\Type();
            } else { # display remote users
                $children[] = new \NethServer\Module\Account\Type();
                $children[] = new \NethServer\Module\Account\AuthProvider();
            }
        } 

        foreach ($children as $child) {
             $this->addChild($child);
        }
    }

}
