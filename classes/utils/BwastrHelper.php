<?php

namespace Grav\Plugin;

/**
 * Resolves Bundeswasserstraßen (BWaStr) IDs for display in the portal.
 *
 * Special IDs that are unknown to the WSV BWaStr-Locator (e.g. 7000 - Nordsee)
 * are listed in the codelist BAW_bwastrIds. Their entry data contains a bounding box
 * as JSON: {"lat1": ..., "lon1": ..., "lat2": ..., "lon2": ...} (WGS84).
 * Every other ID is resolved via the BWaStr-Locator.
 */
class BwastrHelper
{
    const CODELIST_SPECIAL_IDS = 'BAW_bwastrIds';
    const CODELIST_NAMES = '3950010';

    /**
     * Parse a BWaStr code from ISO, e.g. "0701-12.5-30" or "Nordsee, Haupt- und Nebenstrecken - [7000]".
     *
     * @return array{id: string, from: string, to: string}|null
     */
    public static function parseCode(?string $code): ?array
    {
        if (empty($code)) {
            return null;
        }
        // like "Main, Hauptstrecke - [0108]" (name, section - [ID]) without kilometers
        if (preg_match('/\[(\d+)]\s*$/', $code, $matches)) {
            return [
                'id' => $matches[1],
                'from' => '',
                'to' => '',
            ];
        }
        $values = preg_replace('/[^-?0-9.0-9]+/', '', $code);
        $values = str_replace('-', ' ', $values);
        $values = explode(' ', trim($values));
        if (empty(reset($values))) {
            return null;
        }
        return [
            'id' => $values[0],
            'from' => $values[1] ?? '',
            'to' => $values[2] ?? '',
        ];
    }

    /**
     * Resolve a BWaStr ID for the map.
     *
     * A special ID (codelist BAW_bwastrIds) keeps its ID and gets name and bounding box.
     * Any other ID ending with "00" (the whole waterway) is mapped to "01" (main section),
     * because the BWaStr-Locator only knows the sections.
     *
     * @return array{id: string, from: string, to: string, name?: string, bbox?: array}
     */
    public static function resolve(string $id, ?string $from = '', ?string $to = '', string $lang = 'de'): array
    {
        $special = self::getSpecial($id, $lang);
        if ($special) {
            return array_merge([
                'id' => $id,
                'from' => $from ?? '',
                'to' => $to ?? '',
            ], $special);
        }
        return [
            'id' => self::toLocatorId($id),
            'from' => $from ?? '',
            'to' => $to ?? '',
        ];
    }

    /**
     * Get name and bounding box of a special BWaStr ID or null if the ID is not special.
     *
     * @return array{name: string, bbox?: array}|null
     */
    public static function getSpecial(string $id, string $lang = 'de'): ?array
    {
        $entryId = self::normalizeId($id);
        if ($entryId === '') {
            return null;
        }
        $name = CodelistHelper::getCodelistEntry(self::CODELIST_SPECIAL_IDS, $entryId, $lang)
            ?? CodelistHelper::getCodelistEntry(self::CODELIST_SPECIAL_IDS, $entryId, 'de');
        if ($name === null) {
            return null;
        }
        $special = [
            'name' => (string)$name,
        ];
        $bbox = self::parseBoundingBox(CodelistHelper::getCodelistEntryData(self::CODELIST_SPECIAL_IDS, $entryId));
        if ($bbox) {
            $special['bbox'] = $bbox;
        }
        return $special;
    }

    public static function isSpecial(string $id): bool
    {
        return self::getSpecial($id) !== null;
    }

    /**
     * Get the display name of a BWaStr ID: VV-WSV 1103 codelist first, then the special IDs.
     */
    public static function getName(string $id, string $lang): ?string
    {
        $entryId = self::normalizeId($id);
        if ($entryId === '') {
            return null;
        }
        $special = self::getSpecial($entryId, $lang);
        if ($special) {
            return CodelistHelper::getCodelistEntry([self::CODELIST_NAMES], $entryId, $lang) ?? $special['name'];
        }
        return CodelistHelper::getCodelistEntry([self::CODELIST_NAMES], self::normalizeId(self::toLocatorId($entryId)), $lang);
    }

    private static function toLocatorId(string $id): string
    {
        if (str_ends_with($id, '00')) {
            return substr($id, 0, -2) . '01';
        }
        return $id;
    }

    private static function normalizeId(string $id): string
    {
        $id = trim($id);
        if (!ctype_digit($id)) {
            return '';
        }
        return (string)(int)$id;
    }

    private static function parseBoundingBox(?string $data): ?array
    {
        if (empty($data)) {
            return null;
        }
        $json = json_decode($data, true);
        if (!is_array($json)) {
            return null;
        }
        foreach (['lat1', 'lon1', 'lat2', 'lon2'] as $key) {
            if (!isset($json[$key]) || !is_numeric($json[$key])) {
                return null;
            }
        }
        return [
            'lat1' => (float)$json['lat1'],
            'lon1' => (float)$json['lon1'],
            'lat2' => (float)$json['lat2'],
            'lon2' => (float)$json['lon2'],
        ];
    }
}
