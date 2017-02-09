<?php 

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header('domain')->setAttribute('template', $T('NoConfig_header'));


echo sprintf('<div class="information"><p>%s</p><p>%s</p><ul><li>%s</li><li>%s</li></ul><p>%s</p></div>',
    htmlspecialchars($T('ChooseProvider_1')),
    htmlspecialchars($T('ChooseProvider_2')),
    htmlspecialchars($T('ChooseProvider_3')),
    htmlspecialchars($T('ChooseProvider_4')),
    htmlspecialchars($T('ChooseProvider_7')))
;

$configureButton = sprintf('<a href="%s">%s</a>', $view->getModuleUrl('/SssdConfig'), htmlspecialchars($T('configureButton_label')));
$softwareCenterButton = sprintf('<a href="%s">%s</a>', $view->getModuleUrl('/PackageManager'), htmlspecialchars($T('softwareCenterButton_label')));
$dismissButton = sprintf('<a class="nothanks" href="%s">%s</a>', $view->getModuleUrl('/Dashboard'), htmlspecialchars($T('dismissButton_label')));

$column1 = '<i class="fa fa-cloud"></i> <div class="uc-description">' . htmlspecialchars($T('ChooseProvider_5')) . '</div>';
$column2 = '<i class="fa fa-download"></i> <div class="uc-description">' . htmlspecialchars($T('ChooseProvider_6')) . '</div>';

echo $view->columns()
    ->insert($view->panel()->insert($view->literal($column1)))
    ->insert($view->panel()->insert($view->literal($column2)))
;

echo $view->columns()
    ->insert($view->panel()->insert($view->literal($configureButton)))
    ->insert($view->panel()->insert($view->literal($softwareCenterButton)))
;

echo $dismissButton;

$view->includeJavascript("
(function ( $ ) {
    $('.primaryContent a').button();
} ( jQuery ));
");

$view->includeCss('

.information {
    font-size: 1.2em;
    max-width: 505px;
}

.information p {
    margin: 0.8em 0;
}

.information li {
    list-style: circle;
    margin-left: 1em;
}

.column {
    text-align: center;
}

.primaryContent span.ui-button-text {
    margin: .5em 1em;
}

.column a {
    margin-top: 1em;
    display: block;
}

a.nothanks {
    margin-top: 1em;
    display: block;
    max-width: 505px;
}

.column i.fa {
    display: block;
    font-size: 120px;
    color: #aaa;
}

');

