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
 * @todo describe class
 * 
 * @link http://redmine.nethesis.it/issues/185
 */
class User extends \Nethgui\Controller\TableController
{
    private $expired = array();

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return \Nethgui\Module\CompositeModuleAttributesProvider::extendModuleAttributes($base, 'Management', 10)->extendFromComposite($this);
    }

    public function initialize()
    {
        $columns = array(
            'Key',
            'FirstName',
            'LastName',
            'Actions',
        );

        $this
            ->setTableAdapter($this->getPlatform()->getTableAdapter('accounts', 'user'))
            ->setColumns($columns)
            ->addTableActionPluggable(new User\Modify('create'))
            ->addTableAction(new \Nethgui\Controller\Table\Help('Help'))
            ->addRowActionPluggable(new User\Modify('update'))
            ->addRowAction(new User\ChangePassword())
            ->addRowAction(new User\ToggleLock('lock'))
            ->addRowAction(new User\ToggleLock('unlock'))
            ->addRowAction(new User\Modify('delete'))
        ;
        $this->expired = json_decode($this->getPlatform()->exec('/usr/bin/sudo /usr/libexec/nethserver/password-expiration')->getOutput());

        parent::initialize();
    }

    public function prepareViewForColumnKey(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        $userState = isset($values['__state']) ? strtolower($values['__state']) : 'new';
        if ($this->expired->$key == 1) {
            $userState = 'new';
        }
        $rowMetadata['rowCssClass'] = trim($rowMetadata['rowCssClass'] . ' user-' . $userState);
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

        $state = isset($values['__state']) ? $values['__state'] : 'new';

        switch ($state) {
            case 'new':
                $killList[] = 'lock';
                $killList[] = 'unlock';
                break;
            case 'active';
                $killList[] = 'unlock';
                break;
            case 'locked';
                $killList[] = 'lock';
                break;
            default:
                break;
        }

        foreach (array_keys(iterator_to_array($cellView)) as $key) {
            if (in_array($key, $killList)) {
                unset($cellView[$key]);
            }
        }

        if (isset($values['Removable']) && $values['Removable'] === 'no') {
            unset($cellView['delete']);
        }
        
        return $cellView;
    }

}
