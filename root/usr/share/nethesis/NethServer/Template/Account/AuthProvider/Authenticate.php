<?php

/* @var $view Nethgui\Renderer\Xhtml */

$view->requireFlag($view::INSET_DIALOG);

echo $view->header()->setAttribute('template', $T('Authenticate_header'));

echo $view->textInput('login');
echo $view->textInput('password', $view::TEXTINPUT_PASSWORD);

echo $view->buttonList()
        ->insert($view->button('Join', $view::BUTTON_SUBMIT))
;

$idPassword = $view->getUniqueId('password');

$view->includeCss("
#${idPassword} { margin-bottom: 4em }
");
