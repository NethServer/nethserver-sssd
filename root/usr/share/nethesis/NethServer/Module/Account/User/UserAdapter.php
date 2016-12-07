<?php
namespace NethServer\Module\Account\User;

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
class UserAdapter extends \Nethgui\Adapter\LazyLoaderAdapter
{
    /**
     *
     * @var \Nethgui\System\PlatformInterface
     */
    private $platform;
    private $provider;

    public function __construct(\Nethgui\System\PlatformInterface $platform)
    {
        $this->platform = $platform;
        $this->provider = new \NethServer\Tool\UserProvider($this->platform);
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
        foreach ($this->provider->getUsers() as $user => $values) {
            $loader[$user] = $values;
        }
        return $loader;
    }

    public function getColumns()
    {
       if ($this->provider->isReadOnly()) {
           return array('Key','gecos');
       } else {
            return array('Key','gecos','Actions');
       }
    }
}
