<?php
/* @var $view \Nethgui\Renderer\Xhtml */

$view->requireFlag($view::INSET_FORM);

if ($view->getModule()->getIdentifier() == 'update') {
    $headerText = $T('Update user `${0}`');
} else {
    $headerText = $T('Create a new user');
}

echo $view->header('username')->setAttribute('template', $headerText);

$basicInfo = $view->panel()
    ->setAttribute('title', $T('BasicInfo_Title'))
    ->insert($view->textInput('username', ($view->getModule()->getIdentifier() == 'create' ? 0 : $view::STATE_DISABLED | $view::STATE_READONLY )))
    ->insert($view->textInput('FirstName'))
    ->insert($view->textInput('LastName'))
    ->insert($view->objectPicker('Groups')
    ->setAttribute('objects', 'GroupsDatasource')
    ->setAttribute('template', $T('Groups_label'))
    ->setAttribute('objectLabel', 1));


$infoTab = $view->panel()
    ->setAttribute('title', $T('ExtraInfo_Title'))
    ->insert($view->textInput('Company')->setAttribute('placeholder', $view['contactDefaults']['Company']))
    ->insert($view->textInput('Department')->setAttribute('placeholder', $view['contactDefaults']['Department']))
    ->insert($view->textInput('Street')->setAttribute('placeholder', $view['contactDefaults']['Street']))
    ->insert($view->textInput('City')->setAttribute('placeholder', $view['contactDefaults']['City']))
    ->insert($view->textInput('PhoneNumber')->setAttribute('placeholder', $view['contactDefaults']['PhoneNumber']));

$tabs = $view->tabs()
    ->insert($basicInfo)
    ->insert($infoTab)
    ->insertPlugins()
;

echo $tabs;

$buttons = $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);

if ($view->getModule()->getIdentifier() == 'update') {
    $buttons->insert($view->button('ChangePassword', $view::BUTTON_LINK));
}
$buttons->insert($view->button('Cancel', $view::BUTTON_CANCEL));

echo $buttons;

$actionId = $view->getUniqueId();
$view->includeJavascript("
jQuery(function($){
    $('#${actionId}').on('nethguishow', function () {
        $(this).find('.Tabs').tabs('select', 0);
    });
});
");