<?php

namespace NethServer\Module\Sssd;

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
 * Description of Index
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Index extends \Nethgui\Controller\AbstractController
{
    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['domain'] = \Nethgui\array_end(explode('.', \gethostname(), 2));
        $view['Provider'] = $this->getPlatform()->getDatabase('configuration')->getProp('sssd', 'Provider');
    }
}
