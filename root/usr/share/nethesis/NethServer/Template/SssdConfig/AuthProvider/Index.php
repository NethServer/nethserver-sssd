<?php

/* @var $view \Nethgui\Renderer\Xhtml */

echo $view->header('domain')->setAttribute('template', $T('AuthProvider_header'));

if($view['Provider'] === 'ad') {
    $ldapEnabled = $view::STATE_DISABLED;
} elseif($view['Provider'] === 'ldap') {
    $adEnabled = $view::STATE_DISABLED;
}

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


echo $view->fieldset('', $view::FIELDSET_EXPANDABLE)->setAttribute('template', $T('SssdConfig_Advanced_label'))
    ->insert($view->textInput('BindDN')->setAttribute('placeholder', $view['sssd_defaults']['bindDN']))
    ->insert($view->textInput('BindPassword')->setAttribute('placeholder', $view['sssd_defaults']['bindPassword']))
    ->insert($view->textInput('BaseDN')->setAttribute('placeholder', $view['sssd_defaults']['baseDN']))
    ->insert($view->textInput('UserDN')->setAttribute('placeholder', $view['sssd_defaults']['userDN']))
    ->insert($view->textInput('GroupDN')->setAttribute('placeholder', $view['sssd_defaults']['groupDN']))
    ->insert($view->textInput('RawLdapUri')->setAttribute('placeholder', $view['sssd_defaults']['ldapURI']))
    ->insert($view->selector('StartTls', $view::SELECTOR_DROPDOWN)->setAttribute('choices', \Nethgui\Widget\XhtmlWidget::hashToDatasource(array(
        '' => $T('starttls_auto'),
        'enabled' => $T('starttls_enabled'),
        'disabled' => $T('starttls_disabled')))))
;


echo $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('Submit', $view::BUTTON_SUBMIT));
