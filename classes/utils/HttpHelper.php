<?php
namespace Grav\Plugin;

class HttpHelper
{
    public static function getHeader(string $url): array
    {
        $opts['http']['method'] = 'HEAD';
        $context = stream_context_create( $opts );
        return @get_headers($url, true, $context);
    }

    public static function getFileContent(string $url): string|bool
    {
        return @file_get_contents($url);
    }

    public static function getHttpFile(string $url): string|bool
    {
        $remoteFile = fopen($url, 'rb');
        if (!$remoteFile) {
            fclose($remoteFile);
            return false;
        }

        $content = '';
        while (!feof($remoteFile)) {
            // Read chunk of data from remote file
            $content .= fread($remoteFile, 4096); // Adjust chunk size as needed
        }

        fclose($remoteFile);

        return $content;
    }
}