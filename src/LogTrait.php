<?php
// Copyright (c) 2023 James McDonald
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Toggenation;

use Composer\Script\Event;

trait LogTrait
{
    private static function log($message, int $priority = LOG_INFO, ?Event $event = null)
    {
        if (!is_null($event)) {
            $event->getIO()->write($message);
        } else {
            echo $message . "\n";
        }

        $ret = syslog($priority,  $message);
    }
}
