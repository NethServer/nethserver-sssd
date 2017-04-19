<?php
namespace NethServer\Module;
/*
 * Copyright (C) 2016 Nethesis Srl
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Sssd configuration module
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class SssdConfig extends \Nethgui\Controller\CompositeController
{
    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return new \NethServer\Tool\CustomModuleAttributesProvider($base, array(
            'languageCatalog' => array('NethServer_Module_SssdConfig', 'NethServer_Module_Account'),
            'category' => 'Configuration')
        );
    }

    public function initialize()
    {
        parent::initialize();
        $this->loadChildrenDirectory();
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        $provider = $this->getPlatform()->getDatabase('configuration')->getProp('sssd', 'Provider');

        $firstModuleIdentifier = 'Wizard';
        if(file_exists('/etc/e-smith/db/configuration/defaults/slapd/type')) {
            $firstModuleIdentifier = 'LocalLdapProvider';
        } elseif(file_exists('/etc/e-smith/db/configuration/defaults/nsdc/type')) {
            $firstModuleIdentifier = 'LocalAdProvider';
        } elseif ($provider === 'ldap') {
            $firstModuleIdentifier = 'RemoteLdapProvider';
        } elseif($provider === 'ad') {
            $firstModuleIdentifier = 'RemoteAdProvider';
        }

        // Sort children so that if the Provider prop is "none", it starts the Wizard:
        $this->sortChildren(function ($a, $b) use ($firstModuleIdentifier) {
            if($a->getIdentifier() === $firstModuleIdentifier) {
                $c = -1;
            } elseif($b->getIdentifier() === $firstModuleIdentifier) {
                $c = 1;
            } else {
                $c = 0;
            }
            return $c;
        });

        parent::bind($request);
    }

}
