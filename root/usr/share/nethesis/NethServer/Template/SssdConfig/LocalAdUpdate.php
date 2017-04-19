<?php

$view->requireFlag($view::INSET_DIALOG);

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header()->setAttribute('template', $T('LocalAdUpdate_header'));

echo sprintf('<div class="information"><p>%s</p></div>', htmlspecialchars($T('LocalAdUpdate_message')));

echo $view->buttonList($view::BUTTON_CANCEL)
        ->insert($view->button('LocalAdUpdate', $view::BUTTON_SUBMIT))
;