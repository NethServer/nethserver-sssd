<?php
namespace NethServer\Module;
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
 * Description of Sssd
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Sssd extends \Nethgui\Controller\ListComposite
{
    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return new \NethServer\Tool\CustomModuleAttributesProvider($base, array(
            'languageCatalog' => array('NethServer_Module_Sssd', 'NethServer_Module_Account'),
            'category' => 'Status')
        );
    }

    public function initialize()
    {
        parent::initialize();
        $this->setViewTemplate(function (\Nethgui\Renderer\Xhtml $view) {
            $p = $view->panel();
            foreach($view->getModule()->getChildren() as $child) {
                $p->insert($view->inset($child->getIdentifier()));
            }
            return $p;
        });
        $this->loadChildrenDirectory();
        $this->sortChildren(function($a, $b) {
           if($a->getIdentifier() == 'Index') {
               return -1;
           }
           if($b->getIdentifier() == 'Index') {
               return 1;
           }
           return 0;
        });
    }


}
