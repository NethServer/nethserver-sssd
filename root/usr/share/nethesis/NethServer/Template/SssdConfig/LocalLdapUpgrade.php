<?php

$disabledFlags = $view::STATE_READONLY | $view::STATE_DISABLED;

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header()->setAttribute('template', $T('LocalLdapUpgrade_header'));

$informationText = sprintf('<p>%s</p>', htmlspecialchars($T('LocalLdapUpgrade_message1')));

if($view->getModule()->canChangeWorkgroup()) {
    $informationText .= sprintf('<p>%s</p>', htmlspecialchars($T('LocalLdapUpgrade_WS_message1')));
    $informationText .= sprintf('<p>%s</p>', htmlspecialchars($T('LocalLdapUpgrade_WS_message2')));
} else {
    $informationText .= sprintf('<p>%s</p>', htmlspecialchars($T('LocalLdapUpgrade_PDC_message1'))) ;
    $informationText .= sprintf('<p>%s</p>', htmlspecialchars($T('LocalLdapUpgrade_PDC_message2'))) ;
}



echo sprintf('<div class="information">%s</div>', $informationText);

echo $view->textInput('AdRealm');
echo $view->textInput('AdWorkgroup', $view->getModule()->canChangeWorkgroup() ? 0 : $disabledFlags);

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
        ->insert($view->button('LdapUpgradeButton', $view::BUTTON_SUBMIT))
;

$view->includeCss('
#SssdConfig_LocalLdapUpgrade .information {
    font-size: 1.2em;
    max-width: 505px;
    margin-bottom: 1em;
}

#SssdConfig_LocalLdapUpgrade .information p {
    margin-bottom: 0.5em;
}
');