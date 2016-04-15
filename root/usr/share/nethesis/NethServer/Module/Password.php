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
        return \Nethgui\Module\SimpleModuleAttributesProvider::extendModuleAttributes($base, 'Security', 30);
    }

    public function setDefaultValues($parameterName, $value)
    {
        $this->defaultValues[$parameterName] = $value;
        return $this;
    }

    public function initialize()
    {
        $this->declareParameter('Users', $this->createValidator()->memberOf('none', 'strong'), array('configuration', 'passwordstrength', 'Users'));
        $this->declareParameter('Admin', $this->createValidator()->memberOf('none', 'strong'), array('configuration', 'passwordstrength', 'Admin'));
        $this->declareParameter('MaxPassAge', Validate::POSITIVE_INTEGER, array('configuration', 'passwordstrength', 'MaxPassAge'));
        $this->declareParameter('MinPassAge', $this->createValidator()->memberOf('0', '30', '60', '90', '180', '365'), array('configuration', 'passwordstrength', 'MinPassAge'));
        $this->declareParameter('PassExpires', $this->createValidator()->memberOf('yes', 'no'), array('configuration', 'passwordstrength', 'PassExpires'));
        $this->declareParameter('PassWarning', Validate::POSITIVE_INTEGER, array('configuration', 'passwordstrength', 'PassWarning'));

        $this->setDefaultValues('Users', 'strong');
        $this->setDefaultValues('Admin', 'strong');
        $this->setDefaultValues('MaxPassAge', '180');
        $this->setDefaultValues('MinPassAge', '0');
        $this->setDefaultValues('PassExpires', 'yes');
        $this->setDefaultValues('PassWarning', '7');


        parent::initialize();
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);


        $view['MaxPassAgeDatasource'] = \Nethgui\Renderer\AbstractRenderer::hashToDatasource(array(
                    '30' => $view->translate('${0} days', array(30)),
                    '60' => $view->translate('${0} days', array(60)),
                    '90' => $view->translate('${0} days', array(90)),
                    '180' => $view->translate('${0} days', array(180)),
                    '365' => $view->translate('${0} days', array(365)),
        ));
        $view['MinPassAgeDatasource'] = \Nethgui\Renderer\AbstractRenderer::hashToDatasource(array(
                    '0' => $view->translate('${0} days', array(0)),
                    '30' => $view->translate('${0} days', array(30)),
                    '60' => $view->translate('${0} days', array(60)),
                    '90' => $view->translate('${0} days', array(90)),
                    '180' => $view->translate('${0} days', array(180)),
                    '365' => $view->translate('${0} days', array(365)),
        ));
        $view['PassWarningDatasource'] = \Nethgui\Renderer\AbstractRenderer::hashToDatasource(array(
                    '7' => $view->translate('${0} days', array(7)),
                    '15' => $view->translate('${0} days', array(15)),
                    '30' => $view->translate('${0} days', array(30)),
        ));
    }

    protected function onParametersSaved($changes)
    {
        $this->getPlatform()->signalEvent('password-policy-update@post-process');
    }

}
