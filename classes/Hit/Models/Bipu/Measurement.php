<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

use Grav\Common\Grav;

class Measurement
{
    public function __construct(
        public float  $value,
        public string $timestamp,
    )
    {
    }

    static function fromJsonList(
        ?array $jsonList,
        ?int   $fractionDigits = null,
    ): array
    {
        $validValues = array_filter($jsonList ?? [],
            fn($json) => isset($json->value) && (isset($json->display_date) || isset($json->time))
        );

        $measurements = array();
        foreach ($validValues as $json) {
            try {
                $measurements[] = self::fromJson($json, $fractionDigits);
            } catch (\Throwable $e) {
                Grav::instance()['log']->error(
                    'Failed to create measurments: ' . $e->getMessage()
                );
            }
        }

        return $measurements;
    }

    static function fromJson(
        object $json,
        ?int   $fractionDigits = null,
    ): Measurement
    {
        $value = $json->value;

        // Value can be string in EU format, e.g. 1,25.
        if (is_string($value)) {
            $value = (float)$value;
        }

        if (isset($fractionDigits)) {
            $value = round($value, $fractionDigits);
        }

        return new self(
            value: $value,
            timestamp: $json->display_date ?? $json->time,
        );
    }

    static function fromArrayList(
        ?array $arrayList,
        int    $limit = 24,
    ): array
    {
        $validValues = array_filter($arrayList ?? [],
            fn($array) => is_array($array) && count($array) == 2
        );

        $validValues = array_slice($validValues, 0, $limit);

        return array_map(fn($array) => self::fromArray($array), $validValues);
    }

    static function fromArray(array $array): self
    {
        return new self(
            value: $array[1],
            timestamp: $array[0],
        );
    }
}