<?php

namespace NethServer\Module\SssdConfig;

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

use Nethgui\System\PlatformInterface as Validate;

/**
 * List the available provider types
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class RemoteLdapProvider extends \Nethgui\Controller\AbstractController implements \Nethgui\Component\DependencyConsumer
{

    public function initialize()
    {
        $self = $this;
        $bindTypeValueProvider = $this->getPlatform()->getMapAdapter(function () use ($self) {
            if($self->parameters['BindDN']) {
                return 'authenticated';
            }
            return 'anonymous';
        }, function ($value) use ($self) {
            if($value === 'anonymous') {
                $self->parameters['BindDN'] = '';
                $self->parameters['BindPassword'] = '';
                return TRUE;
            }
        }, array());

        parent::initialize();
        $this->declareParameter('LdapUri', Validate::ANYTHING, array('configuration', 'sssd', 'LdapURI'));
        $this->declareParameter('BindType', $this->createValidator()->memberOf('authenticated', 'anonymous'), $bindTypeValueProvider);
        $this->declareParameter('BindDN', Validate::ANYTHING, array('configuration', 'sssd', 'BindDN'));
        $this->declareParameter('BindPassword', Validate::ANYTHING, array('configuration', 'sssd', 'BindPassword'));
        $this->declareParameter('BaseDN', Validate::ANYTHING, array('configuration', 'sssd', 'BaseDN'));
        $this->declareParameter('UserDN', Validate::ANYTHING, array('configuration', 'sssd', 'UserDN'));
        $this->declareParameter('GroupDN', Validate::ANYTHING, array('configuration', 'sssd', 'GroupDN'));
        $this->declareParameter('StartTls', $this->createValidator()->memberOf('', 'enabled', 'disabled'), array('configuration', 'sssd', 'StartTls'));
    }

    public function process()
    {
        parent::process();
        if( ! $this->getRequest()->isMutation()) {
            return;
        }

        $configDb = $this->getPlatform()->getDatabase('configuration');
        $configDb->setProp('sssd', array('status' => 'enabled', 'Provider' => 'ldap'));
        $this->getPlatform()->signalEvent('nethserver-sssd-save &');
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);
        if($request->isMutation() && $this->parameters['BindDN'] === '') {
            $this->parameters['BindType'] = 'anonymous';
            $this->parameters['BindPassword'] = '';
        }
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        parent::validate($report);
        if( ! $this->getRequest()->isMutation()) {
            return;
        }
        if($this->parameters['StartTls'] === 'enabled' && substr($this->parameters['LdapUri'], 0, 6) === 'ldaps:') {
            $report->addValidationErrorMessage($this, 'StartTls', 'valid_starttls_urischeme');
        }
        $credentialsValidator = $this->getPlatform()->createValidator()->platform('ldap-credentials', $this->parameters['BaseDN'], $this->parameters['LdapUri'], $this->parameters['StartTls'] === 'enabled' ? '1' : '', $this->parameters['BindDN']);
        if( ! $credentialsValidator->evaluate($this->parameters['BindPassword'])) {
            $report->addValidationError($this, 'BindType', $credentialsValidator);
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);

        if( ! $view['BindType']) {
            $view['BindType'] = $view['BindDN'] ? 'authenticated' : 'anonymous';
        }

        $view['domain'] = $this->getPlatform()->getDatabase('configuration')->getType('DomainName');
        $view['RemoteProviderUnbind'] = $view->getModuleUrl('../RemoteProviderUnbind');
        if($this->getRequest()->isValidated()) {
            $view->getCommandList()->show();
            if($this->getRequest()->hasParameter('probeSuccess')) {
                $this->notifications->warning($view->translate('probeLdapSuccess_warning'));
            }
        }
    }

    public function setUserNotifications(\Nethgui\Model\UserNotifications $n)
    {
        $this->notifications = $n;
        return $this;
    }

    public function getDependencySetters()
    {
        return array(
            'UserNotifications' => array($this, 'setUserNotifications'),
        );
    }

}
