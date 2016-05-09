<?php
namespace NethServer\Module\Account\Type\User;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
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

use Nethgui\System\PlatformInterface as Validate;
use Nethgui\Controller\Table\Modify as Table;

/**
 * User modify actions
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @author Stephane de Labrusse <stephdl@de-labrusse.fr> 
 * @since 1.0
 */
class Modify extends \Nethgui\Controller\Table\Modify
{

    public function initialize()
    {
        parent::initialize();
        // after parent's initialization we have Platform correctly set up.

        if (in_array($this->getIdentifier(), array('create', 'update'))) {
            $this->setViewTemplate('NethServer\Template\User\Modify');
        } elseif ($this->getIdentifier() === 'delete') {
            $this->setViewTemplate('Nethgui\Template\Table\Delete');
        }

        // The user name must satisfy the USERNAME generic grammar:
        if ($this->getIdentifier() === 'create') {
            $userNameValidator = $this->createValidator(Validate::USERNAME)->platform('user-create');
        } else {
            $userNameValidator = FALSE;
        }
        $parameterSchema = array(
            array('username', $userNameValidator, Table::KEY),
            array('gecos', Validate::NOTEMPTY, Table::FIELD),
            array('groups', Validate::ANYTHING, Table::FIELD),
            array('expires', $this->createValidator()->memberOf('yes', 'no'), Table::FIELD),
            array('shell', $this->createValidator()->memberOf('/bin/bash', '/usr/libexec/openssh/sftp-server'), Table::FIELD)
        );

        $this->setSchema($parameterSchema);
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        if ($this->getIdentifier() === 'delete') {
            $v = $this->createValidator()->platform('user-delete');
            if( ! $v->evaluate($this->getAdapter()->getKeyValue())) {
                $report->addValidationError($this, 'username', $v);
            }
        }
        parent::validate($report);
    }
    private function saveGroups($user, $groups)
    {
        if (!$groups) {
           $groups = array();
        }
        $updatedGroups = array();
        $provider = new \NethServer\Tool\GroupProvider($this->getPlatform());
        $currentGroups = $provider->getGroups();
        foreach ($currentGroups as $group => $v) {
            $members = $v['members'];
            if (in_array($group, $groups)) { # we must add $user to $group
                $members[] = $user;
                $updatedGroups[$group] = $members;
            }
            if (in_array($user, $members) && !in_array($group, $groups)) { # $user removed from $group
                if(($key = array_search($user, $members)) !== false) { 
                    unset($members[$key]);
                }
                $updatedGroups[$group] = $members;
            }
        }

        # apply the configuration
        foreach ($updatedGroups as $group => $members) {
            $params = array();
            $params[] = $group;
            $members = array_unique($members);
            foreach ($members as $u) {
                $tmp = explode('@',$u);
                $params[] = $tmp[0];
            }
            $this->getPlatform()->signalEvent('group-modify', $params);
        }
    }

    public function process()
    {
        if ( ! $this->getRequest()->isMutation()) {
            return;
        }
        if ($this->getIdentifier() === 'delete') {
            $this->getPlatform()->signalEvent('user-delete', array($this->parameters['username']));
            $this->getParent()->getAdapter()->flush();
            return;
        } elseif ($this->getIdentifier() === 'update') {
            $event = 'modify';
        } else {
            $event = $this->getIdentifier();
        }
        $params = array($this->parameters['username'], $this->parameters['gecos'], $this->parameters['shell']);
        $this->getPlatform()->signalEvent('user-'.$event, $params);
        $this->saveGroups($this->parameters['username'], $this->parameters['groups']);
        $this->getPlatform()->signalEvent('password-policy-update', array($this->parameters['username'], $this->parameters['expires']));
        $this->getParent()->getAdapter()->flush();
    }


    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if (isset($this->parameters['username'])) {
            $view['ChangePassword'] = $view->getModuleUrl('../ChangePassword/' . $this->parameters['username']);
            $view['FormAction'] = $view->getModuleUrl($this->parameters['username']);
        } else {
            $view['ChangePassword'] = '';
        }

        $provider = new \NethServer\Tool\GroupProvider($this->getParent()->getPlatform());
        $tmp = array();
        foreach ($provider->getGroups() as $key => $values) {
            $tmp[] = array($key, $key);
        }

        $view['groupsDatasource'] = $tmp;
    }

}
