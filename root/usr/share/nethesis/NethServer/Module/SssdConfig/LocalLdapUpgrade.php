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
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class LocalLdapUpgrade extends \Nethgui\Controller\AbstractController {

    public function initialize()
    {
        parent::initialize();
        $realmValidator = $this->createValidator(Validate::HOSTNAME_FQDN);
        $ipAddressValidator = $this->createValidator(Validate::IP)->platform('dcipaddr');
        $workgroupValidator = FALSE;
        if($this->canChangeWorkgroup()) {
            $workgroupValidator = $this->createValidator(Validate::HOSTNAME_SIMPLE)->maxLength(15);
        }

        $this->declareParameter('AdRealm', $realmValidator);
        $this->declareParameter('AdWorkgroup', $workgroupValidator);
        $this->declareParameter('AdIpAddress', $ipAddressValidator);
    }

    public function canChangeWorkgroup()
    {
        $db = $this->getPlatform()->getDatabase('configuration');
        if($db->getProp('smb', 'ServerRole') === 'PDC') {
            return FALSE;
        }
        return TRUE;
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);
        $db = $this->getPlatform()->getDatabase('configuration');
        if($request->isMutation()) {
            # The form can be disabled: establish the default value
            if( ! $request->hasParameter('AdWorkgroup')) {
                $this->parameters['AdWorkgroup'] = strtoupper($db->getProp('sssd', 'Workgroup'));
            }
        } else {
            $this->parameters['AdRealm'] = strtolower($db->getProp('sssd', 'Realm'));
            $this->parameters['AdWorkgroup'] = strtoupper($db->getProp('sssd', 'Workgroup'));
            if( ! $this->parameters['AdRealm']) {
                $this->parameters['AdRealm'] = 'ad.' . $db->getType('DomainName');
            }
        }
    }

    public function process()
    {
        parent::process();
        if($this->getRequest()->isMutation()) {
            $db = $this->getPlatform()->getDatabase('configuration');
            $db->setProp('sssd', array(
                'Realm' => strtoupper($this->parameters['AdRealm']),
            ));
            if($this->canChangeWorkgroup() && $this->getRequest()->hasParameter('AdWorkgroup')) {
                $db->setProp('sssd', array(
                    'Workgroup' => strtoupper($this->parameters['AdWorkgroup']),
                ));
            }
            $this->getPlatform()->getDatabase('configuration')->setKey('nsdc', 'service', array('IpAddress' => $this->parameters['AdIpAddress']));
            $this->getPlatform()->signalEvent('nethserver-directory-ns6upgrade &');
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if($this->getRequest()->isValidated()) {
            $view['domain'] = $this->getPlatform()->getDatabase('configuration')->getType('DomainName');
            if($this->getRequest()->isMutation()) {
                 $this->getPlatform()->setDetachedProcessCondition('success', array(
                    'location' => array(
                        'url' => $view->getModuleUrl('/SssdConfig/LocalAdProvider?upgradeSuccess'),
                        'freeze' => TRUE,
                )));
                $this->getPlatform()->setDetachedProcessCondition('failure', array(
                    'location' => array(
                        'url' => $view->getModuleUrl('/SssdConfig/LocalAdProvider?upgradeFailure&taskId={taskId}'),
                        'freeze' => TRUE,
                )));
            } else {
                $elements = json_decode($this->getPlatform()->exec('/usr/libexec/nethserver/trusted-networks')->getOutput(), TRUE);
                $greenList = array();
                if(is_array($elements)) {
                    foreach($elements as $elem) {
                        if($elem['provider'] === 'green') {
                            $greenList[] = $elem['cidr'];
                        }
                    }
                }
                $view['greenList'] = implode(', ', array_unique($greenList));
                $view->getCommandList()->show();
            }
        }
    }

}