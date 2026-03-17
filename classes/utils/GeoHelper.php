<?php

namespace Grav\Plugin;
use Grav\Common\Grav;

class GeoHelper
{

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

    public static function transformGeojsonToWKT(String $geojson): bool|string
    {
        $config = Grav::instance()['config'];
        $geo_api = $config->get('plugins.ingrid-grav-utils.geo_api');

        $resp = false;
        $data = $geojson;

        $api_url = $geo_api['url'];
        $api_user = $geo_api['user'];
        $api_pass = $geo_api['pass'];

        if ($data and !empty($api_url)) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
            ));
            curl_setopt($curl, CURLOPT_URL, $api_url . 'wkt');
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