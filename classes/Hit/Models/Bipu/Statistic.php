<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

class Statistic
{
    public function __construct(
        public string  $id,
        public string  $title,
        public ?string $description,
        public ?string $unit,
        public ?string $property,

        /* @var Measurement[] $values */
        public array   $values,
    )
    {
    }

    static function fromJsonList(?array $jsonList): array
    {
        $validValues = array_filter($jsonList ?? [],
            fn($json) => isset($json->oid) && !empty($json->values) && (isset($json->name) || isset($json->category?->name))
        );

        return array_map(function ($json) {
            return new Statistic(
                id: $json->oid,
                title: $json->name ?? $json->category?->name,
                description: $json->info ?? null,
                unit: $json->unit,
                property: $json->property,
                values: Measurement::fromJsonList(
                    $json->values,
                    self::getFractionDigit($json->unit ?? null)
                ),
            );
        }, $validValues);
    }

    static function getFractionDigit(?string $unit): ?int
    {
        return match ($unit) {
            "MW" => 0,
            default => null,
        };
    }
}