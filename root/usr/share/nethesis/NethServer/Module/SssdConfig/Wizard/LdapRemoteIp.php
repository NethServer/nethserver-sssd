<?php

namespace NethServer\Module\SssdConfig\Wizard;

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
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class LdapRemoteIp extends \Nethgui\Controller\AbstractController {

    public function initialize()
    {
        parent::initialize();
        $self = $this;
        $ldapRemoteIpAddressValueProvider = $this->getPlatform()->getMapAdapter(function ($Provider, $LdapUri, $StartTls, $BaseDn, $GroupDn, $UserDn) use ($self) {
            // Always return an empty string
            return '';
        }, function ($value) use ($self) {
            // Invoke external helper and return prop values for Nethgui serializers:
            $p = $this->getPlatform()->exec('/usr/sbin/account-provider-test probeldap ${@}', array($value, $self->parameters['LdapRemoteTcpPort']));
            $v = json_decode($p->getOutput(), TRUE);
            if(is_array($v)) {
                return array('none', $v['LdapURI'], $v['StartTls'] ? 'enabled' : 'disabled', $v['BaseDN'], $v['GroupDN'], $v['UserDN']);
            }
        }, array(
            array('configuration', 'sssd', 'Provider'),
            array('configuration', 'sssd', 'LdapURI'),
            array('configuration', 'sssd', 'StartTls'),
            array('configuration', 'sssd', 'BaseDN'),
            array('configuration', 'sssd', 'GroupDN'),
            array('configuration', 'sssd', 'UserDN'),
        ));
        $this->declareParameter('LdapRemoteTcpPort', $this->createValidator()->orValidator($this->createValidator(Validate::PORTNUMBER), $this->createValidator(Validate::EMPTYSTRING)));
        $this->declareParameter('LdapRemoteIpAddress', Validate::HOSTNAME, $ldapRemoteIpAddressValueProvider);
    }

    public function prepareView(\Nethgui\View\ViewInterface $view) {
        parent::prepareView($view);
        $view['Back'] = $view->getModuleUrl('../Ldap');
        if($this->getRequest()->isValidated()) {
            if($this->getRequest()->isMutation()) {
                $view->getCommandList()->hide();
            } else {
                $view->getCommandList()->show();
            }
        }
    }

    public function nextPath()
    {
        if($this->getRequest()->isMutation()) {
            return '../RemoteLdapProvider?probeSuccess';
        }
        return FALSE;
    }

}