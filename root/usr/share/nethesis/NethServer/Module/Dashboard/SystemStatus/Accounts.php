<?php

namespace NethServer\Module\Dashboard\SystemStatus;

/*
 * Copyright (C) 2013 Nethesis S.r.l.
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Retrieve system accounts statistic
 *
 * @author Giacomo Sanchietti
 */
class Accounts extends \Nethgui\Controller\AbstractController implements \Nethgui\Component\DependencyInjectorAggregate
{

    public $sortId = 40;

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $attributes)
    {
        return new \NethServer\Tool\CustomModuleAttributesProvider($attributes, array('languageCatalog' => array('NethServer_Module_Dashboard_SystemStatus_Accounts', 'NethServer_Module_Account')));
    }

    private function getGroupProvider()
    {
        static $provider;
        if( ! $provider) {
            $provider = call_user_func($this->dependencyInjector, new \NethServer\Tool\GroupProvider($this->getPlatform()));
        }
        return $provider;
    }

    public function setDependencyInjector($di)
    {
        $this->dependencyInjector = $di;
        return $this;
    }

    private function readAccounts()
    {
        $accounts = array('user' => 0, 'group' => 0, 'ibay' => 0, 'pseudonym' => 0, 'ftp' => 0, 'vpn' => 0, 'machine' => 0);

        $counters = $this->getGroupProvider()->getAccountCounters(1);
        if(is_array($counters)) {
            $accounts = array_merge($accounts, $counters);
        }

        foreach ($this->getPlatform()->getDatabase('accounts')->getAll() as $record) {
            if ($record['type'] === 'ibay') {
                $accounts['ibay'] += 1;
            } elseif ($record['type'] === 'pseudonym') {
                $accounts['pseudonym'] += 1;
            } elseif ($record['type'] === 'ftp') {
                $accounts['ftp'] += 1;
            } elseif ($record['type'] === 'vpn') {
                $accounts['vpn'] += 1;
            }
        }

        return $accounts;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['provider'] = $this->getPlatform()->getDatabase('configuration')->getProp('sssd', 'Provider');
        $view['accounts'] = $this->readAccounts();

        $this->getGroupProvider()->prepareNotifications($view, FALSE);
    }

}
