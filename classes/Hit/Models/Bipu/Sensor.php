<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

use Grav\Common\Grav;

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
        $sensors = array();
        foreach ($jsonList ?? [] as $json) {
            try {
                $sensors[] = new self(
                    name: $json->name,
                    unit: $json->unit,
                    property: $json->property,
                    values: Measurement::fromJsonList($json->values ?? null),
                );
            } catch (\Throwable $e) {
                Grav::instance()['log']->error(
                    'Failed to create Sensor: ' . $e->getMessage()
                );
            }
        }
        return $sensors;
    }
}