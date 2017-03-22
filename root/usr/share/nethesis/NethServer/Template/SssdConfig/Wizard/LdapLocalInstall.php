<?php

// $view->requireFlag($view::INSET_DIALOG);

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header()->setAttribute('template', $T('LdapLocalInstall_header'));

echo sprintf('<div class="information"><p>%s</p></div>', htmlspecialchars($T('LdapLocalInstall_message')));

echo $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('Back', $view::BUTTON_LINK))
        ->insert($view->button('Install', $view::BUTTON_SUBMIT))
;