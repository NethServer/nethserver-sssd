<?php

// $view->requireFlag($view::INSET_DIALOG);

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header()->setAttribute('template', $T('LdapRemoteIp_header'));

echo $view->panel()
    ->insert($view->columns()
        ->insert($view->textInput('LdapRemoteIpAddress'))
        ->insert($view->textInput('LdapRemoteTcpPort')))
;

echo $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('Back', $view::BUTTON_LINK))
        ->insert($view->button('LdapRemoteBind', $view::BUTTON_SUBMIT))
;

$viewIdentifier = $view->getUniqueId();
$portIdentifier = $view->getUniqueId('LdapRemoteTcpPort');

$view->includeCss("
#{$viewIdentifier} .column {
    text-align: left;
    width: auto;
}

#{$portIdentifier} {
    width: 5em;
}
");