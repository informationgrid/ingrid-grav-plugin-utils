<?php
namespace Grav\Plugin;
use Grav\Common\Grav;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HttpHelper
{
    public static function getHeader(string $url): array
    {
        DebugHelper::debug('Get header for: ' . $url);
        $grav = Grav::instance();
        $response = [false, false];
        $cache = $grav['cache'];
        $cacheId = md5('HttpHelper.getHeader_' . $url);

        if ($items = $cache->fetch($cacheId)) {
            return $items;
        }

        $httpPluginConfig = $grav['config']->get('plugins.ingrid-grav-utils.http');
        $httpPluginConfigHeaderTimeout = $httpPluginConfig['header.timeout'] ?? 3;

        $client = new Client();
        $clientOptions = [
            'connect_timeout' => $httpPluginConfigHeaderTimeout
        ];
        $httpConfig = $grav['config']->get('system.http');
        $httpConfigProxyUrl = $httpConfig['proxy_url'];
        if (!empty($httpConfigProxyUrl)) {
            $clientOptions['proxy'] = [
                'http' => $httpConfigProxyUrl,
                'https' => $httpConfigProxyUrl,
            ];
        }
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
                    DebugHelper::debug('Error get http content for: ' . $url);
                }
            }
        }
        if ($res) {
            $response = [$res->getStatusCode(), $res->getHeaders()];
        }
        $cache->save($cacheId, $response);
        return $response;
    }

    public static function getHttpContent(string $url): string|bool
    {
        DebugHelper::debug('Get http content for: ' . $url);
        $grav = Grav::instance();
        $client = new Client();
        $clientOptions = [
            'connect_timeout' => 10
        ];
        $httpConfig = $grav['config']->get('system.http');
        $httpConfigProxyUrl = $httpConfig['proxy_url'];
        if (!empty($httpConfigProxyUrl)) {
            $clientOptions['proxy'] = [
                'http' => $httpConfigProxyUrl,
                'https' => $httpConfigProxyUrl,
            ];
        }
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