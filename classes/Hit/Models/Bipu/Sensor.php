<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

class Sensor
{
    public function __construct(
        public string $name,
        public string $unit,
        public string $property,

        /* @var Measurement[] $values */
        public array  $values,
    )
    {
    }

    static function fromJsonList(
        ?array $jsonList
    ): array
    {
        $validValues = array_filter($jsonList ?? [],
            fn($json) => isset($json->name) && isset($json->unit) && isset($json->property)
        );
        return array_map(fn($json) => new self(
            name: $json->name,
            unit: $json->unit,
            property: $json->property,
            values: Measurement::fromJsonList($json->values),
        ), $validValues);
    }
}