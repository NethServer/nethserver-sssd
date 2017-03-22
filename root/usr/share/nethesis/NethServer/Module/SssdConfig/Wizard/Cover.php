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

/**
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Cover extends \Nethgui\Controller\AbstractController implements \Nethgui\Component\DependencyConsumer
{

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);
        $sessDb = $this->getPlatform()->getDatabase('SESSION');
        $sessDb->deleteKey(get_class($this->getParent()));
        $sessDb->setType(get_class($this->getParent()), array());
    }

    public function prepareView(\Nethgui\View\ViewInterface $view) {
        parent::prepareView($view);
        $view['configAd'] = $view->getModuleUrl('../Ad');
        $view['configLdap'] = $view->getModuleUrl('../Ldap');
        $config = $this->getPlatform()->getDatabase('configuration');
        $view['domain'] = $config->getType('DomainName');

        if($this->getRequest()->hasParameter('uninstallSuccess')) {
            $this->notifications->message($view->translate('uninstallSuccess_notification'));
            $view->getCommandList()->show();
        } elseif ($this->getRequest()->hasParameter('uninstallFailure')) {
            $taskStatus = $this->systemTasks->getTaskStatus($this->getRequest()->getParameter('taskId'));
            $data = \Nethgui\Module\Tracker::findFailures($taskStatus);
            $this->notifications->trackerError($data);
        } elseif($this->getRequest()->hasParameter('unbindSuccess')) {
            $this->notifications->message($view->translate('unbindSuccess_notification'));
            $view->getCommandList()->show();
        } elseif ($this->getRequest()->hasParameter('unbindFailure')) {
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
