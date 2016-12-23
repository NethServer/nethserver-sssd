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
class UserProvider extends BaseProvider
{

    public function getUsers()
    {
        $process = $this->platform->exec('/usr/bin/sudo /usr/libexec/nethserver/list-users');
        $this->checkProcessExitCode($process);
        $users = json_decode($process->getOutput(), TRUE);
        if( ! is_array($users)) {
            return array();
        }
        ksort($users, SORT_STRING | SORT_FLAG_CASE);
        return $users;
    }

    public function getUserMembership($userName)
    {
        $process = $this->platform->exec('/usr/bin/sudo /usr/libexec/nethserver/list-user-membership ${@}', array($userName));
        $this->checkProcessExitCode($process);
        $groups = json_decode($process->getOutput(), TRUE);
        if( ! is_array($groups)) {
            return array();
        }
        sort($groups, SORT_STRING | SORT_FLAG_CASE);
        return $groups;
    }

}
