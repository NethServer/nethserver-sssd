<?php

namespace NethServer\Module;

/*
 *
 * Copyright (C) 2015 Nethesis S.r.l.
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
 * Manage the password policy
 *
 * @author Stephane de Labrusse <stephdl@de-labrusse.fr>
 */
class Password extends \Nethgui\Controller\AbstractController
{

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        $provider = new \NethServer\Tool\UserProvider($this->getPlatform());
        # hide this module if no provider is installed
        if ($provider->isReadOnly()) {
            return $base;
        } else {
            return \Nethgui\Module\SimpleModuleAttributesProvider::extendModuleAttributes($base, 'Security', 30);
        }
    }

    public function initialize()
    {
        $this->declareParameter('Users', $this->createValidator()->memberOf('none', 'strong'), array('configuration', 'passwordstrength', 'Users'));
        $this->declareParameter('MaxPassAge', Validate::POSITIVE_INTEGER, array('configuration', 'passwordstrength', 'MaxPassAge'));
        $this->declareParameter('MinPassAge', $this->createValidator()->memberOf('0', '30', '60', '90', '180', '365'), array('configuration', 'passwordstrength', 'MinPassAge'));
        $this->declareParameter('PassExpires', $this->createValidator()->memberOf('yes', 'no'), array('configuration', 'passwordstrength', 'PassExpires'));

        parent::initialize();
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);

        $maxPassAgeDatasource = array(
            '30' => $view->translate('${0} days', array(30)),
            '60' => $view->translate('${0} days', array(60)),
            '90' => $view->translate('${0} days', array(90)),
            '180' => $view->translate('${0} days', array(180)),
            '365' => $view->translate('${0} days', array(365)),
        );
        if(!isset($maxPassAgeDatasource[$this->parameters['MaxPassAge']])) {
            $maxPassAgeDatasource[$this->parameters['MaxPassAge']] = $view->translate('${0} days', array($this->parameters['MaxPassAge']));
        }
        \ksort($maxPassAgeDatasource);

        $minPassAgeDatasource = array(
            '0' => $view->translate('${0} days', array(0)),
            '30' => $view->translate('${0} days', array(30)),
            '60' => $view->translate('${0} days', array(60)),
            '90' => $view->translate('${0} days', array(90)),
            '180' => $view->translate('${0} days', array(180)),
            '365' => $view->translate('${0} days', array(365)),
        );
        if(!isset($minPassAgeDatasource[$this->parameters['MinPassAge']])) {
            $minPassAgeDatasource[$this->parameters['MinPassAge']] = $view->translate('${0} days', array($this->parameters['MinPassAge']));
        }
        \ksort($minPassAgeDatasource);

        $view['MaxPassAgeDatasource'] = \Nethgui\Renderer\AbstractRenderer::hashToDatasource($maxPassAgeDatasource);
        $view['MinPassAgeDatasource'] = \Nethgui\Renderer\AbstractRenderer::hashToDatasource($minPassAgeDatasource);
    }

    protected function onParametersSaved($changes)
    {
        $this->getPlatform()->signalEvent('password-policy-update@post-process');
    }

}
