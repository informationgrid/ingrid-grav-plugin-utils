<?php
namespace Grav\Plugin;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HttpHelper
{
    public static function getHeader(string $url): array
    {
        DebugHelper::debug('Get header for: ' . $url);
        $client = new Client();
        $clientOptions = [
            'connect_timeout' => 10
        ];
        $res = null;
        try {
            $res = $client->request('HEAD', $url, $clientOptions);
        } catch (GuzzleException $ge) {
            if ($ge->getCode() == 404 ||
                $ge->getCode() == 405) {
                DebugHelper::debug('Try \'GET\' method to get header for: ' . $url);
                try {
                    $res = $client->request('GET', $url, $clientOptions);
                } catch (GuzzleException $e) {
                    return [false, false];
                }
            }
        }
        if ($res) {
            return [$res->getStatusCode(), $res->getHeaders()];
        }
        return [false, false];
    }

    public static function getHttpContent(string $url): string|bool
    {
        DebugHelper::debug('Get http content for: ' . $url);
        $client = new Client();
        $clientOptions = [
            'connect_timeout' => 10
        ];
        try {
            $res = $client->request('GET', $url, $clientOptions);
            return $res->getBody()->getContents();
        } catch (GuzzleException $ge) {
            return false;
        }
    }

    public static function getFileContent(string $pathname): string|bool
    {
        DebugHelper::debug('Get file content for: ' . $pathname);
        return @file_get_contents($pathname);
    }

    public static function getHttpFile(string $url): string|bool
    {
        DebugHelper::debug('Get file for: ' . $url);
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