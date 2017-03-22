<?php

/* @var $view \Nethgui\Renderer\Xhtml */

$disabledFlags = $view::STATE_READONLY | $view::STATE_DISABLED;

echo $view->header('domain')->setAttribute('template', $T('LocalAdProvider_header'));

echo $view->textInput('AdRealm', $disabledFlags);
echo $view->textInput('AdWorkgroup', $disabledFlags);
echo $view->textInput('AdIpAddress', $disabledFlags);

$buttons = $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('LocalAdProviderUninstall', $view::BUTTON_LINK));

if($view->getModule()->isSambaUpdateAvailable()) {
    $buttons->insert($view->button('LocalAdUpdate', $view::BUTTON_LINK));
}

echo $buttons;