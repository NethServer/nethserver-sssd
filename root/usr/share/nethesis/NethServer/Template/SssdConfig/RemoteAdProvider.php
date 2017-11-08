<?php

/* @var $view \Nethgui\Renderer\Xhtml */

echo $view->header('domain')->setAttribute('template', $T('RemoteAdProvider_header'));

echo $view->panel()
    ->insert($view->columns()
      ->insert($view->textInput('LdapUri'))
      ->insert($view->selector('StartTls', $view::SELECTOR_DROPDOWN))
    )
    ->insert($view->textInput('BaseDN'))
    ->insert($view->textInput('UserDN'))
    ->insert($view->textInput('GroupDN'))
    ->insert($view->fieldsetSwitch('BindType', 'authenticated', $view::FIELDSETSWITCH_EXPANDABLE | $view::FIELDSETSWITCH_CHECKBOX)
        ->insert($view->textInput('BindDN'))
        ->insert($view->textInput('BindPassword')))
;

echo $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('Save', $view::BUTTON_SUBMIT))
        ->insert($view->button('RemoteProviderUnbind', $view::BUTTON_LINK))
;

$view->includeCss('
#SssdConfig_RemoteAdProvider .column {
    width: auto;
}
');
