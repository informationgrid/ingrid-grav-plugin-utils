<?php

namespace Grav\Plugin;

class RssResult
{
    public function __construct()
    {
    }

    public static function getResults(): array
    {
        $response = null;
        try {
            $response = HttpHelper::getFileContent('user-data://feeds/feeds.json');
        } catch (\Throwable $th) {
        }
        if ($response) {
            $result = json_decode($response, true);
            return $result['data'] ?? [];
        }
        return [];
    }

}
