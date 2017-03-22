<?php

/* @var $view \Nethgui\Renderer\Xhtml */

echo $view->header('domain')->setAttribute('template', $T('LocalLdapProvider_header'));


$buttons = $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('LocalLdapProviderUninstall', $view::BUTTON_LINK));

if($view->getModule()->canUpgradeToSamba()) {
    $buttons->insert($view->button('LocalLdapProviderUpgrade', $view::BUTTON_LINK));
}

echo $buttons;