<?php

namespace NethServer\Module\Account\AuthProvider;

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
        if ($this->parameters['Provider'] === 'ad') {
            $this->isAuthNeeded = TRUE;
            $this->getPlatform()->signalEvent('nethserver-dnsmasq-save');
        } elseif ($this->parameters['Provider'] === 'ldap') {
            $this->getPlatform()->getDatabase('configuration')->setProp('sssd', array('status' => 'enabled'));
            $this->getPlatform()->signalEvent('nethserver-sssd-save &');
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
                    'url' => $view->getModuleUrl('/Account/AuthProvider/Index?installSuccess'),
                    'freeze' => TRUE,
            )));
        }
        if($this->getRequest()->hasParameter('installSuccess')) {
            $view->getCommandList('/Main')->sendQuery($view->getModuleUrl('/Account'));
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
