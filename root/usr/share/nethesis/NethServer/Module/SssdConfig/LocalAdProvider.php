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
        $this->declareParameter('AdIpAddress', FALSE, array('configuration', 'nsdc', 'IpAddress'));
        $this->declareParameter('AdRealm', FALSE, array('configuration', 'sssd', 'Realm'));
        $this->declareParameter('AdWorkgroup', FALSE, array('configuration', 'sssd', 'Workgroup'));
    }

    public function isSambaUpdateAvailable()
    {
        if( ! file_exists('/etc/e-smith/db/configuration/defaults/nsdc/type')) {
            return FALSE;
        }
        if($this->getPlatform()->exec('/usr/bin/sudo /usr/libexec/nethserver/check-samba-update')->getExitCode() === 0) {
            return FALSE;
        }
        return TRUE;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['domain'] = $this->getPlatform()->getDatabase('configuration')->getType('DomainName');
        $view['LocalAdProviderUninstall'] = $view->getModuleUrl('../LocalProviderUninstall');
        $view['LocalAdUpdate'] = $view->getModuleUrl('../LocalAdUpdate');
        $view['AdRealm'] = $this->getPlatform()->getDatabase('sssd')->getType('Realm');
        if ( !$view['AdRealm'] ) { # only if upgraded from old SSSD implementation
            $view['AdRealm'] = $view['domain'];
        }
        $view['AdWorkgroup'] = $this->getPlatform()->getDatabase('smb')->getType('Workgroup');
        if ( !$view['AdWorkgroup'] ) {
            $tmp = explode('.',$view['AdRealm']);
            $view['AdWorkgroup'] = strtoupper($tmp[0]);
        }
        $this->notifications->defineTemplate('adminTodo', \NethServer\Module\AdminTodo::TEMPLATE, 'bg-yellow');
        if($this->getRequest()->hasParameter('installSuccess')) {
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

        if($this->isSambaUpdateAvailable()) {
            $this->notifications->warning($view->translate('sambaUpdateIsAvailable_notification'));
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
