<?php

namespace NethServer\Module\Sssd;

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
 * Description of Index
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Index extends \Nethgui\Controller\AbstractController
{

    private $details = '';
    private $provider = '';

    public function process()
    {
        parent::process();
        $this->provider = $this->getPlatform()->getDatabase('configuration')->getProp('sssd', 'Provider');
        if ($this->getRequest()->isValidated()) {
            if ($this->provider === 'ad') {
                $this->details .= $this->getPlatform()->exec('/usr/bin/sudo /usr/bin/net ads info 2>&1')->getOutput() . "\n\n";
                $this->details .= $this->getPlatform()->exec('/usr/bin/sudo /usr/bin/net ads testjoin 2>&1')->getOutput() . "\n";

                $netbiosname = substr($this->getPlatform()->getDatabase('configuration')->getType('SystemName'), 0, 15) . '$';
                $searchCmd = $this->getPlatform()->exec("/usr/bin/sudo net ads search -P '(&(sAMAccountName=${netbiosname})(objectCategory=computer))' name sAMAccountName distinguishedName servicePrincipalName objectSid dNSHostName pwdLastSet lastLogon whenCreated whenChanged accountExpires 2>&1");
                if($searchCmd->getExitCode() === 0) {
                    $this->details .= implode("\n", array_slice($searchCmd->getOutputArray(), 3));
                } else {
                    $this->details .= $searchCmd->getOutput();
                }
            } elseif ($this->provider === 'ldap') {
                $this->details = 'LDAP URI: ' . $this->getPlatform()->getDatabase('configuration')->getProp('sssd', 'LdapURI');
            }
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['domain'] = \Nethgui\array_end(explode('.', \gethostname(), 2));
        $view['Provider'] = $this->provider;
        $view['Details'] = $this->details;
    }

}
