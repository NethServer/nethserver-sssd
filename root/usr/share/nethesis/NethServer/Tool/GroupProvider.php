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
class GroupProvider extends BaseProvider
{

    public function getGroups()
    {
        $process = $this->platform->exec('/usr/bin/sudo /usr/libexec/nethserver/list-groups');
        $this->checkProcessExitCode($process);
        $groups = json_decode($process->getOutput(), TRUE);
        if( ! is_array($groups)) {
            return array();
        }
        return $groups;
    }

    public function getGroupMembers($groupName)
    {
        $process = $this->platform->exec('/usr/bin/sudo /usr/libexec/nethserver/list-group-members ${@}', array($groupName));
        $this->checkProcessExitCode($process);
        $members = json_decode($process->getOutput(), TRUE);
        if( ! is_array($members)) {
            return array();
        }
        return $members;
    }

}
