<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

class Point
{
    public function __construct(
        public float $lon,
        public float $lat
    )
    {
    }

    static function fromArray(array $coordinates): self
    {
        return new self($coordinates[0], $coordinates[1]);
    }

    static function fromCentroid(?object $centroid): ?self
    {
        if (empty($centroid?->type) || $centroid->type != "Point") {
            return null;
        }

        if (empty($centroid?->coordinates)) {
            return null;
        }

        return self::fromArray($centroid->coordinates);
    }
}