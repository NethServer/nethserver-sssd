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
class Ldap extends \Nethgui\Controller\AbstractController {

    public function prepareView(\Nethgui\View\ViewInterface $view) {
        parent::prepareView($view);
        $view['configLdapLocal'] = $view->getModuleUrl('../LdapLocalInstall');
        $view['configLdapRemote'] = $view->getModuleUrl('../LdapRemoteIp');
        $view['Back'] = $view->getModuleUrl('../Cover');
        if($this->getRequest()->isValidated()) {
            $view->getCommandList()->show();
        }
    }
}