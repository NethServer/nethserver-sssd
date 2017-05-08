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
class LocalLdapProvider extends \Nethgui\Controller\AbstractController implements \Nethgui\Component\DependencyConsumer
{
    public function initialize()
    {
        parent::initialize();
    }

    public function canUpgradeToSamba()
    {
        static $result;
        if(isset($result)) {
            return $result;
        }
        if( ! $this->isFirstChild()) {
            $result = FALSE;
            return FALSE;
        }

        $process = $this->getPlatform()->exec("ldapsearch -LLL -H ldapi:/// -x -w '' -D '' -b dc=directory,dc=nh objectClass=sambaDomain 2>/dev/null | grep -q '^sambaDomainName: '");
        if($process->getExitCode() === 0 && !file_exists("/var/run/.nethserver-fixnetwork")) {
            $result = TRUE;
        } else {
            $result = FALSE;
        }
        return $result;
    }

    protected function isFirstChild()
    {
        return $this === \Nethgui\array_head($this->getParent()->getChildren());
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['domain'] = $this->getPlatform()->getDatabase('configuration')->getType('DomainName');
        $view['LocalLdapProviderUninstall'] = $view->getModuleUrl('../LocalProviderUninstall');
        $view['LocalLdapProviderUpgrade'] = $view->getModuleUrl('../LocalLdapUpgrade');

        if($this->getRequest()->hasParameter('installSuccess')) {
            $this->notifications->message($view->translate('installSuccessLdap_notification'));
            $view->getCommandList()->show();
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
