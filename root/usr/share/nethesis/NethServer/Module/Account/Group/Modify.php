<?php
namespace NethServer\Module\Account\Group;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
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
 * Group modify actions
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Modify extends \Nethgui\Controller\Table\Modify
{

    private function getGroupProvider()
    {
        static $provider;
        if( ! $provider) {
            $provider = new \NethServer\Tool\GroupProvider($this->getPlatform());
        }
        return $provider;
    }

    private function getUserProvider()
    {
        static $provider;
        if( ! $provider) {
            $provider = new \NethServer\Tool\UserProvider($this->getPlatform());
        }
        return $provider;
    }

    private function getUsers()
    {
        static $users;
        if( ! isset($users)) {
            $users = array_keys($this->getUserProvider()->getUsers());
        }
        return $users;
    }

    private function getGroups()
    {
        static $groups;
        if( ! isset($groups)) {
            $groups = array_keys($this->getGroupProvider()->getGroups());
        }
        return $groups;
    }

    private function enumerateMembers()
    {
        if($this->getGroupProvider()->isAD()) {
            return array_unique(array_merge($this->getUsers(), $this->getGroups()));
        }
        return $this->getUsers();
    }

    public function readMembers()
    {
        return $this->getGroupProvider()->getGroupMembers($this->parameters['groupname']);
    }

    public function initialize()
    {
        // The group name must satisfy the USERNAME generic grammar:
        if ($this->getIdentifier() === 'create') {
            $groupNameValidator = $this->createValidator(Validate::USERNAME);
        } else {
            $groupNameValidator = FALSE;
        }

        $parameterSchema = array(
            array('groupname', $groupNameValidator, Table::KEY),
            array('members', Validate::ANYTHING, array($this, 'readMembers')),
        );

        $this->setSchema($parameterSchema);

        $this->declareParameter('CreatePseudoRecords', Validate::YES_NO);
        $this->setDefaultValue('CreatePseudoRecords', 'no');

        parent::initialize();
    }


    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        if ( ! $this->getRequest()->isMutation()) {
            return;
        }

        if ($this->getIdentifier() === 'delete') {
            $v = $this->createValidator()->platform('group-delete');
            if( ! $v->evaluate($this->getAdapter()->getKeyValue())) {
                $report->addValidationError($this, 'groupname', $v);
            }
        }
        if ($this->getIdentifier() === 'update' || $this->getIdentifier() === 'create') {
            $this->getValidator('members')->memberOf($this->enumerateMembers());
        }
        if ($this->getIdentifier() === 'create') {
            $groups = array();
            $groupProvider = new \NethServer\Tool\GroupProvider($this->getPlatform());
            foreach (array_keys($groupProvider->getGroups()) as $u) {
                 $tmp = explode('@',$u);
                 $groups[] = $tmp[0];
            }

            if ( in_array($this->parameters['groupname'], $groups) ) { # group already exists
                $report->addValidationErrorMessage($this, 'groupname', 'group_exists');
            }
        }

        parent::validate($report);
    }

    public function process()
    {
        if ( ! $this->getRequest()->isMutation()) {
            return;
        }

        if ($this->getIdentifier() === 'delete') {
            $this->getPlatform()->signalEvent('group-delete',  array($this->parameters['groupname']));
            $this->getParent()->getAdapter()->flush();
            return;
        } elseif ($this->getIdentifier() === 'update') {
            $event = 'modify';
        } else {
            $event = $this->getIdentifier();
        }
        $params[] = $this->parameters['groupname'];
        if (!$this->parameters['members']) {
            $this->parameters['members'] = array();
        }
        $members = array_unique($this->parameters['members']);
        foreach ($members as $u) {
            $tmp = explode('@',$u);
            $params[] = $tmp[0];
        }

        $this->getPlatform()->signalEvent(sprintf('group-%s', $event), $params);

        // Email group alias creation
        if (($this->getIdentifier() === 'create') && ($this->parameters['CreatePseudoRecords'] === 'yes')) {

            $members = implode(",",array_unique($this->parameters['members']));
            $accountsDb = $this->getPlatform()->getDatabase('accounts');
            $domain = $this->getPlatform()->getDatabase('configuration')->getType('DomainName');
            $groupnameFull = $this->parameters['groupname'] . '@' . $domain;

            if( ! $accountsDb->getKey($groupnameFull)) {
                $accountsDb->setKey($groupnameFull, 'pseudonym', array ('Description'=>'Automatic group mailbox', 'Account' => $members));
            }

            //pseudonyme creation
            $this->getPlatform()->signalEvent('pseudonym-create', array($groupnameFull));

            // Sharedmailbox creation
            $parameters = array ($this->parameters['groupname'], $this->parameters['groupname'], "group=$groupnameFull", "OWNER");
            $this->getPlatform()->signalEvent('sharedmailbox-create', $parameters);
        }

        $this->getParent()->getAdapter()->flush();
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $templates = array(
            'create' => 'NethServer\Template\Group\Modify',
            'update' => 'NethServer\Template\Group\Modify',
            'delete' => 'Nethgui\Template\Table\Delete',
        );
        $view->setTemplate($templates[$this->getIdentifier()]);

        $tmp = array();
        if ($this->getRequest()->isValidated()) {
            foreach ($this->enumerateMembers() as $key) {
                $tmp[] = array($key, $key);
            }
            $view->getCommandList()->show(); // required by nextPath() method of this class
        }
        $view['membersDatasource'] = $tmp;
        $view['domain'] = $this->getPlatform()->getDatabase('configuration')->getType('DomainName');
    }

    public function nextPath()
    {
        // FALSE disables prepareNextViewOptimized() call from parent module:
        return $this->getRequest()->isMutation() ? 'read' : FALSE;
    }
}
