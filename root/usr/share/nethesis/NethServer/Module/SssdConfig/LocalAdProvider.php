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
class LocalAdProvider extends \Nethgui\Controller\AbstractController implements \Nethgui\Component\DependencyConsumer
{

    public function initialize()
    {
        parent::initialize();
        $confDb = $this->getPlatform()->getDatabase('configuration');
        $this->declareParameter('AdIpAddress', FALSE, array('configuration', 'nsdc', 'IpAddress'));
        $this->declareParameter('AdRealm', FALSE, function () use ($confDb) {
            return strtolower($confDb->getProp('sssd', 'Realm'));
        });
        $this->declareParameter('AdWorkgroup', FALSE, function () use ($confDb) {
            return strtoupper($confDb->getProp('sssd', 'Workgroup'));
        });
    }
    
    private function readNsSambaRpmVersion()
    {
        $provider = $this->getPlatform()->getDatabase('configuration')->getProp('sssd', 'Provider');
        if($provider !== 'ad') {
            return '';
        }
        $version = $this->getPlatform()->exec('/usr/bin/sudo /usr/libexec/nethserver/read-nssamba-version')->getOutput();
        return trim($version);
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['domain'] = $this->getPlatform()->getDatabase('configuration')->getType('DomainName');
        $view['LocalAdProviderDcChangeIp'] = $view->getModuleUrl('../LocalAdProviderDcChangeIp');
        $view['LocalAdProviderUninstall'] = $view->getModuleUrl('../LocalProviderUninstall');
        $view['LocalAdUpdate'] = $view->getModuleUrl('../LocalAdUpdate');
        $view['AdNsSambaRpmVersion'] = $this->readNsSambaRpmVersion();
        $view['BindDN'] = $this->getPlatform()->getDatabase('configuration')->getProp('sssd', 'BindDN');
        $view['BindPassword'] = $this->getPlatform()->getDatabase('configuration')->getProp('sssd', 'BindPassword');
        $this->notifications->defineTemplate('adminTodo', \NethServer\Module\AdminTodo::TEMPLATE, 'bg-yellow');
        if($this->getRequest()->hasParameter('dcChangeIpSuccess')) {
            $this->notifications->message($view->translate('dcChangeIpSuccess_notification'));
            $view->getCommandList()->show();
        } elseif ($this->getRequest()->hasParameter('dcChangeIpFailure')) {
            $taskStatus = $this->systemTasks->getTaskStatus($this->getRequest()->getParameter('taskId'));
            $data = \Nethgui\Module\Tracker::findFailures($taskStatus);
            $this->notifications->trackerError($data);
        } elseif($this->getRequest()->hasParameter('installSuccess')) {
            $this->notifications->message($view->translate('installSuccessAd_notification'));
            $view->getCommandList()->show();
            $view->getCommandList()->sendQuery($view->getModuleUrl('/AdminTodo?notifications'));
        } elseif ($this->getRequest()->hasParameter('installFailure')) {
            $taskStatus = $this->systemTasks->getTaskStatus($this->getRequest()->getParameter('taskId'));
            $data = \Nethgui\Module\Tracker::findFailures($taskStatus);
            $this->notifications->trackerError($data);
        } elseif($this->getRequest()->isValidated()) {
            $view->getCommandList()->show();
        }
    }


    public function setUserNotifications(\Nethgui\Model\UserNotifications $n)
    {
        $this->notifications = $n;
        return $this;
    }

    public function setSystemTasks(\Nethgui\Model\SystemTasks $t)
    {
        $this->systemTasks = $t;
        return $this;
    }

    public function getDependencySetters()
    {
        return array(
            'UserNotifications' => array($this, 'setUserNotifications'),
            'SystemTasks' => array($this, 'setSystemTasks'),
        );
    }
}
