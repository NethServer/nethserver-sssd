<?php

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header()->setAttribute('template', $T('Ldap_header'));

echo sprintf('<div class="information"><p>%s</p></div>', htmlspecialchars($T('ChooseLdap_general')));

$column1 = '<i class="fa small fa-cloud"></i> <div class="uc-description">' . htmlspecialchars($T('ChooseLdap_remote')) . '</div>';
$column2 = '<i class="fa small fa-download"></i> <div class="uc-description">' . htmlspecialchars($T('ChooseLdap_local')) . '</div>';

echo $view->columns()
    ->insert($view->panel()->insert($view->literal($column1)))
    ->insert($view->panel()->insert($view->literal($column2)))
;

echo $view->columns()
    ->insert($view->panel()->insert($view->button('configLdapRemote', $view::BUTTON_LINK)))
    ->insert($view->panel()->insert($view->button('configLdapLocal', $view::BUTTON_LINK)))
;

echo $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('Back', $view::BUTTON_LINK))
;