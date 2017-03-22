<?php

$view->requireFlag($view::INSET_DIALOG);

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header()->setAttribute('template', $T('LocalProviderUninstall_header'));

echo sprintf('<div class="information"><p>%s</p></div>', htmlspecialchars($T('LocalProviderUninstall_message')));

echo $view->buttonList($view::BUTTON_CANCEL)
        ->insert($view->button('UninstallButton', $view::BUTTON_SUBMIT))
;