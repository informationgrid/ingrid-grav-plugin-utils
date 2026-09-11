<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

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
            fn($json) => isset($json->value) && isset($json->display_date)
        );

        return array_map(
            fn($json) => self::fromJson($json, $fractionDigits),
            $validValues
        );
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
            timestamp: $json->display_date,
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