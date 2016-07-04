<?php
/* @var $view \Nethgui\Renderer\Xhtml */

if ($view->getModule()->getIdentifier() == 'update') {
    $headerText = $T('Update user `${0}`');
    if ($view['isAD']) {
        $shellStyle = $view::STATE_DISABLED;
    }
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

/*
*   Password field
*/
if ($view->getModule()->getIdentifier() == 'create'){
  $lprefix = 'valid_platform,password-strength,password-strength,';
  $tests = array(
    array(// missing lowercase
        'label' => $T($lprefix . 7),
        'test' => '[a-z]'
    ),
    array(// missing digit
        'label' => $T($lprefix . 5),
        'test' => '[0-9]'
    ),
    array(// missing uppercase
        'label' => $T($lprefix . 6),
        'test' => '[A-Z]'
    ),
    array(// missing symbol
        'label' => $T($lprefix . 8),
        'test' => '(\W|_)'
    ),
    array(// too short
        'label' => $T($lprefix . 3),
        'test' => '.{7}.*'
    )
  );

    $view->includeFile('NethServer/Js/jquery.nethserver.passwordstrength.js');
    $view->includeJavascript("
jQuery(document).ready(function () {
    $('#" . $view->getUniqueId('newPassword') . "').PasswordStrength({
        position: {my: 'right top', at: 'right bottom'},
        leds: " . json_encode($tests) . ",
        id: " . json_encode($view->getUniqueId('passwordStrength')) . ",
    }).on('keyup', function () { $('#" . $view->getUniqueId('confirmNewPassword') . "').PasswordStrength('refresh'); });

    $('#" . $view->getUniqueId('confirmNewPassword') . "').PasswordStrength({
        position: {my: 'right top', at: 'right bottom'},
        leds: [{ label: '" . $T('ConfirmNoMatch_label') . "', test: function(value) { return value === $('#" . $view->getUniqueId('newPassword') . "').val() }}],
        id: " . json_encode($view->getUniqueId('confirmNewPassword')) . ",
    });
});
    ");
    $view->includeCss("
    .PasswordStrength {overflow: hidden; padding-top: 4px}
    .PasswordStrength .led {float: left; cursor: help}
    .PasswordStrength .led.off.ui-icon { background-image: url(/css/ui/images/ui-icons_cd0a0a_256x240.png) }
    .PasswordStrength .led.on.ui-icon { background-image: url(/css/ui/images/ui-icons_888888_256x240.png) }
    ");

    echo $view->fieldsetSwitch('setPassword', 'enabled', $view::FIELDSETSWITCH_CHECKBOX | $view::FIELDSETSWITCH_EXPANDABLE)->setAttribute('uncheckedValue', 'disabled')
        ->insert($view->textInput('newPassword', $view::TEXTINPUT_PASSWORD))
        ->insert($view->textInput('confirmNewPassword', $view::TEXTINPUT_PASSWORD));
}

$buttons = $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);

if ($view->getModule()->getIdentifier() == 'update') {
    $buttons->insert($view->button('ChangePassword', $view::BUTTON_LINK));
}
$buttons->insert($view->button('Cancel', $view::BUTTON_CANCEL));

echo $buttons;

