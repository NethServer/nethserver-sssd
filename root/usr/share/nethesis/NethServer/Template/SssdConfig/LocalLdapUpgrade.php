<?php

$view->requireFlag($view::INSET_DIALOG);

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header()->setAttribute('template', $T('LocalLdapUpgrade_header'));

echo sprintf('<div class="information"><p>%s</p></div>', htmlspecialchars($T('LocalLdapUpgrade_message')));

echo $view->buttonList($view::BUTTON_CANCEL)
        ->insert($view->button('LdapUpgradeButton', $view::BUTTON_SUBMIT))
;