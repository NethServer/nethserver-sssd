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

    public function getUsers()
    {
        if ($this->listUsersCommand) {
            $users = json_decode(exec('/usr/bin/sudo '.$this->listUsersCommand), TRUE);
        } else { # users from remote server
            foreach ($this->platform->getDatabase('NethServer::Database::Passwd')->getAll() as $user => $fields) {
                if ($fields['uid'] < 1000) {
                    continue;
                }
                $users[$user] = $fields;
            }
        }
        return is_array($users) ? $users : array();
    }

    public function isReadOnly()
    {
        return (!$this->listUsersCommand);
    }

    public function __construct(\Nethgui\System\PlatformInterface $platform)
    {
         $this->platform = $platform;

         # Search for installed provider
         if (file_exists('/usr/libexec/nethserver/ldap-list-users')) {
             $columns[] = 'Actions';
             $this->listUsersCommand = '/usr/libexec/nethserver/ldap-list-users';
         } else if (file_exists('/usr/libexec/nethserver/ad-list-users')) {
             $columns[] = 'Actions';
             $this->listUsersCommand = '/usr/libexec/nethserver/ad-list-users';
         }
    }

}
