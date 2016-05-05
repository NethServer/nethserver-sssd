<?php
namespace NethServer\Module\Account\Type\User;

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
 * List users 
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class UsersAdapter extends \Nethgui\Adapter\LazyLoaderAdapter
{
    /**
     *
     * @var \Nethgui\System\PlatformInterface
     */
    private $platform;

    private $listUsersCommand = '';
    private $columns = array();


    public function __construct(\Nethgui\System\PlatformInterface $platform)
    {
        $this->platform = $platform;
        $this->initColumnsAndCommand();
        parent::__construct(array($this, 'readUsers'));
    }

    public function flush()
    {
        $this->data = NULL;
        return $this;
    }

    public function readUsers()
    {
        $loader = new \ArrayObject();

        if ($this->listUsersCommand) {
            $users = json_decode(exec('/usr/bin/sudo '.$this->listUsersCommand), TRUE);
        } else {
            $users = $this->getPlatform()->getDatabase('NethServer::Database::Passwd')->getAll();
        }
        foreach ($users as $user => $values) {
            $loader[$user] = $values;
        }
        return $loader;
    }

    private function initColumnsAndCommand()
    {
         $columns = array('Key','gecos');

         if (file_exists('/usr/libexec/nethserver/ldap-list-users')) {
             $columns[] = 'Actions';
             $this->listUsersCommand = '/usr/libexec/nethserver/ldap-list-users';
         } else if (file_exists('/usr/libexec/nethserver/ad-list-users')) {
             $columns[] = 'Actions';
             $this->listUsersCommand = '/usr/libexec/nethserver/ad-list-users';
         }

         $this->columns = $columns;
    }

    public function getColumns()
    {
        return $this->columns;
    }
}