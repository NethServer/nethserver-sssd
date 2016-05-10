<?php

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header('domain')->setAttribute('template', $T('SssdIndex_header'));

if($view['Provider'] === 'none') {
    echo sprintf('<a href="%s" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"><span class="ui-button-text">%s</span></a>', $view->getModuleUrl('/Account'), $T('Configure'));
} elseif($view['Provider'] === 'ad' || $view['Provider'] === 'ldap') {
    echo $view->textLabel('Details')->setAttribute('tag', 'pre');
}

