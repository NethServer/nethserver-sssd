<?php

echo $view->header('username')->setAttribute('template', $T('ChangePassword_Header'));

include dirname(__DIR__) . '/PasswordForm.php';
