<?php

namespace NethServer\Module\SssdConfig;

/*
 * Copyright (C) 2017 Nethesis S.r.l.
 * http://www.nethesis.it - nethserver@nethesis.it
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License,
 * or any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see COPYING.
 */

 use Nethgui\System\PlatformInterface as Validate;

 /**
  * @author Davide Principi <davide.principi@nethesis.it>
  */
class RemoteAdProvider extends \Nethgui\Controller\AbstractController  implements \Nethgui\Component\DependencyConsumer
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
            if($value === '') {
                $self->parameters['BindDN'] = '';
                $self->parameters['BindPassword'] = '';
                return TRUE;
            }
        }, array());

        parent::initialize();
        $this->declareParameter('LdapUri', Validate::ANYTHING, array('configuration', 'sssd', 'LdapURI'));
        $this->declareParameter('BindType', $this->createValidator()->memberOf('authenticated', ''), $bindTypeValueProvider);
        $this->declareParameter('BindDN', Validate::ANYTHING, array('configuration', 'sssd', 'BindDN'));
        $this->declareParameter('BindPassword', Validate::ANYTHING, array('configuration', 'sssd', 'BindPassword'));
        $this->declareParameter('BaseDN', Validate::ANYTHING, array('configuration', 'sssd', 'BaseDN'));
        $this->declareParameter('UserDN', Validate::ANYTHING, array('configuration', 'sssd', 'UserDN'));
        $this->declareParameter('GroupDN', Validate::ANYTHING, array('configuration', 'sssd', 'GroupDN'));
        $this->declareParameter('StartTls', $this->createValidator()->memberOf('', 'enabled', 'disabled'), array('configuration', 'sssd', 'StartTls'));
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        parent::validate($report);
        if($this->parameters['StartTls'] === 'enabled' && substr($this->parameters['LdapUri'], 0, 6) === 'ldaps:') {
            $report->addValidationErrorMessage($this, 'StartTls', 'valid_starttls_urischeme');
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);

        if( ! $view['BindType']) {
            $view['BindType'] = $view['BindDN'] ? 'simple' : '';
        }

        $view['domain'] = $this->getPlatform()->getDatabase('configuration')->getType('DomainName');
        $view['RemoteProviderUnbind'] = array($view->getModuleUrl('../RemoteProviderUnbind'), $view->translate('RemoteProviderUnbind_label', array($view['domain'])));

        $starttlsChoices = array(
            array('disabled', $view->translate('starttls_disabled')),
            array('enabled', $view->translate('starttls_enabled')),
        );
        // Display StartTls "default" value for backword compatibility
        if ($this->parameters['StartTls'] === '') {
            $starttlsChoices[] = array('', $view->translate('starttls_auto'));
        }
        $view['StartTlsDatasource'] = $starttlsChoices;

        if($this->getRequest()->isValidated()) {
            $view->getCommandList()->show();
            if($this->getRequest()->hasParameter('bindSuccess')) {
                $realm = $this->getPlatform()->getDatabase('configuration')->getProp('sssd', 'Realm');
                $this->notifications->message($view->translate('bindAdSuccess_notification', array(strtolower($realm))));
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