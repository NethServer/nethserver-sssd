<?php

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header()->setAttribute('template', $T('Ad_header'));

echo sprintf('<div class="information"><p>%s</p></div>', htmlspecialchars($T('ChooseAd_general')));

$column1 = '<i class="fa small fa-graduation-cap"></i> <div class="uc-description">' . htmlspecialchars($T('ChooseAd_joinmember')) . '</div>';
//$column2 = '<i class="fa small fa-sitemap"></i> <div class="uc-description">' . htmlspecialchars($T('ChooseAd_joindc')) . '</div>';
$column3 = '<i class="fa small fa-plus"></i> <div class="uc-description">' . htmlspecialchars($T('ChooseAd_newforest')) . '</div>';


echo $view->columns()
    ->insert($view->panel()->insert($view->literal($column1)))
//    ->insert($view->panel()->insert($view->literal($column2)))
    ->insert($view->panel()->insert($view->literal($column3)))
;

echo $view->columns()
    ->insert($view->panel()->insert($view->button('configAdJoinMember', $view::BUTTON_LINK)))
//    ->insert($view->panel()->insert($view->button('configAdJoinDc', $view::BUTTON_LINK)))
    ->insert($view->panel()->insert($view->button('configAdNewDomain', $view::BUTTON_LINK)))
;

echo $view->buttonList($view::BUTTON_HELP)
        ->insert($view->button('Back', $view::BUTTON_LINK))
;