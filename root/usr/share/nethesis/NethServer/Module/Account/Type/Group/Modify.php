<?php
namespace NethServer\Module\Account\Type\Group;

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

    private $provider = null;

    private function getUserProvider()
    {
        if(!$this->provider) {
            $this->provider = new \NethServer\Tool\UserProvider($this->getPlatform());
        }
        return $this->provider;
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
            array('members', Validate::ANYTHING, Table::FIELD),
        );
        
        $this->setSchema($parameterSchema);

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
            $users = array_keys($this->getUserProvider()->getUsers());
            $this->getValidator('members')->memberOf($users);
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
        foreach ($this->getUserProvider()->getUsers() as $key => $values) {
            $tmp[] = array($key, $key);
        }

        $view['membersDatasource'] = $tmp;

    }

}
