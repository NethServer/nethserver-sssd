<?php

/* @var $view \Nethgui\Renderer\Xhtml */

$disabledFlags = $view::STATE_READONLY;

echo $view->header('domain')->setAttribute('template', $T('LocalAdProvider_header'));

echo $view->textInput('AdNsSambaRpmVersion', $disabledFlags);
echo $view->textInput('AdRealm', $disabledFlags);
echo $view->textInput('AdWorkgroup', $disabledFlags);
echo $view->textInput('AdIpAddress', $disabledFlags);

echo $view->fieldset()->setAttribute('template', $T('BindType_label'))
    ->insert($view->textInput('BindDN', $disabledFlags))
    ->insert($view->textInput('BindPassword', $disabledFlags))
;

$buttons = $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('LocalAdProviderDcChangeIp', $view::BUTTON_LINK))
        ->insert($view->button('LocalAdProviderUninstall', $view::BUTTON_LINK));

echo $buttons;
