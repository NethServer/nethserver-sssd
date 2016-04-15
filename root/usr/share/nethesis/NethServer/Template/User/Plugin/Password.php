<?php
/* @var $view \Nethgui\Renderer\Xhtml */

echo $view->checkBox('PassExpires', 'yes')->setAttribute('uncheckedValue', 'no');
