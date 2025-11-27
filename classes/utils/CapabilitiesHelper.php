<?php

namespace Grav\Plugin;

use Grav\Common\Grav;

class CapabilitiesHelper
{

    public static function getCapabilitiesUrl(string $url, ?string $serviceVersion, ?string $serviceType): ?string
    {
        if (!empty($url)) {
            if ($serviceVersion) {
                $tmpService = self::extractServiceFromServiceTypeVersion($serviceVersion);
                if ($tmpService) {
                    $service = $tmpService;
                }
            }

            if (isset($service) && str_contains($service, " ")) {
                return $url;
            }

            if (strpos($url, '?')) {
                if (!isset($service)) {
                    if (isset($serviceType)) {
                        $codelistValue = CodelistHelper::getCodelistEntryByIso(['5100'], $serviceType, 'de');
                        if (empty($codelistValue)) {
                            $service = $serviceType;
                        }
                    }
                }
            } else {
                $service = 'WMTS';
            }

            if (!isset($service) && isset($serviceType) && $serviceType === "view") {
                $service = 'WMS';
            }

            if (isset($service)) {
                if (strpos($url, '?')) {
                    $params = [];
                    if (!stripos($url, 'request=getcapabilities')) {
                        $params[] = 'REQUEST=GetCapabilities';
                    }
                    if (!stripos($url, 'service=')) {
                        $params[] = 'SERVICE=' . $service;
                    }
                    if (!str_ends_with($url, '?')) {
                        if (!str_ends_with($url, '&')) {
                            $url .= '&';
                        }
                    }
                    $url .= implode('&', $params);
                }
                return $url;
            }
        }
        return null;
    }

    public static function getMapUrl(string $url, ?string $serviceVersion = null, ?string $serviceType = null, ?string $additional = null): ?string
    {
        if (!empty($url)) {
            if ($serviceVersion) {
                $tmpService = self::extractServiceFromServiceTypeVersion($serviceVersion);
                if ($tmpService) {
                    if (str_contains(strtolower($tmpService), 'wms') || str_contains(strtolower($tmpService), 'wmts')) {
                        $service = $tmpService;
                    }
                }
            }

            if (strpos($url, '?')) {
                if (isset($service)) {
                    if (isset($serviceType)) {
                        $codelistValue = CodelistHelper::getCodelistEntryByIso(['5100'], $serviceType, 'de');
                        if (empty($codelistValue)) {
                            $service = $serviceType;
                        }
                    }
                }
            } else {
                $service = 'WMTS';
            }

            $config = Grav::instance()['config'];
            $theme = $config->get('system.pages.theme');
            $is_masterportal = $config->get('themes.' . $theme . '.map.is_masterportal');

            if (!isset($service) && isset($serviceType) && $serviceType === "view") {
                $service = 'WMS';
            }

            if ($is_masterportal) {
                if (isset($service)) {
                    if ($additional != null && $additional !== 'NOT_FOUND') {
                        $layerArray = array(
                            array(
                                'typ' => $service,
                                'url' => $url,
                                'identifier' => $additional
                            )
                        );
                        return '?ImportLayers=' . urlencode(json_encode($layerArray));
                    } else {
                        $layerArray = array(
                            'secondary' => array(
                                'currentComponent' => 'serviceImport',
                                'attributes' => array(
                                    'type' => 'serviceImport',
                                    'url' => $url,
                                    'serviceType' => $service
                                )
                            )
                        );
                        return '?MENU=' . urlencode(json_encode($layerArray));
                    }
                }
            } else {
                if (isset($service)) {
                    if (str_contains('?', $url)) {
                        $params = [];
                        if (!stripos($url, 'request=getcapabilities')) {
                            $params[] = 'REQUEST=GetCapabilities';
                        }
                        if (!stripos($url, 'service=')) {
                            $params[] = 'SERVICE=' . $service;
                        }
                        if (!str_ends_with($url, '?')) {
                            if (!str_ends_with($url, '&')) {
                                $url .= '&';
                            }
                        }
                        $url .= implode('&', $params);
                    }
                    $layersParam = $service . '||' . $url;
                    if (!empty($layersParam)) {
                        if ($additional != null && $additional !== 'NOT_FOUND') {
                            $layersParam .= '||' . $additional;
                        }
                        return '?layers=' . urlencode($layersParam);
                    }
                }
            }
        }
        return null;
    }

    public static function extractServiceFromServiceTypeVersion(string $serviceTypeVersion): ?string {
        $splitVersion = explode(",", $serviceTypeVersion);
        $i = 0;
        $tmpVersion = $splitVersion[$i];
        $hasLetters = StringHelper::containsLetters($tmpVersion);
        while(!$hasLetters) {
            $i++;
            if(count($splitVersion) > $i) {
                $tmpVersion = $splitVersion[$i];
                $hasLetters = StringHelper::containsLetters($tmpVersion);
            } else {
                break;
            }
        }
        preg_match('/((?<=\\:| )|^)([a-zA-Z]+?)( [0-9]|$|,)/i', $tmpVersion, $matches);
        if (StringHelper::containsOnlyLetters($tmpVersion) && $matches) {
            if (!str_contains(strtolower($tmpVersion), 'ogc ') || !str_contains(strtolower($tmpVersion), 'ogc:')) {
                $match = $matches[3];
                if ($match) {
                    return $match;
                }
            } else {
                return $tmpVersion;
            }
        } else if (StringHelper::containsLetters($tmpVersion) && $matches) {
            $match = $matches[2];
            if ($match) {
                return $match;
            }
        }
        return null;
    }

    public static function getHitServiceType(?string $serviceTypeVersion, ?string $serviceType): ?string
    {
        if (!empty($serviceTypeVersion)) {
            $service = self::extractServiceFromServiceTypeVersion($serviceTypeVersion);
            if(!empty($service)) {
                return $service;
            }
        }
        if(!empty($serviceType)) {
            $codelistValue = CodelistHelper::getCodelistEntryByIso(["5100"], $serviceType,"de");
            if (empty($codelistValue)) {
                return $serviceType;
            }
        }
        if(!empty($serviceTypeVersion)) {
            if(StringHelper::containsLetters($serviceTypeVersion)) {
                return $serviceTypeVersion;
            }
        }
        return null;
    }


}