<?php
namespace NethServer\Module\Account\User;

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

    private $provider = null;
    private $currentGroups = array();

    private function getGroupProvider()
    {
        if(!$this->provider) {
            $this->provider = new \NethServer\Tool\GroupProvider($this->getPlatform());
        }
        return $this->provider;
    }

    private function getUserProvider()
    {
        static $userProvider;
        if( ! $userProvider) {
            $userProvider = new \NethServer\Tool\UserProvider($this->getPlatform());
        }
        return $userProvider;
    }

    private function getGroups()
    {
        static $groups;
        if( ! isset($groups)) {
            $groups = array_keys($this->getGroupProvider()->getGroups());
        }
        return $groups;
    }

    public function readGroups()
    {
        return $this->getUserProvider()->getUserMembership($this->parameters['username']);
    }

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
            $userNameValidator = $this->createValidator(Validate::USERNAME);
        } else {
            $userNameValidator = FALSE;
        }
        $parameterSchema = array(
            array('username', $userNameValidator, Table::KEY),
            array('gecos', Validate::NOTEMPTY, Table::FIELD),
            array('groups', Validate::ANYTHING, array($this, 'readGroups')),
            array('expires', $this->createValidator()->memberOf('yes', 'no'), Table::FIELD),
            array('shell', $this->createValidator()->memberOf('/bin/bash', '/usr/libexec/openssh/sftp-server'), Table::FIELD),
            array('setPassword',Validate::SERVICESTATUS),
            array('newPassword', Validate::ANYTHING),
            array('confirmNewPassword', Validate::ANYTHING)
        );

        $this->setSchema($parameterSchema);
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        $this->setCreateDefaults(array(
            'expires' => $this->getPlatform()->getDatabase('configuration')->getProp('passwordstrength', 'PassExpires')
        ));
        parent::bind($request);
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        if ( ! $this->getRequest()->isMutation()) {
            return;
        }

        if ($this->getIdentifier() === 'delete') {
            $v = $this->createValidator()->platform('user-delete');
            if( ! $v->evaluate($this->getAdapter()->getKeyValue())) {
                $report->addValidationError($this, 'username', $v);
            }
        }
        if ($this->getIdentifier() === 'update' || $this->getIdentifier() === 'create') {
            $this->getValidator('groups')->memberOf($this->getGroups());
        }
        if ($this->getIdentifier() === 'create') {
            $users = array();
            if ( $this->parameters['setPassword']==='enabled' ) {
                $passwordValidator = $this->getPlatform()->createValidator()->platform('password-strength', 'Users');

                $this->stash = new \NethServer\Tool\PasswordStash();
                $this->stash->store($this->parameters['newPassword']);

                if ($this->parameters['newPassword'] !== $this->parameters['confirmNewPassword']) {
                    $report->addValidationErrorMessage($this, 'confirmNewPassword', 'ConfirmNoMatch_label');
                } elseif( ! $passwordValidator->evaluate($this->stash->getFilePath())) {
                   $report->addValidationError($this, 'newPassword', $passwordValidator);
                }
            }

            $users = array_keys($this->getUserProvider()->getUsers());

            if ( in_array($this->parameters['username'], $this->stripDomainSuffix($users)) ) { # user already exists
                $report->addValidationErrorMessage($this, 'username', 'user_exists');
            }
        }

        parent::validate($report);
    }

    private function stripDomainSuffix($a)
    {
        $o = array();
        foreach($a as $item) {
            $o[] = \Nethgui\array_head(explode('@', $item));
        }
        return $o;
    }

    private function saveGroups($user, $groups)
    {
        if (!$groups) {
           $groups = array();
        }

        $currentGroups = $this->readGroups();

        $groupsAdded = array_diff($groups, $currentGroups);
        foreach($groupsAdded as $g) {
            $members = $this->getGroupProvider()->getGroupMembers($g);
            $this->getPlatform()->signalEvent('group-modify', $this->stripDomainSuffix(array_merge(array($g), $members, array($user))));
        }

        $groupsRemoved = array_diff($currentGroups, $groups);
        foreach($groupsRemoved as $g) {
            $members = $this->getGroupProvider()->getGroupMembers($g);
            $this->getPlatform()->signalEvent('group-modify', $this->stripDomainSuffix(array_merge(array($g), array_diff($members, array($user)))));
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
        if ($this->getIdentifier() === 'create' && $this->parameters['setPassword']==='enabled'){
            #User created, launch password-modify event
            $this->getPlatform()->signalEvent('password-modify', array($this->parameters['username'].'@'.$this->getPlatform()->getDatabase('configuration')->getType('DomainName'), $this->stash->getFilePath()));
        }
        $this->getParent()->getAdapter()->flush();
    }


    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if ($this->getIdentifier() === 'create' && ! $this->getRequest()->isMutation()) {
            $view['setPassword'] = 'enabled';
        }
        if (isset($this->parameters['username'])) {
            $view['ChangePassword'] = $view->getModuleUrl('../ChangePassword/' . $this->parameters['username']);
            $view['FormAction'] = $view->getModuleUrl($this->parameters['username']);
        } else {
            $view['ChangePassword'] = '';
        }

        $tmp = array();
        if ($this->getRequest()->isValidated()) {
            foreach ($this->getGroups() as $key) {
                $tmp[] = array($key, $key);
            }
            $view->getCommandList()->show(); // required by nextPath() method of this class
        }
        $view['isAD'] = $this->getGroupProvider()->isAD();
        $view['groupsDatasource'] = $tmp;
        $view['domain'] = $this->getPlatform()->getDatabase('configuration')->getType('DomainName');
    }

    public function nextPath()
    {
        // FALSE disables prepareNextViewOptimized() call from parent module:
        return $this->getRequest()->isMutation() ? 'read' : FALSE;
    }

}
