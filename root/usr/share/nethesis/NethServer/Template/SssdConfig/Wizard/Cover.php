<?php

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header('domain')->setAttribute('template', $T('Wizard_header'));

echo sprintf('<div class="information"><p>%s</p></div>', htmlspecialchars($T('ChooseProvider_general')));

$column1 = '<i class="fa fa-database"></i> <div class="uc-description">' . htmlspecialchars($T('ChooseProvider_ldap')) . '</div>';
$column2 = '<i class="fa fa-sitemap"></i> <div class="uc-description">' . htmlspecialchars($T('ChooseProvider_ad')) . '</div>';

echo $view->columns()
    ->insert($view->panel()->insert($view->literal($column1)))
    ->insert($view->panel()->insert($view->literal($column2)))
;

echo $view->columns()
    ->insert($view->panel()->insert($view->button('configLdap', $view::BUTTON_LINK)))
    ->insert($view->panel()->insert($view->button('configAd', $view::BUTTON_LINK)))
;

echo $view->buttonList($view::BUTTON_HELP);

$view->includeJavascript("
(function ( $ ) {
    $('.primaryContent a').button();
} ( jQuery ));
");

$view->includeCss('

#SssdConfig_Wizard .information {
    font-size: 1.2em;
    max-width: 505px;
}

#SssdConfig_Wizard .information p {
    margin: 0.8em 0;
}

#SssdConfig_Wizard .information li {
    list-style: circle;
    margin-left: 1em;
}

#SssdConfig_Wizard .column {
    text-align: center;
    margin-bottom: 1em;
}

#SssdConfig_Wizard .column a.link {
    display: block;
    width: 100%;
}

#SssdConfig_Wizard .column a.link span.ui-button-text {
    padding: 1em 0;
}

#SssdConfig_Wizard .column i.fa {
    display: block;
    font-size: 120px;
    color: #aaa;
}

#SssdConfig_Wizard .column i.fa.small {
    font-size: 64px;
}

#SssdConfig_Wizard .uc-description {
    margin: 1em 0;
}
');

