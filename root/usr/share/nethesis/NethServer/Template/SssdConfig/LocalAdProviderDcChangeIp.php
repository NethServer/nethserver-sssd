<?php

$view->requireFlag($view::INSET_DIALOG);

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header()->setAttribute('template', $T('LocalAdProviderDcChangeIp_header'));

echo sprintf('<div class="information"><p>%s</p><p>%s</p></div>', htmlspecialchars($T('LocalAdProviderDcChangeIp_message1')), htmlspecialchars($T('LocalAdProviderDcChangeIp_message2')));

$AdIpAddressId = $view->getUniqueId('AdIpAddress');
$labelOpenTag = "<label for='$AdIpAddressId'>";
$help = '<div class="dcalert notification bg-yellow">
  <p>' . $labelOpenTag . '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> ' . htmlspecialchars($T('AdIpAddress_help1')) . '</label></p>
  <ul>
    <li>' . $labelOpenTag . $view->textLabel('greenList')->setAttribute('template', $T('AdIpAddress_help2')) . '</label></li>
    <li>' . $labelOpenTag . htmlspecialchars($T('AdIpAddress_help3')) . '</label></li>
  </ul>
</div>';

echo $view->textInput('AdIpAddress');
echo $help;
echo $view->buttonList($view::BUTTON_CANCEL)
        ->insert($view->button('ChangeIPButton', $view::BUTTON_SUBMIT))
;


$view->includeCss("
.dcalert {
    color: #000;
    background-color: #FFB600;
    border: 1px solid #FFB600;
    border-radius: 2px;
    padding: 15px;
    margin: 10px 10px 50px;
    position: relative;
}
.dcalert:before {
  content: '';
  position: absolute;
  bottom: 100%;
  left: 20px;
  width: 0;
  border-bottom: 18px solid #FFB600;
  border-left: 18px solid transparent;
  border-right: 18px solid transparent;
}
.notification.bg-yellow {color: #000; background-color: #FFB600; border-color: #FFB600 }
.notification.bg-yellow a {color: #000}
.dcalert ul {
    list-style-type: disc;
    margin-left: 25px;
}

.information {margin:10px 0 20px}
.information p + p {margin-top: 8px}
input#SssdConfig_LocalAdProviderDcChangeIp_AdIpAddress{font-size: 1.5em}
");
