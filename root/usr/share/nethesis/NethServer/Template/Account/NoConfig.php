<?php 

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header('domain')->setAttribute('template', $T('NoConfig_header'));
echo sprintf('<a href="%s" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"><span class="ui-button-text">%s</span></a>', $view->getModuleUrl('/SssdConfig'), $T('Configure'));
