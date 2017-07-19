<?php


/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header()->setAttribute('template', $T('LocalAdProviderDcChangeIp_header'));

$AdIpAddressId = $view->getUniqueId('AdIpAddress');
$labelOpenTag = "<label for='$AdIpAddressId'>";
$help = '<div class="dcalert notification bg-yellow">
  <p>' . $labelOpenTag . '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> ' . htmlspecialchars($T('LocalAdProviderDcChangeIp_message1')) . '</label></p>
  <br>
  <p>' . htmlspecialchars($T('AdIpAddress_help1')) . '</p>
  <ul>
    <li>' . $labelOpenTag . $view->textLabel('greenList')->setAttribute('template', $T('AdIpAddress_help2')) . '</label></li>
    <li>' . $labelOpenTag . htmlspecialchars($T('AdIpAddress_help3')) . '</label></li>
  </ul>
</div>';

echo $view->textInput('AdIpAddress');
echo $help;
echo $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('ChangeIPButton', $view::BUTTON_SUBMIT))
        ->insert($view->button('Back', $view::BUTTON_CANCEL))
;

