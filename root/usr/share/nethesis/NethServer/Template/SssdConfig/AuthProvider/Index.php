<?php

/* @var $view \Nethgui\Renderer\Xhtml */

echo $view->header('domain')->setAttribute('template', $T('AuthProvider_header'));

if($view['Provider'] === 'ad') {
    $ldapEnabled = $view::STATE_DISABLED;
} elseif($view['Provider'] === 'ldap') {
    $adEnabled = $view::STATE_DISABLED;
    if($view['RawLdapUri'] === 'ldap://127.0.0.1') {
        $globalFlags = $view::STATE_DISABLED | $view::STATE_READONLY;
    }
}

echo $view->panel()
        ->insert($view->radioButton('Provider', 'none', $globalFlags))
        ->insert($view->fieldsetSwitch('Provider', 'ldap', $view::FIELDSET_EXPANDABLE | $ldapEnabled | $globalFlags)
                ->insert($view->textInput('LdapUri')->setAttribute('placeholder', $view['defaultLdapUri']))
        )
        ->insert($view->fieldsetSwitch('Provider', 'ad', $view::FIELDSET_EXPANDABLE | $adEnabled | $globalFlags )
                ->insert($view->textInput('NetbiosDomain', $view::STATE_DISABLED | $view::STATE_READONLY))
                ->insert($view->textInput('AdDns', $globalFlags)->setAttribute('placeholder', $view['defaultAdDns']))
        )
;


echo $view->fieldset('', $view::FIELDSET_EXPANDABLE | $globalFlags )->setAttribute('template', $T('SssdConfig_Advanced_label'))
    ->insert($view->textInput('BindDN', $globalFlags)->setAttribute('placeholder', $view['sssd_defaults']['bindDN']))
    ->insert($view->textInput('BindPassword', $globalFlags)->setAttribute('placeholder', $view['sssd_defaults']['bindPassword']))
    ->insert($view->textInput('BaseDN', $globalFlags)->setAttribute('placeholder', $view['sssd_defaults']['baseDN']))
    ->insert($view->textInput('UserDN', $globalFlags)->setAttribute('placeholder', $view['sssd_defaults']['userDN']))
    ->insert($view->textInput('GroupDN', $globalFlags)->setAttribute('placeholder', $view['sssd_defaults']['groupDN']))
    ->insert($view->textInput('RawLdapUri', $globalFlags)->setAttribute('placeholder', $view['sssd_defaults']['ldapURI']))
    ->insert($view->selector('StartTls', $globalFlags | $view::SELECTOR_DROPDOWN)->setAttribute('choices', \Nethgui\Widget\XhtmlWidget::hashToDatasource(array(
        '' => $T('starttls_auto'),
        'enabled' => $T('starttls_enabled'),
        'disabled' => $T('starttls_disabled')))))
;


echo $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('Submit', $view::BUTTON_SUBMIT | $globalFlags));
