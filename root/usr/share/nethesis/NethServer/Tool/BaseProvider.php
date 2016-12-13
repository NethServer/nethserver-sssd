<?php
namespace NethServer\Tool;

/*
 * Copyright (C) 2016 Nethesis S.r.l.
 * http://www.nethesis.it - nethserver@nethesis.it
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License,
 * or any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see COPYING.
 */

/**
 * Base implementation for account providers
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class BaseProvider implements \Nethgui\Component\DependencyConsumer, \Nethgui\Log\LogConsumerInterface
{

    protected $platform;
    protected $provider = false;
    protected $isLocalProvider = FALSE;

    protected $failures = array();

    public function getErrors()
    {
        return $this->failures;
    }

    protected function checkProcessExitCode($process)
    {
        if ($process->getExitCode() === 0) {
            return TRUE;
        }
        $this->failures[] = $process;
        return FALSE;
    }

    public function isReadOnly()
    {
        return $this->isLocalProvider === FALSE;
    }

    public function isAD()
    {
        return $this->provider === 'ad';
    }

    public function __construct(\Nethgui\System\PlatformInterface $platform)
    {
        $this->platform = $platform;

        $sssd = $platform->getDatabase('configuration')->getKey('sssd');
        $this->provider = $sssd['Provider'];

        if (($this->provider === 'ldap' && $sssd['LdapURI'] === '')
           || ($this->provider === 'ad' && $platform->getDatabase('configuration')->getProp('nsdc', 'status') === 'enabled')) {
            $this->isLocalProvider = TRUE;
        }
    }

    public function getAccountCounters($timeout = 5)
    {
        $process = $this->platform->exec('/usr/bin/sudo /usr/libexec/nethserver/count-accounts -t ' . $timeout);
        $this->checkProcessExitCode($process);
        $counters = json_decode($process->getOutput(), TRUE);
        if( ! is_array($counters)) {
            return array();
        }
        return $counters;
    }

    public function prepareNotifications(\Nethgui\View\ViewInterface $view, $showWarnings = TRUE)
    {
        foreach($this->getErrors() as $e) {
            $code = $e->getExitCode();
            $message = $view->translate(sprintf('AccountProvider_Error_%d', $code), array($code, $e->getErrorOutput()));
            if(in_array($code, array(4, 110))) {
                if($showWarnings) {
                    $this->notifications->warning($message);
                }
                $this->getLog()->warning(sprintf("%s: %s", get_class($this), $message));
                $this->getLog()->warning($e->getErrorOutput());
            } else {
                $this->notifications->error($message);
                $this->getLog()->error(sprintf("%s: %s", get_class($this), $message));
                $this->getLog()->error($e->getErrorOutput());
            }

        }
        return $this;
    }

    public function setUserNotifications(\Nethgui\Model\UserNotifications $n)
    {
        $this->notifications = $n;
        return $this;
    }

    public function getDependencySetters()
    {
        return array(
            'UserNotifications' => array($this, 'setUserNotifications'),
        );
    }

    public function getLog()
    {
        return $this->log;
    }

    public function setLog(\Nethgui\Log\LogInterface $log)
    {
        $this->log = $log;
        return $this;
    }

}