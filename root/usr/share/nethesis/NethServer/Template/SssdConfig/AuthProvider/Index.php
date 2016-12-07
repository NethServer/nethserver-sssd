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

echo $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('BindProvider', $view::BUTTON_SUBMIT));
