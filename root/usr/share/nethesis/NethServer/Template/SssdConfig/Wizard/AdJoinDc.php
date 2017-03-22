<?php

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header()->setAttribute('template', $T('AdJoinDc_header'));

echo $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('Back', $view::BUTTON_LINK))
;