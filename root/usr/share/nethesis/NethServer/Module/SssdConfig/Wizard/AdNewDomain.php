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
class AdNewDomain extends \Nethgui\Controller\AbstractController {

    public function initialize()
    {
        parent::initialize();
        $workgroupValidator = $this->createValidator(Validate::HOSTNAME_SIMPLE)->maxLength(15);
        $realmValidator = $this->createValidator(Validate::HOSTNAME_FQDN);
        $ipAddressValidator = $this->createValidator(Validate::IP)->platform('dcipaddr');

        $this->declareParameter('AdRealm', $realmValidator, array('configuration', 'sssd', 'Realm'));
        $this->declareParameter('AdWorkgroup', $workgroupValidator, array('configuration', 'sssd', 'Workgroup'));
        $this->declareParameter('AdIpAddress', $ipAddressValidator);
    }

    public function readAdWorkgroup($value)
    {
        return $value;
    }

    public function writeAdWorkgroup($value)
    {
        return array(strtoupper($value));
    }

    public function process()
    {
        parent::process();
        if($this->getRequest()->isMutation()) {
            $this->getPlatform()->getDatabase('configuration')->setKey('nsdc', 'service', array('status' => 'enabled', 'IpAddress' => $this->parameters['AdIpAddress']));
            $this->getPlatform()->exec('/usr/bin/sudo /usr/libexec/nethserver/pkgaction --install @nethserver-dc', array(), TRUE);
        }
    }


    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['Back'] = $view->getModuleUrl('../Ad');
        if($this->getRequest()->isValidated()) {
            $view->getCommandList()->show();
            if($this->getRequest()->isMutation()) {
                $this->getPlatform()->setDetachedProcessCondition('success', array(
                    'location' => array(
                        'url' => $view->getModuleUrl('/SssdConfig/LocalAdProvider?installSuccess'),
                        'freeze' => TRUE,
                )));
                $this->getPlatform()->setDetachedProcessCondition('failure', array(
                    'location' => array(
                        'url' => $view->getModuleUrl('/SssdConfig/LocalAdProvider?installFailure&taskId={taskId}'),
                        'freeze' => TRUE,
                )));
            } else {
                // Fill the form with default values
                $realm = strtolower($this->getPlatform()->getDatabase('configuration')->getType('DomainName'));
                $nbdomain = strtoupper(substr(\Nethgui\array_head(explode('.', $realm)), 0, 15));
                $view['AdRealm'] = $realm;
                $view['AdWorkgroup'] = $nbdomain;
            }
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
        }
    }

}