<?php

/* @var $view \Nethgui\Renderer\Xhtml */

echo $view->header('domain')->setAttribute('template', $T('LocalLdapProvider_header'));

$alert = '';
$buttons = $view->buttonList($view::BUTTON_HELP);

if($view->getModule()->canUpgradeToSamba()) {
    $alert = '<div style="max-width: 500px" class="dcalert notification bg-yellow"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> ' . htmlspecialchars($view->translate('canUpgradeToSamba_notification')) . '</div>';
    $buttons->insert($view->button('LocalLdapProviderUpgrade', $view::BUTTON_LINK));
}

$buttons->insert($view->button('LocalLdapProviderUninstall', $view::BUTTON_LINK));

echo $buttons;
echo $alert;