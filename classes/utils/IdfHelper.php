<?php

namespace Grav\Plugin;
use Grav\Common\Grav;

class IdfHelper
{

    public static function registerNamespaces(\SimpleXMLElement $node): void
    {
        $node->registerXPathNamespace('idf', 'http://www.portalu.de/IDF/1.0');
        $node->registerXPathNamespace('gco', 'http://www.isotc211.org/2005/gco');
        $node->registerXPathNamespace('gmd', 'http://www.isotc211.org/2005/gmd');
        $node->registerXPathNamespace('gml', 'http://www.opengis.net/gml/3.2');
        $node->registerXPathNamespace('gmx', 'http://www.isotc211.org/2005/gmx');
        $node->registerXPathNamespace('gts', 'http://www.isotc211.org/2005/gts');
        $node->registerXPathNamespace('srv', 'http://www.isotc211.org/2005/srv');
        $node->registerXPathNamespace('xlink', 'http://www.w3.org/1999/xlink');
        $node->registerXPathNamespace('xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $node->registerXPathNamespace('igctx', 'https://www.ingrid-oss.eu/schemas/igctx');
    }

    public static function getNode(\SimpleXMLElement $node, string $xpath): ?\SimpleXMLElement
    {
        self::registerNamespaces($node);
        $tmpNode = $node->xpath($xpath);
        if ($tmpNode) {
            return $tmpNode[0];
        }
        return null;
    }

    public static function getNodeValue(\SimpleXMLElement $node, string $xpath, ?array $codelist = null, ?string $lang = null): ?string
    {
        self::registerNamespaces($node);
        $tmpNode = $node->xpath($xpath);
        if ($tmpNode) {
            $value = (string) $tmpNode[0];
            if ($codelist && $lang) {
                $codelistValue = CodelistHelper::getCodelistEntry($codelist, $value, $lang);
                if ($codelistValue == null) {
                    $codelistValue = CodelistHelper::getCodelistEntryByIso($codelist, $value, $lang);
                }
                if ($codelistValue == null) {
                    $codelistValue = CodelistHelper::getCodelistEntryByData($codelist, $value, $lang);
                }
                if ($codelistValue == null) {
                    $codelistValue = $value;
                }
                return $codelistValue;
            } else {
                return $value;
            }
        }
        return null;
    }

    public static function getNodeBoolValue(\SimpleXMLElement $node, string $xpath): bool
    {
        self::registerNamespaces($node);
        $tmpNode = $node->xpath($xpath);
        if ($tmpNode) {
            $value = (string) $tmpNode[0];
            if ($value === 'true') {
                return true;
            }
        }
        return false;
    }

    public static function getNodeList(\SimpleXMLElement $node, string $xpath): array
    {
        self::registerNamespaces($node);
        $tmpNode = $node->xpath($xpath);
        if ($tmpNode) {
            return $tmpNode;
        }
        return [];
    }

    public static function getNodeValueList(\SimpleXMLElement $node, string $xpath, ?array $codelist = null, ?string $lang = null): array
    {
        self::registerNamespaces($node);
        $array = array();
        $tmpNodes = $node->xpath($xpath);
        foreach ($tmpNodes as $tmpNode) {
            if ($tmpNode) {
                $value = (string) $tmpNode;
                if ($codelist && $lang) {
                    $codelistValue = CodelistHelper::getCodelistEntry($codelist, $value, $lang);
                    if ($codelistValue == null) {
                        $codelistValue = CodelistHelper::getCodelistEntryByIso($codelist, $value, $lang);
                    }
                    if ($codelistValue == null) {
                        $codelistValue = CodelistHelper::getCodelistEntryByData($codelist, $value, $lang);
                    }
                    if ($codelistValue == null) {
                        $codelistValue = $value;
                    }
                    $array[] = $codelistValue;
                } else {
                    $array[] = $value;
                }
            }
        }
        return $array;
    }

    public static function getNodeValueListWithSubEntries(\SimpleXMLElement $node, string $xpath, array $xpathSubs, array $subTypes = null, array $codelist = null, ?string $lang = null): array
    {
        self::registerNamespaces($node);
        $array = array();
        $tmpNodes = self::getNodeList($node, $xpath);
        foreach ($tmpNodes as $tmpNode) {
            if (isset($tmpNode)) {
                $item = [];
                foreach ($xpathSubs as $key => $xpathSub) {
                    $values = self::getNodeValueList($tmpNode, $xpathSub, $codelist[$key] ?? null, $lang);
                    if (!empty($values)) {
                        if (count($values) > 1) {
                            $item[] = array(
                                "values" => $values,
                                "type" => $subTypes[$key] ?? 'text'
                            );
                        } else {
                            $item[] = array(
                                "value" => $values[0],
                                "type" => $subTypes[$key] ?? 'text'
                            );
                        }
                    }
                }
                if (!empty($item)) {
                    $array[] = $item;
                }
            }
        }
        return $array;
    }
    public static function getNodeValueListCodelistCompare($node, string $xpath, ?array $codelist, ?string $lang, bool $addEqual = true): array
    {
        self::registerNamespaces($node);
        $array = array();
        $tmpNodes = self::getNodeValueList($node, $xpath);
        foreach ($tmpNodes as $tmpNode) {
            if ($tmpNode) {
                $value = (string) $tmpNode;
                if ($codelist && $lang) {
                    $codelistValue = CodelistHelper::getCodelistEntryByCompare($codelist, $value, $lang, $addEqual);
                    if ($codelistValue) {
                        $array[] = $codelistValue;
                    }
                } else {
                    $array[] = $value;
                }
            }
        }
        return $array;
    }

    public static function transformGML(\SimpleXMLElement $node, string $exportFormat): bool|string
    {
        $config = Grav::instance()['config'];
        $geo_api = $config->get('plugins.ingrid-grav-utils.geo_api');

        $resp = false;
        $data = $node->asXML();

        $api_url = $geo_api['url'];
        $api_user = $geo_api['user'];
        $api_pass = $geo_api['pass'];

        if ($data and !empty($api_url)) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/xml',
            ));
            curl_setopt($curl, CURLOPT_URL, $api_url . $exportFormat);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            if (isset($api_user) and isset($api_pass)) {
                curl_setopt($curl, CURLOPT_USERPWD, $api_user . ":" . $api_pass);
            }

            $resp = curl_exec ($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($httpcode !== 200) {
                $resp = false;
            }
            curl_close($curl);

        }
        return $resp;
    }
}