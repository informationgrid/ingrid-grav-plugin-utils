<?php

namespace Grav\Plugin;

use Grav\Common\Utils;
use Grav\Common\Grav;

class DetailParserMetadataIdfUVP
{
    static string $xPathStringLength = '[string-length(text()) > 0]';

    public static function parse(\SimpleXMLElement $node, string $uuid, ?string $dataSourceName, array $providers, string $lang, ?string $timestamp): DetailMetadataUVP
    {
        $metadata = new DetailMetadataUVP($uuid);
        $metadata->parentUuid = IdfHelper::getNodeValue($node, "./parent_id" . self::$xPathStringLength);
        $metadata->metaClass = IdfHelper::getNodeValue($node, "./type" . self::$xPathStringLength);
        $metadata->metaClassName = CodelistHelper::getCodelistEntry(["8001"], $metadata->metaClass, $lang);
        $metadata->title = IdfHelper::getNodeValue($node, "./name" . self::$xPathStringLength);
        $metadata->summary = IdfHelper::getNodeValue($node, "./descr" . self::$xPathStringLength);
        $metadata->date = IdfHelper::getNodeValue($node, "./date" . self::$xPathStringLength);
        $metadata->categories = IdfHelper::getNodeValueList($node, "./uvpgs/uvpg[@category and string-length(@category)!=0]/@category");
        $metadata->steps = self::getSteps($node, $timestamp);
        $metadata->negative = self::getNegative($node, $timestamp);
        $metadata->addresses = self::getAddresses($node, $lang);
        $metadata->bbox = self::getBBox($node, $metadata->title);
        $metadata->hasDocs = count(IdfHelper::getNodeValueList($node,"//docs/doc")) > 0;
        return $metadata;
    }

    private static function getBBox(\SimpleXMLElement $node, string $title): array
    {
        $array = [];
        $value = IdfHelper::getNodeValue($node, './spatialValue');
        if (isset($value)) {
            $values = explode(':', $value);
            $countValues = count($values);
            if (!empty($countValues)) {
                if (!str_contains($values[$countValues - 1], 'null')) {
                    $coords = explode(', ', $values[$countValues - 1]);
                    $array[] = array(
                        "title" => empty($values[0]) ? $title : $values[0],
                        "westBoundLongitude" => (float)$coords[0],
                        "southBoundLatitude" => (float)$coords[1],
                        "eastBoundLongitude" => (float)$coords[2],
                        "northBoundLatitude" => (float)$coords[3],
                    );
                }
            }
        }
        return $array;
    }
    private static function getSteps(\SimpleXMLElement $node, ?string $timestamp): array
    {
        $array = [];
        $nodes = IdfHelper::getNodeList($node, "./steps/step[./*]");
        if (!empty($nodes)) {
            foreach ($nodes as $tmpNode) {
                $type = IdfHelper::getNodeValue($tmpNode, './@type');
                $dateFrom = IdfHelper::getNodeValue($tmpNode, './datePeriod/from | ./date/from');
                $dateTo = IdfHelper::getNodeValue($tmpNode, './datePeriod/to | ./date/to');
                $technicalDocs = self::getDocs($tmpNode, './docs[@type="technicalDocs"]/doc', $timestamp);
                $applicationDocs = self::getDocs($tmpNode, './docs[@type="applicationDocs"]/doc', $timestamp);
                $reportsRecommendationsDocs = self::getDocs($tmpNode, './docs[@type="reportsRecommendationsDocs"]/doc', $timestamp);
                $moreDocs = self::getDocs($tmpNode, './docs[@type="moreDocs"]/doc', $timestamp);
                $publicationDocs = self::getDocs($tmpNode, './docs[@type="publicationDocs"]/doc', $timestamp);
                $considerationDocs = self::getDocs($tmpNode, './docs[@type="considerationDocs"]/doc', $timestamp);
                $approvalDocs = self::getDocs($tmpNode, './docs[@type="approvalDocs"]/doc', $timestamp);
                $designDocs = self::getDocs($tmpNode, './docs[@type="designDocs"]/doc', $timestamp);
                $item = array(
                    'type' => $type,
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                    'technicalDocs' => $technicalDocs,
                    'applicationDocs' => $applicationDocs,
                    'reportsRecommendationsDocs' => $reportsRecommendationsDocs,
                    'moreDocs' => $moreDocs,
                    'publicationDocs' => $publicationDocs,
                    'considerationDocs' => $considerationDocs,
                    'approvalDocs' => $approvalDocs,
                    'designDocs' => $designDocs,
                );
                $array[] = $item;
            }
        }
        return $array;
    }

    private static function getNegative(\SimpleXMLElement $node, ?string $timestamp): array
    {
        $array = [];
        $dateFrom = IdfHelper::getNodeValue($node, './datePeriod/from');
        $uvpNegativeRelevantDocs = self::getDocs($node, './docs[@type="uvpNegativeRelevantDocs"]/doc', $timestamp);
        if (isset($dateFrom) || !empty($uvpNegativeRelevantDocs)) {
            return array(
                'dateFrom' => $dateFrom,
                'uvpNegativeRelevantDocs' => $uvpNegativeRelevantDocs,
            );
        }
        return $array;
    }

    private static function getDocs(\SimpleXMLElement $node, string $xpath, ?string $timestamp): array
    {
        $array = [];
        $nodes = IdfHelper::getNodeList($node, $xpath);
        foreach ($nodes as $tmpNode) {
            $label = IdfHelper::getNodeValue($tmpNode, './label');
            $link = IdfHelper::getNodeValue($tmpNode, './link');
            $item = array(
                'label' => $label,
                'link' => $link,
                'timestamp' => $timestamp,
            );
            $array[] = $item;
        }
        return $array;
    }
    private static function getAddresses(\SimpleXMLElement $node, string $lang): array
    {
        $array = [];
        $nodes = IdfHelper::getNodeList($node, "./addresses/address");

        foreach ($nodes as $tmpNode) {
            $id = IdfHelper::getNodeValue($tmpNode, './@id');
            $name = IdfHelper::getNodeValue($tmpNode, './name');
            $parents = IdfHelper::getNodeValueList($tmpNode, './parent/name');
            $phone = IdfHelper::getNodeValue($tmpNode, './phone');
            $fax = IdfHelper::getNodeValue($tmpNode, './fax');
            $mail = IdfHelper::getNodeValue($tmpNode, './mail');
            $url = IdfHelper::getNodeValue($tmpNode, './url');
            $street = IdfHelper::getNodeValue($tmpNode, './street');
            $city = IdfHelper::getNodeValue($tmpNode, './city');
            $postalcode = IdfHelper::getNodeValue($tmpNode, './postalcode');
            $country = IdfHelper::getNodeValue($tmpNode, './country');
            $postbox = IdfHelper::getNodeValue($tmpNode, './postbox');
            $item = array (
                'id' => $id,
                'name' => $name,
                'parents' => $parents,
                'phone' => $phone,
                'fax' => $fax,
                'mail' => $mail,
                'url' => $url,
                'street' => $street,
                'city' => $city,
                'postalcode' => $postalcode,
                'country' => $country,
                'postbox' => $postbox,
            );
            $array[] = $item;
        }
        return $array;
    }
}
