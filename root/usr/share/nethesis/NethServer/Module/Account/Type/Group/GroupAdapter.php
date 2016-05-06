<?php
namespace NethServer\Module\Account\Type\Group;

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
 * List groups 
 *
 * @author Giacomo Sanchietti <giacomo.sanchietti@nethesis.it>
 */
class GroupAdapter extends \Nethgui\Adapter\LazyLoaderAdapter
{
    /**
     *
     * @var \Nethgui\System\PlatformInterface
     */
    private $platform;

    public function __construct(\Nethgui\System\PlatformInterface $platform)
    {
        $this->platform = $platform;
        $this->provider = new \NethServer\Tool\GroupProvider($platform);
        parent::__construct(array($this, 'readGroups'));
    }

    public function flush()
    {
        $this->data = NULL;
        return $this;
    }

    public function readGroups()
    {
        $loader = new \ArrayObject();

        foreach ($this->provider->getGroups() as $user => $values) {
            $loader[$user] = $values;
        }
        return $loader;
    }

    public function getColumns()
    {
        if ($this->provider->isReadOnly()) {
            return array('Key');
        } else {
            return array('Key','Actions');
        }
    }
}
