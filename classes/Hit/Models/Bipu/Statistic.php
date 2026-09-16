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

        public ?DataOrigin $dataOrigin,
        public ?string $category,
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
                description: $json->info_text ?? null,
                unit: $json->unit ?? null,
                property: $json->property ?? null,
                values: Measurement::fromJsonList(
                    $json->values,
                    self::getFractionDigit($json->unit ?? null)
                ),
                dataOrigin: DataOrigin::fromJson($json),
                category: $json->category?->slug ?? null,
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