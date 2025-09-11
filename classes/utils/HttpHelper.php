<?php
namespace Grav\Plugin;
use Grav\Common\Grav;

class HttpHelper
{
    public static function getHeader(string $url): array
    {
        $opts['http']['timeout'] = 3;
        $context = stream_context_create( $opts );
        return get_headers($url, true, $context);
    }

    public static function getFileContent(string $url): string|bool
    {
        $opts['http']['timeout'] = 3;
        $context = stream_context_create( $opts );
        return @file_get_contents($url, false, $context);
    }
}