<?php

namespace Grav\Plugin;

use DateTime;
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
        $existingRss = HttpHelper::getFileContent('user-data://feeds/feeds.json');

        foreach($feeds as $feed) {
            self::getRssFeedItems($feed, $array, json_decode($existingRss, true));
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

    private static function getRssFeedItems(array $feed, array &$array, ?array $existingRss): void
    {
        $url = $feed["url"];
        $lang = $feed["lang"];
        $summary = $feed["summary"];
        $provider = $feed["provider"];
        $category = $feed["category"];

        try {
            if (($response = HttpHelper::getHttpContent($url)) !== false) {
                $content = simplexml_load_string($response);
                if ($content) {
                    $itemNodes = $content->xpath("//item");
                    $dateNow = new DateTime();
                    foreach ($itemNodes as $itemNode) {
                        $title = (string)$itemNode->title;
                        $link = (string)$itemNode->link;
                        $date = (string)$itemNode->pubDate;

                        if (!$date) {
                            # Extract date from url
                            if (preg_match_all('/\d{2}\d{2}\d{2}/',$link,$matches)) {
                                if ($matches) {
                                    $date = DateTime::createFromFormat("!ymd", $matches[0][0]);
                                    if ($date) {
                                        $date = $date->format('Y-m-d T');
                                    }
                                }
                            }
                        }

                        if ($date) {
                            $tmpDate = date_create($date);
                            if ($tmpDate) {
                                $rssDate = date_format($tmpDate, "d.m.y");
                                $rssTime = date_format($tmpDate, "H:i");
                                $rssDateMs = (float)date_format($tmpDate, "Uv");
                            }
                        }

                        if ($date) {
                            if ($dateNow < new DateTime($date)) {
                                break;
                            }
                        }
                        $description = strip_tags((string)$itemNode->description);

                        $item = array(
                            "title" => $title,
                            "url" => $link,
                            "date" => $rssDate ?? $dateNow->format('d.m.Y'),
                            "time" => isset($rssTime) && $rssTime != "00:00" ? $rssTime : null,
                            "date_ms" => $rssDateMs ?? $dateNow->format('uv'),
                            "summary" => $description,
                            "provider" => $summary ?? $provider,
                        );

                        if ($date) {
                            $array[] = $item;
                        } else {
                            $existItem = false;
                            if (isset($existingRss['data'])) {
                                foreach ($existingRss['data'] as $rssItem) {
                                    if ($rssItem['url'] === $link && $rssItem['title'] === $title) {
                                        $existItem = $rssItem;
                                        break;
                                    }
                                }
                            }
                            if ($existItem) {
                                $array[] = $existItem;
                            } else {
                                $array[] = $item;
                            }
                        }
                    }
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
