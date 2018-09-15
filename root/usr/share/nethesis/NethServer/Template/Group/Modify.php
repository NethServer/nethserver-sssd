<?php
/* @var $view \Nethgui\Renderer\Xhtml */

if ($view->getModule()->getIdentifier() == 'update') {
    $headerText = $T('Update group `${0}`');
    $groupname = (string) $view->textInput('groupname', $view::STATE_DISABLED | $view::STATE_READONLY);
} else {
    $headerText = $T('Create a new group');
    $groupname = (string) $view->textInput('groupname');
    $groupname = str_replace("</div>", "@".$view['domain']."</div>", $groupname);
    $pseudonymCreation = 'yes';
}

echo $view->header('groupname')->setAttribute('template', $headerText);

$groupInfo = $view->panel()
    ->setAttribute('title', $T('GroupTab_Title'))
    ->insert($view->literal($groupname))
    ->insert($view->objectPicker('members')
        ->setAttribute('objects', 'membersDatasource')
        ->setAttribute('template', $T('Members_label'))
        ->setAttribute('objectLabel', 1));

echo $groupInfo;

//display only on group creation
if ($pseudonymCreation === 'yes' and
        (file_exists ('/etc/e-smith/db/configuration/defaults/dovecot/status'))) {
    echo $view->fieldset()->setAttribute('template', $T('ExtraFields_label'))->insert($view->checkBox('CreatePseudoRecords', 'yes')->setAttribute('uncheckedValue', 'no'));
}

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_CANCEL | $view::BUTTON_HELP);
