<?php

/* @var $view \Nethgui\Renderer\Xhtml */

echo $view->header('domain')->setAttribute('template', $T('AuthProvider_header'));

if($view['Provider'] === 'ad') {
    $ldapEnabled = $view::STATE_DISABLED;
} elseif($view['Provider'] === 'ldap') {
    $adEnabled = $view::STATE_DISABLED;
}

$advanced = $view->fieldset('', $view::FIELDSET_EXPANDABLE)->setAttribute('template', $T('SssdConfig_Advanced_label'))
    ->insert($view->textInput('BindDN'))
    ->insert($view->textInput('BindPassword'))
    ->insert($view->textInput('BaseDN'))
    ->insert($view->textInput('UserDN'))
    ->insert($view->textInput('GroupDN'))
    ->insert($view->textInput('RawLdapUri'))
    ->insert($view->selector('StartTls', $view::SELECTOR_DROPDOWN)->setAttribute('choices', \Nethgui\Widget\XhtmlWidget::hashToDatasource(array(
        '' => $T('starttls_auto'),
        'enabled' => $T('starttls_enabled'),
        'disabled' => $T('starttls_disabled')))))
;

echo $view->panel()
        ->insert($view->radioButton('Provider', 'none', 0))
        ->insert($view->fieldsetSwitch('Provider', 'ldap', $view::FIELDSET_EXPANDABLE | $ldapEnabled)
                ->insert($view->textInput('LdapUri')->setAttribute('placeholder', $view['defaultLdapUri']))
        )
        ->insert($view->fieldsetSwitch('Provider', 'ad', $view::FIELDSET_EXPANDABLE | $adEnabled)
                ->insert($view->textInput('NetbiosDomain', $view::STATE_DISABLED | $view::STATE_READONLY))
                ->insert($view->textInput('AdDns')->setAttribute('placeholder', $view['defaultAdDns']))
        )
;

echo $advanced;

echo $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('Submit', $view::BUTTON_SUBMIT));
