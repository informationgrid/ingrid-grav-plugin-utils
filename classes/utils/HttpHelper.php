<?php
namespace Grav\Plugin;
use Grav\Common\Grav;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HttpHelper
{
    public static function getHeader(string $url): array {
        return self::getHeaderWithHash($url, null);
    }

    public static function getHeaderWithHash(string $url, ?string $hash): array
    {
        DebugHelper::debug('Get header for: ' . $url);
        $grav = Grav::instance();
        $response = [false, false];
        $cache = $grav['cache'];
        if (!isset($hash)) {
            $hash = '';
        }
        $cacheId = md5('HttpHelper.getHeader_' . $hash . '_'. $url);

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
        $httpConfigProxyNo = $httpConfig['proxy_no'] ?? [];
        if (!empty($httpConfigProxyUrl)) {
            $clientOptions['proxy'] = [
                'http' => $httpConfigProxyUrl,
                'https' => $httpConfigProxyUrl,
                'no' => $httpConfigProxyNo
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
        $httpConfigProxyNo = $httpConfig['proxy_no'] ?? [];
        if (!empty($httpConfigProxyUrl)) {
            $clientOptions['proxy'] = [
                'http' => $httpConfigProxyUrl,
                'https' => $httpConfigProxyUrl,
                'no' => $httpConfigProxyNo
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
        $grav = Grav::instance();
        $locator = $grav['locator'];
        $folderPath = $locator->findResource('cache://', true);

        $tmpFile = tempnam($folderPath, 'download_');
        if ($tmpFile === false) {
            DebugHelper::error('Error create temp file.');
            return false;
        }
        try {
            $remoteFile = fopen($url, 'rb');
            if ($remoteFile === false) {
                fclose($remoteFile);
                DebugHelper::error('Error load remote file');
                return false;
            }

            $localFile = fopen($tmpFile, 'wb');
            if ($localFile === false) {
                fclose($remoteFile);
                DebugHelper::error('Error write locale file');
                return false;
            }
            try {
                while (!feof($remoteFile)) {
                    $chunk = fread($remoteFile, 4096);

                    if ($chunk === false) {
                        return false;
                    }

                    if ($chunk !== '') {
                        $written = fwrite($localFile, $chunk);

                        if ($written === false || $written !== strlen($chunk)) {
                            return false;
                        }
                    }
                }
            } finally {
                fclose($remoteFile);
                fclose($localFile);
            }
        } catch (\Throwable $e) {
            return false;
        }
        return $tmpFile;
    }

}