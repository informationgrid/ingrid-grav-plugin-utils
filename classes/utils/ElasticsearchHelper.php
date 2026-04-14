<?php

namespace Grav\Plugin;

class ElasticsearchHelper
{

    public static function toArray(mixed $esHit): array
    {
        if (gettype($esHit) == "array")
            return (array) $esHit;
        return array($esHit);

    }

    public static function getValue(\stdClass $esHit, string $key): mixed
    {
        $tmpValue = null;
        if (property_exists($esHit, 'fields')) {
            $fields = $esHit->fields;
            if (isset($fields) && property_exists($fields, $key)) {
                $tmpValue = $fields->$key;
            }
        }
        if (!$tmpValue) {
            if (property_exists($esHit, '_source')) {
                $tmpSource = $esHit->_source;
                if (property_exists($tmpSource, $key)){
                    $tmpValue = $tmpSource->$key;
                } else {
                    $keys = explode('.', $key);
                    foreach ($keys as $index => $tmpKey) {
                        if (property_exists($tmpSource, $tmpKey)) {
                            if ($index === count($keys) - 1) {
                                $tmpValue = $tmpSource->$tmpKey;
                            } else {
                                $tmpSource = $tmpSource->$tmpKey;
                            }
                        }
                    }
                }
            }
        }
        if (is_string($tmpValue)) {
            $tmpValue = trim($tmpValue);
        } else if (is_array($tmpValue)) {
            $tmpValue = reset($tmpValue);
        }
        return $tmpValue;
    }

    public static function getValueTime(\stdClass $esHit, string $key): ?string
    {
        $tmpValue = self::getValue($esHit, $key);
        if ($tmpValue) {
            if (str_contains($tmpValue, 'T')) {
                return $tmpValue;
            } else {
                $time = date("Y-m-d", strtotime(substr($tmpValue,0,14)));
                $time .= 'T';
                $time .= date("H:i:s", strtotime(substr($tmpValue,0,14)));
                $time .= '.000Z';
                return $time;
            }
        }
        return null;
    }

    public static function getValueArray(\stdClass $esHit, string $key): array
    {
        $tmpValue = [];
        if (property_exists($esHit, 'fields')) {
            $fields = $esHit->fields;
            if (isset($fields) && property_exists($fields, $key)) {
                $tmpValue = $fields->$key;
            }
        }
        if (empty($tmpValue)) {
            if (property_exists($esHit, '_source')) {
                $tmpSource = $esHit->_source;
                if (property_exists($tmpSource, $key)){
                    $tmpValue = $tmpSource->$key;
                } else {
                    $keys = explode('.', $key);
                    foreach ($keys as $index => $tmpKey) {
                        if (!($tmpSource instanceof \stdClass)) {
                            $tmpSourceItems = [];
                            foreach ($tmpSource as $tmpSourceItem) {
                                if (isset($tmpSourceItem) && property_exists($tmpSourceItem, $tmpKey)) {
                                    $tmpSourceItems[] = $tmpSourceItem->$tmpKey;
                                } else {
                                    $tmpSourceItems[] = '';
                                }
                            }
                            if ($index === count($keys) - 1) {
                                $tmpValue = $tmpSourceItems;
                            } else {
                                $tmpSource = $tmpSourceItems;
                            }
                        } else {
                            if (property_exists($tmpSource, $tmpKey)) {
                                if ($index === count($keys) - 1) {
                                    $tmpValue = $tmpSource->$tmpKey;
                                } else {
                                    $tmpSource = $tmpSource->$tmpKey;
                                }
                            } else {
                                break;
                            }
                        }
                    }
                }
            }
        }
        return self::toArray($tmpValue) ?? [];
    }

    public static function getFirstValue(\stdClass $esHit, string $key): mixed
    {
        $array = self::getValueArray($esHit, $key);
        return reset($array);
    }

    public static function getBBoxes(\stdClass $esHit, ?string $title): array
    {
        $array = array();
        $x1s = ElasticsearchHelper::getValueArray($esHit, "x1");
        $y1s = ElasticsearchHelper::getValueArray($esHit, "y1");
        $x2s = ElasticsearchHelper::getValueArray($esHit, "x2");
        $y2s = ElasticsearchHelper::getValueArray($esHit, "y2");

        if (!empty($x1s) && !empty($x2s) && !empty($y1s) && !empty($y2s)) {
            $locations = ElasticsearchHelper::getValueArray($esHit, "location");
            $count = 0;
            foreach ($x1s as $x1) {
                if ($x1) {
                    $array[] = [
                        "title" => $locations[$count] ?? $title,
                        "westBoundLongitude" => (float)$x1s[$count],
                        "southBoundLatitude" => (float)$y1s[$count],
                        "eastBoundLongitude" => (float)$x2s[$count],
                        "northBoundLatitude" => (float)$y2s[$count],
                    ];
                }
                $count++;
            }
        }
        return $array;
    }

    public static function isCurrentIndexFormat(\stdClass $esHit): bool
    {
        if (
            (in_array("www", self::getValueArray($esHit, "datatype"))) ||
            (in_array("address", self::getValueArray($esHit, "datatype"))) ||
            (in_array("IDF_1.0", self::getValueArray($esHit, "datatype"))) ||
            (self::getValue($esHit, 'x1') && self::getValue($esHit, 'y1')) ||
            (self::getValueArray($esHit, 't04_search.searchterm')) ||
            (self::getValue($esHit, 'additional_html_1')) ||
            (self::getValue($esHit, 'created'))
        ) {
            return true;
        }
        return false;
    }
}