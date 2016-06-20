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
 * Return the list of users accordingly to SSSD provider: AD or LDAP
 *
 * @author Giacomo Sanchietti <giacomo.sanchietti@nethesis.it>
 */
class UserProvider
{

    private $listUsersCommand = '';
    private $platform;
    private $ad = false;

    public function getUsers()
    {
        $provider = $this->platform->getDatabase('configuration')->getProp('sssd', 'Provider');
        if ($provider != 'ad' && $provider != 'ldap') {
            return array();
        }

        if ($this->listUsersCommand) {
            $users = json_decode(exec('/usr/bin/sudo '.$this->listUsersCommand), TRUE);
        } else { # users from remote server
            $users = $this->platform->getDatabase('NethServer::Database::Passwd')->getAll();
        }
        /*Filter out system users*/
        $handle = fopen('/etc/nethserver/system-users','r');
        $systemUsers = array();
        if ($handle){
            while (($line = fgets($handle)) !== false) {
                $systemUsers[] = strtolower(trim($line));
            }
        }
        if (!empty($users))
        {
            foreach ($users as $key => $user)
            {
                $tmp = split ('@',strtolower($key));
                # hide system users and machine accounts
                if (in_array($tmp[0],$systemUsers) || (isset($user['uid']) && $user['uid'] < 1000) || strpos($tmp[0], '$') !== false)
                {
                    unset($users[$key]);
                }
            }
            return $users;
        }
        return array();
    }

    public function isReadOnly()
    {
        return (!$this->listUsersCommand);
    }

    public function isAD()
    {
        return $this->ad;
    }

    public function __construct(\Nethgui\System\PlatformInterface $platform)
    {
         $this->platform = $platform;

         # Search for installed provider
         if (file_exists('/usr/libexec/nethserver/ldap-list-users')) {
             $columns[] = 'Actions';
             $this->listUsersCommand = '/usr/libexec/nethserver/ldap-list-users';
             $this->ad = false;
         } else if (file_exists('/usr/libexec/nethserver/ad-list-users')) {
             $columns[] = 'Actions';
             $this->listUsersCommand = '/usr/libexec/nethserver/ad-list-users';
             $this->ad = true;
         }
    }

}
