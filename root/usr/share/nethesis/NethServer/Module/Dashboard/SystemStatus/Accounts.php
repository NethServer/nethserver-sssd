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
class Accounts extends \Nethgui\Controller\AbstractController
{

    public $sortId = 40;
 
    private $accounts = array();

    private function readAccounts()
    {
        //$accounts = array('users' => 0, 'ibays' => 0, 'groups' => 0);
        $accounts = array();
        $records = $this->getPlatform()->getDatabase('accounts')->getAll();
        foreach ($records as $record) {
            # skip deleted account
            if (strpos($record['type'],'delete') !== FALSE) {
                continue;
            }
            if (!isset($accounts[$record['type']])) {
                $accounts[$record['type']] = 0;
            }
            $accounts[$record['type']] = $accounts[$record['type']]+1;
        }
 
        return $accounts;
    }

    public function process()
    {
        $this->accounts = $this->readAccounts();
    }
 
    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        if (!$this->accounts) {
            $this->accounts = $this->readAccounts();
        }

        $view['accounts'] = $this->accounts;
    }
}
