<?php

namespace NethServer\Module\SssdConfig;

/*
 * Copyright (C) 2014  Nethesis S.r.l.
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This wizard is executed when sssd/Provider prop is "none".
 * Additional wizard steps go to Wizard/ directory.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Wizard extends \Nethgui\Controller\CompositeController
{

    public function initialize()
    {
        parent::initialize();
        $this->loadChildrenDirectory();

        // Force Cover to be the first child (wizard entry point):
        $sortf = function(\Nethgui\Module\ModuleInterface $a, \Nethgui\Module\ModuleInterface $b) {
            if ($a instanceof \NethServer\Module\SssdConfig\Wizard\Cover) {
                return -1;
            }
            if ($b instanceof \NethServer\Module\SssdConfig\Wizard\Cover) {
                return 1;
            }
            return 0;
        };

        $this->sortChildren($sortf);
    }

}
