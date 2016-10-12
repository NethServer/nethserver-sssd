<?php

/* @var $view \Nethgui\Renderer\Xhtml */

echo $view->header('domain')->setAttribute('template', $T('AuthProvider_header'));

$formState = 0;
if($view['Provider'] !== 'none') {
    $formState = $view::STATE_DISABLED;
}

echo $view->panel($formState)
        ->insert($view->radioButton('Provider', 'none', $formState))
        ->insert($view->fieldsetSwitch('Provider', 'ldap', $view::FIELDSET_EXPANDABLE | $formState)
                ->insert($view->textInput('LdapUri')->setAttribute('placeholder', $view['defaultLdapUri']))
        )
        ->insert($view->fieldsetSwitch('Provider', 'ad', $view::FIELDSET_EXPANDABLE | $formState)
                ->insert($view->textInput('NetbiosDomain', $view::STATE_DISABLED | $view::STATE_READONLY))
                ->insert($view->textInput('AdDns')->setAttribute('placeholder', $view['defaultAdDns']))
        )
;

echo $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('BindProvider', $view::BUTTON_SUBMIT | $formState));
