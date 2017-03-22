<?php

// $view->requireFlag($view::INSET_DIALOG);

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header()->setAttribute('template', $T('AdJoinMember_header'));

echo $view->textInput('AdRealm');
echo $view->textInput('AdDns');

echo $view->fieldset()->setAttribute('template', $T('AdJoinMemberCredentials_label'))
  ->insert($view->textInput('AdUsername'))
  ->insert($view->textInput('AdPassword', $view::TEXTINPUT_PASSWORD))
;

echo $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('Back', $view::BUTTON_LINK))
        ->insert($view->button('AdJoinMember', $view::BUTTON_SUBMIT))
;
