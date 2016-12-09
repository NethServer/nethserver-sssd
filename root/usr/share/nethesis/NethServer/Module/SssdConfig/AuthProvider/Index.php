<?php

namespace NethServer\Module\SssdConfig\AuthProvider;

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
class Index extends \Nethgui\Controller\AbstractController
{

    protected $isAuthNeeded = FALSE;
    protected $reloadPage = FALSE;

    public function initialize()
    {
        parent::initialize();
        $providerValidator = $this->createValidator()->memberOf('none', 'ldap', 'ad');
        $ldapUriAdapter = $this->getPlatform()->getMapAdapter(function($v) {
            return preg_replace('|^ldaps?://|', '', $v);
        }, function($v) {
            return array('ldap://' . $v);
        }, array(array('configuration', 'sssd', 'LdapURI')));
        $this->declareParameter('Provider', $providerValidator, array('configuration', 'sssd', 'Provider'));
        $this->declareParameter('LdapUri', Validate::HOSTADDRESS, $ldapUriAdapter);
        $this->declareParameter('AdDns', Validate::IP_OR_EMPTY, array('configuration', 'sssd', 'AdDns'));
        $this->declareParameter('BindDN', Validate::ANYTHING, array('configuration', 'sssd', 'BindDN'));
        $this->declareParameter('BindPassword', Validate::ANYTHING, array('configuration', 'sssd', 'BindPassword'));
        $this->declareParameter('BaseDN', Validate::ANYTHING, array('configuration', 'sssd', 'BaseDN'));
        $this->declareParameter('UserDN', Validate::ANYTHING, array('configuration', 'sssd', 'UserDN'));
        $this->declareParameter('GroupDN', Validate::ANYTHING, array('configuration', 'sssd', 'GroupDN'));
        $this->declareParameter('RawLdapUri', Validate::ANYTHING, array('configuration', 'sssd', 'LdapURI'));
        $this->declareParameter('StartTls', $this->createValidator()->memberOf('', 'enabled', 'disabled'), array('configuration', 'sssd', 'StartTls'));
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        if ($this->parameters['Provider'] === 'ad') {
            $this->getValidator('AdDns')->platform('ad-dns');
        }
        parent::validate($report);
    }

    protected function onParametersSaved($changedParameters)
    {
        $configDb = $this->getPlatform()->getDatabase('configuration');

        if ($this->parameters['Provider'] === 'ad') {
            if( ! in_array('Provider', $changedParameters)) {
                $this->getPlatform()->signalEvent('nethserver-sssd-save &');
            } else {
                $this->isAuthNeeded = TRUE;
                $this->getPlatform()->signalEvent('nethserver-dnsmasq-save');
            }
        } elseif ($this->parameters['Provider'] === 'ldap') {
            $configDb->setProp('sssd', array('status' => 'enabled'));
            $this->getPlatform()->signalEvent('nethserver-sssd-save &');
            $this->reloadPage = TRUE;
        } else {
            $configDb->setProp('sssd', array('status' => 'disabled'));
            $configDb->delProp('sssd', array(
                'BaseDN',
                'BindDN',
                'BindPassword',
                'UserDN',
                'GroupDN',
                'LdapURI',
                'StartTls',
            ));
            $this->getPlatform()->signalEvent('nethserver-sssd-save');
            $this->getPlatform()->signalEvent('nethserver-sssd-leave &');
            $this->reloadPage = TRUE;
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['domain'] = $this->getPlatform()->getDatabase('configuration')->getType('DomainName');
        $view['NetbiosDomain'] = $this->getPlatform()->getDatabase('configuration')->getProp('smb', 'Workgroup');
        if (!$view['NetbiosDomain']) {
            $view['NetbiosDomain'] = \Nethgui\array_head(explode('.', $view['domain']));
        }
        $view['NetbiosDomain'] = strtoupper(substr($view['NetbiosDomain'], 0, 15));
        if($this->getRequest()->isMutation() && $this->reloadPage) {
            $this->getPlatform()->setDetachedProcessCondition('success', array(
                'location' => array(
                    'url' => $view->getModuleUrl('/SssdConfig/AuthProvider/Index?installSuccess'),
                    'freeze' => TRUE,
            )));
        }
        if($this->getRequest()->hasParameter('installSuccess')) {
            $view->getCommandList('/Main')->sendQuery($view->getModuleUrl('/SssdConfig'));
        }
        
        $view['sssd_defaults'] = array_merge(
            array(
                'baseDN' => '',
                'bindDN' => '',
                'bindPassword' => '',
                'userDN' => '',
                'groupDN' => '',
                'ldapURI' => '',
                'host' => '',
                'port' => '',
                'startTls'=> '',
                'isAD' => '',
                'isLdap' => '',
            ),
            json_decode($this->getPlatform()->exec('/usr/bin/sudo /usr/libexec/nethserver/sssd-defaults')->getOutput(), TRUE)
        );
        if($view['sssd_defaults']['bindPassword']) {
            $view['sssd_defaults'] = array_merge($view['sssd_defaults'], array('bindPassword' => '*****'));
        }
        
    }

    public function nextPath()
    {
        if ($this->isAuthNeeded) {
            return 'Authenticate';
        } elseif($this->reloadPage) {
            return FALSE;
        }
        return parent::nextPath();
    }

}
