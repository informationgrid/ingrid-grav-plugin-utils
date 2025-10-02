<?php

namespace Grav\Plugin;

use Grav\Common\Grav;

class DebugHelper
{

    public static function debug(string $text): void
    {
        $grav = Grav::instance();
        $log = $grav['log'];
        $isDebug = $grav['config']->get('plugins.ingrid-grav-utils.debug');

        if ($isDebug) {
            $log->debug($text);
        }
    }

    public static function info(string $text): void
    {
        $grav = Grav::instance();
        $log = $grav['log'];
        $log->info($text);
    }

    public static function error(string $text): void
    {
        $grav = Grav::instance();
        $log = $grav['log'];
        $log->error($text);
    }
}