<?php
namespace NethServer\Tool;

/*
 * Copyright (C) 2016 Nethesis S.r.l.
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
 * Return the list of groups accordingly to SSSD provider: AD or LDAP
 *
 * @author Giacomo Sanchietti <giacomo.sanchietti@nethesis.it>
 */
class GroupProvider
{

    private $listGroupsCommand = '';
    private $platform;
    private $ad = false;

    public function getGroups()
    {
        $provider = $this->platform->getDatabase('configuration')->getProp('sssd', 'Provider');
        if ($provider != 'ad' && $provider != 'ldap') {
            return array();
        }

        if ($this->listGroupsCommand) {
            $groups = json_decode(exec('/usr/bin/sudo '.$this->listGroupsCommand), TRUE);
        } else { # groups from remote server
            $groups = $this->platform->getDatabase('NethServer::Database::Group')->getAll();
        }
        /*Filter out system groups*/
        $handle = fopen('/etc/nethserver/system-groups','r');
        $systemGroups = array();
        if ($handle){
            while (($line = fgets($handle)) !== false) {
                $systemGroups[] = strtolower(trim($line));
            }
        }
        fclose($handle);
        if (!empty($groups))
        {
            foreach ($groups as $key => $group)
            {
                $tmp = split ('@',strtolower($key)); 
                if ( in_array($tmp[0],$systemGroups) || (isset($group['gid']) && ($group['gid'] < 1000)) )
                {
                    /*Remove group if it's a system group*/
                    unset($groups[$key]);
                } 
            }
        }

        return is_array($groups) ? $groups : array();
    }

    public function isReadOnly()
    {
        return (!$this->listGroupsCommand);
    }

    public function isAD()
    {
        return $ad;
    }
    
    public function __construct(\Nethgui\System\PlatformInterface $platform)
    {
         $this->platform = $platform;

         # Search for installed provider
         if (file_exists('/usr/libexec/nethserver/ldap-list-groups')) {
             $columns[] = 'Actions';
             $this->listGroupsCommand = '/usr/libexec/nethserver/ldap-list-groups';
             $this->ad = false;
         } else if (file_exists('/usr/libexec/nethserver/ad-list-groups')) {
             $columns[] = 'Actions';
             $this->listGroupsCommand = '/usr/libexec/nethserver/ad-list-groups';
             $this->ad = true;
         }
    }
}
