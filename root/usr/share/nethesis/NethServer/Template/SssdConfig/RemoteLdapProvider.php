<?php

/* @var $view \Nethgui\Renderer\Xhtml */

echo $view->header('domain')->setAttribute('template', $T('RemoteLdapProvider_header'));

echo $view->panel()
    ->insert($view->columns()
      ->insert($view->textInput('LdapUri'))
      ->insert($view->selector('StartTls', $view::SELECTOR_DROPDOWN)->setAttribute('choices', \Nethgui\Widget\XhtmlWidget::hashToDatasource(array(
        '' => $T('starttls_auto'),
        'enabled' => $T('starttls_enabled'),
        'disabled' => $T('starttls_disabled'))))))
    ->insert($view->textInput('BaseDN'))
    ->insert($view->textInput('UserDN'))
    ->insert($view->textInput('GroupDN'))
    ->insert($view->radioButton('BindType', 'anonymous'))
    ->insert($view->fieldsetSwitch('BindType', 'authenticated', $view::FIELDSETSWITCH_EXPANDABLE)
        ->insert($view->textInput('BindDN'))
        ->insert($view->textInput('BindPassword')))
;

echo $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('Save', $view::BUTTON_SUBMIT))
        ->insert($view->button('RemoteProviderUnbind', $view::BUTTON_LINK))
;

$view->includeCss('
#SssdConfig_RemoteLdapProvider .column {
    width: auto;
}
');
