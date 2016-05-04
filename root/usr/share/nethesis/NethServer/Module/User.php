<?php
namespace NethServer\Module;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
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
 * Handle user list
 * 
 */
class User extends \Nethgui\Controller\TableController
{

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return \Nethgui\Module\CompositeModuleAttributesProvider::extendModuleAttributes($base, 'Management', 10)->extendFromComposite($this);
    }

    public function initialize()
    {
        $adapter = new \NethServer\Module\User\UsersAdapter($this->getPlatform());

        $this
            ->setTableAdapter($adapter)
            ->setColumns($adapter->getColumns())
            ->addTableAction(new User\Modify('create'))
            ->addTableAction(new \Nethgui\Controller\Table\Help('Help'))
            ->addRowAction(new User\Modify('update'))
            ->addRowAction(new User\ChangePassword())
            ->addRowAction(new User\ToggleLock('lock'))
            ->addRowAction(new User\ToggleLock('unlock'))
            ->addRowAction(new User\Modify('delete'))
        ;

        parent::initialize();
    }

    public function prepareViewForColumnKey(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        if ($values['new'] || $values['expired']) {
            $rowMetadata['rowCssClass'] = trim($rowMetadata['rowCssClass'] . ' user-new');
        }
        return strval($key);
    }

    /**
     * Override prepareViewForColumnActions to hide/show lock/unlock actions
     * @param \Nethgui\View\ViewInterface $view
     * @param string $key The data row key
     * @param array $values The data row values
     * @return \Nethgui\View\ViewInterface 
     */
    public function prepareViewForColumnActions(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        $cellView = $action->prepareViewForColumnActions($view, $key, $values, $rowMetadata);

        $killList = array();

        # Users are from remote source, do not display any action
        if (!isset($values['new']) && !isset($values['new']) && !isset($values['new'])) {
            return null;
        }
    
        if ($values['new'] || $values['expired']) {
            $killList[] = 'lock';
            $killList[] = 'unlock';
        } else {
            if ($values['locked']) {
                $killList[] = 'lock';
            } else {
                $killList[] = 'unlock';
            }
        }

        foreach (array_keys(iterator_to_array($cellView)) as $key) {
            if (in_array($key, $killList)) {
                unset($cellView[$key]);
            }
        }

        return $cellView;
    }

    public function process()
    {
        if ($this->getRequest()->isMutation()) {
            $keyValue = $this->parameters['Id'];
            //TODO: execute command
            $this->getAdapter()->flush();
        } else {
            parent::process();
        }
    }
}
