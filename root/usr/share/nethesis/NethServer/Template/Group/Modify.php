<?php
/* @var $view \Nethgui\Renderer\Xhtml */

$view->requireFlag($view::INSET_FORM);

if ($view->getModule()->getIdentifier() == 'update') {
    $headerText = $T('Update group `${0}`');
} else {
    $headerText = $T('Create a new group');
}

echo $view->header('groupname')->setAttribute('template', $headerText);

$groupInfo = $view->panel()
    ->setAttribute('title', $T('GroupTab_Title'))    
    ->insert($view->textInput('groupname', ($view->getModule()->getIdentifier() === 'create' ? 0 : $view::STATE_DISABLED | $view::STATE_READONLY)))
    ->insert($view->objectPicker('Members')
        ->setAttribute('objects', 'MembersDatasource')
        ->setAttribute('template', $T('Members_label'))
        ->setAttribute('objectLabel', 1));

echo $groupInfo;

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_CANCEL | $view::BUTTON_HELP);

$actionId = $view->getUniqueId();
$view->includeJavascript("
jQuery(function($){
    $('#${actionId}').on('nethguishow', function () {
        $(this).find('.Tabs').tabs('select', 0);
    });
});
");
