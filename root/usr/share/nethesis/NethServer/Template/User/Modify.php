<?php
/* @var $view \Nethgui\Renderer\Xhtml */

if ($view->getModule()->getIdentifier() == 'update') {
    $headerText = $T('Update user `${0}`');
    $shellStyle = $view::STATE_DISABLED;
} else {
    $headerText = $T('Create a new user');
    $shellStyle = 0;
}

echo $view->header('username')->setAttribute('template', $headerText);

$passPanel = $view->fieldset()->setAttribute('template', $T('Options_label'))
    ->insert($view->checkBox('expires', 'yes')->setAttribute('uncheckedValue', 'no'))
    ->insert($view->checkbox('shell', '/bin/bash', $shellStyle)->setAttribute('uncheckedValue', '/usr/libexec/openssh/sftp-server'));

$basicInfo = $view->panel()
    ->setAttribute('title', $T('BasicInfo_Title'))
    ->insert($view->textInput('username', ($view->getModule()->getIdentifier() == 'create' ? 0 : $view::STATE_DISABLED | $view::STATE_READONLY )))
    ->insert($view->textInput('gecos'))
    ->insert($view->objectPicker('groups')
    ->setAttribute('objects', 'groupsDatasource')
    ->setAttribute('template', $T('Groups_label'))
    ->setAttribute('objectLabel', 1))
    ->insert($passPanel);


echo $basicInfo;

$buttons = $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);

if ($view->getModule()->getIdentifier() == 'update') {
    $buttons->insert($view->button('ChangePassword', $view::BUTTON_LINK));
}
$buttons->insert($view->button('Cancel', $view::BUTTON_CANCEL));

echo $buttons;

