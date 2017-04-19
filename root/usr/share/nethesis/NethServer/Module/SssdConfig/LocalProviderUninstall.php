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

/**
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class LocalProviderUninstall extends \Nethgui\Controller\AbstractController {

    public function process()
    {
        parent::process();
        if($this->getRequest()->isMutation()) {
            $this->getPlatform()->signalEvent('nethserver-sssd-remove-provider &');
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if($this->getRequest()->isValidated()) {
            $view['domain'] = $this->getPlatform()->getDatabase('configuration')->getType('DomainName');
            if($this->getRequest()->isMutation()) {
                $view->getCommandList()->hide();
                $this->getPlatform()->setDetachedProcessCondition('success', array(
                    'location' => array(
                        'url' => $view->getModuleUrl('/SssdConfig/Wizard/Cover?uninstallSuccess'),
                        'freeze' => TRUE,
                )));
                $this->getPlatform()->setDetachedProcessCondition('failure', array(
                    'location' => array(
                        'url' => $view->getModuleUrl('/SssdConfig/Wizard/Cover?uninstallFailure&taskId={taskId}'),
                        'freeze' => TRUE,
                )));
            } else {
                $view->getCommandList()->show();
            }
        }
    }

}