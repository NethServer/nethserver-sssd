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
 * Manage system user groups
 * 
 * @link http://redmine.nethesis.it/issues/185
 */
class Group extends \Nethgui\Controller\TableController
{

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return \Nethgui\Module\SimpleModuleAttributesProvider::extendModuleAttributes($base, 'Management', 20);
    }

    public function initialize()
    {
        $columns = array(
            'Key',
            'Description',
            'Actions',
        );

        $this
            ->setTableAdapter($this->getPlatform()->getTableAdapter('accounts', 'group'))
            ->setColumns($columns)
            ->addTableActionPluggable(new Group\Modify('create'), 'PlugService')
            ->addTableAction(new \Nethgui\Controller\Table\Help('Help'))
            ->addRowActionPluggable(new Group\Modify('update'), 'PlugService')
            ->addRowActionPluggable(new Group\Modify('delete'), 'PlugDelete')
        ;

        parent::initialize();
    }

    /**
     * Honour "Removable=no" prop.
     *
     * @param \Nethgui\Controller\Table\Read $action
     * @param \Nethgui\View\ViewInterface $view
     * @param type $key
     * @param type $values
     * @param type $rowMetadata
     * @return type
     */
    public function prepareViewForColumnActions(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        $cellView = $action->prepareViewForColumnActions($view, $key, $values, $rowMetadata);
        if (isset($values['Removable']) && $values['Removable'] === 'no') {
            unset($cellView['delete']);
        }
        return $cellView;
    }

}
