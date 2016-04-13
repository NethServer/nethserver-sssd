<?php
namespace NethServer\Module\User;

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
            array('FirstName', Validate::NOTEMPTY, Table::FIELD),
            array('LastName', Validate::NOTEMPTY, Table::FIELD),
            array('Company', Validate::ANYTHING, Table::FIELD),
            array('Department', Validate::ANYTHING, Table::FIELD),
            array('Street', Validate::ANYTHING, Table::FIELD),
            array('City', Validate::ANYTHING, Table::FIELD),
            array('PhoneNumber', Validate::ANYTHING, Table::FIELD),
        );

        $this->setSchema($parameterSchema);
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);

        $groupsAdapter = new MembershipAdapter($this->parameters['username'], $this->getPlatform());
        $this->declareParameter('Groups', Validate::USERNAME_COLLECTION, $groupsAdapter);
        $this->declareParameter('GroupsDatasource', FALSE, array($groupsAdapter, 'provideGroupsDatasource'));

        /*
         * Having declared Groups parameter after "bind()" call we now perform
         * the value assignment by hand.
         */
        if ($request->isMutation() && $request->hasParameter('Groups')) {
            $this->parameters['Groups'] = $request->getParameter('Groups');
        }
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        if ($this->getIdentifier() === 'delete') {
            $v = $this->createValidator(Validate::USERNAME)->platform('user-delete');
            if( ! $v->evaluate($this->getAdapter()->getKeyValue())) {
                $report->addValidationError($this, 'username', $v);
            }
        }
        parent::validate($report);
    }
    /**
     * Delete the record after the event has been successfully completed
     * @param string $key
     */
    protected function processDelete($key)
    {
        $accountDb = $this->getPlatform()->getDatabase('accounts');
        $accountDb->setType($key, 'user-deleted');
        $deleteProcess = $this->getPlatform()->signalEvent('user-delete', array($key));
        if ($deleteProcess->getExitCode() === 0) {
            parent::processDelete($key);
        }
    }

    protected function onParametersSaved($changedParameters)
    {
        if ($this->getIdentifier() === 'delete') {
            // delete case is handled in "processDelete()" method: 
            // signalEvent() is invoked there.
            return;
        } elseif ($this->getIdentifier() === 'update') {
            $event = 'modify';
        } else {
            $event = $this->getIdentifier();
        }
        $this->getPlatform()->signalEvent(sprintf('user-%s@post-process', $event), array($this->parameters['username']));
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['contactDefaults'] = $this->getPlatform()->getDatabase('configuration')->getKey('OrganizationContact');
        if (isset($this->parameters['username'])) {
            $view['ChangePassword'] = $view->getModuleUrl('../ChangePassword/' . $this->parameters['username']);
            $view['FormAction'] = $view->getModuleUrl($this->parameters['username']);
        } else {
            $view['ChangePassword'] = '';
        }
    }

}
