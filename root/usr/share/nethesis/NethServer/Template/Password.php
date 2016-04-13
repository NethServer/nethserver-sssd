<?php
/* @var $view \Nethgui\Renderer\Xhtml */

echo $view->header()->setAttribute('template', $T('Password_Title'));

echo $view->panel()
        ->insert($view->checkbox('Users', 'strong')->setAttribute('uncheckedValue', 'none'))
        ->insert($view->checkbox('Admin', 'strong')->setAttribute('uncheckedValue', 'none'))
        ->insert($view->checkbox('PassExpires', 'yes')->setAttribute('uncheckedValue', 'no'))
        ->insert($view->slider('MaxPassAge', $view::SLIDER_ENUMERATIVE | $view::LABEL_ABOVE)
                ->setAttribute('label', $T('Maximum password age (${0})')))
        ->insert($view->slider('MinPassAge', $view::SLIDER_ENUMERATIVE | $view::LABEL_ABOVE)
                ->setAttribute('label', $T('Minimum password age (${0})')))
        ->insert($view->slider('PassWarning', $view::SLIDER_ENUMERATIVE | $view::LABEL_ABOVE)
                ->setAttribute('label', $T('Number of days to sent a warning (${0})')));

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);
