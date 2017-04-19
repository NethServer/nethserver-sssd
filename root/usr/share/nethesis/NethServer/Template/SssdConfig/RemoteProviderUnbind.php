<?php

$view->requireFlag($view::INSET_DIALOG);

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header()->setAttribute('template', $T('RemoteProviderUnbind_header'));

echo sprintf('<div class="information"><p>%s</p></div>', htmlspecialchars($T('RemoteProviderUnbind_message')));

echo $view->buttonList($view::BUTTON_CANCEL)
        ->insert($view->button('UnbindButton', $view::BUTTON_SUBMIT))
;