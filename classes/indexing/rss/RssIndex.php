<?php

namespace Grav\Plugin;
use Grav\Common\Grav;
use SimpleXMLElement;
use Exception;

class RssIndex
{
    public function __construct()
    {
    }

    public static function indexJob(array $feeds): void
    {
        DebugHelper::debug('Start job: RSS Indexing');
        $array = array();
        foreach($feeds as $feed) {
            self::getRssFeedItems($feed, $array);
        }
        $names = array();
        #iterating over the arr
        foreach ($array as $key => $val) {
            #storing the key of the names array as the Name key of the arr
            $names[$key] = $val['date_ms'];

        }
        array_multisort($names, SORT_DESC, $array);
        $result = array(
            "status" => array(
                "time" => date("d.m.Y H:i", time()),
                "count" => count($array)
            ),
            "data" => $array
        );
        self::writeJsonFile(json_encode($result, JSON_PRETTY_PRINT), "user-data://feeds", "feeds.json");
        DebugHelper::debug('Finished job: RSS Indexing');
    }

    private static function getRssFeedItems(array $feed, array &$array): void
    {
        $url = $feed["url"];
        $lang = $feed["lang"];
        $summary = $feed["summary"];
        $provider = $feed["provider"];
        $category = $feed["category"];

        try {
            if (($response = HttpHelper::getHttpContent($url)) !== false) {
                $content = simplexml_load_string($response);
                $itemNodes = $content->xpath("//item");
                foreach ($itemNodes as $itemNode) {
                    $title = (string)$itemNode->title;
                    $link = (string)$itemNode->link;
                    $date = (string)$itemNode->pubDate;
                    $description = strip_tags((string)$itemNode->description);
                    $item = array(
                        "title" => $title,
                        "url" => $link,
                        "date" => $date ? date_format(date_create($date), "d.m.y") : "",
                        "time" => $date ? date_format(date_create($date), "H:i:s") : "",
                        "date_ms" => $date ? (float)date_format(date_create($date), "Uv") : "",
                        "summary" => $description,
                        "provider" => $summary ?? $provider,
                    );
                    $array[] = $item;
                }
            } else {
                DebugHelper::error('Error load RSS-URL: ' . $url);
            }
        } catch (Exception $e) {
        }
    }

    private static function writeJsonFile(string $json, string $dir, string $file): void
    {
        mkdir($dir);
        $fp = fopen($dir . "/" . $file, "w");
        fwrite($fp, $json);
        fclose($fp);
    }

}
