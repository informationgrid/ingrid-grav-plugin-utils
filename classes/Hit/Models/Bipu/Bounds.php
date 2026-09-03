<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

class Bounds
{
    public function __construct(
        public Point $top_left,
        public Point $bottom_right,
    )
    {
    }

    static function fromArray(array $coordinates): self
    {
        return new self(
            top_left: Point::fromArray([$coordinates[0], $coordinates[1]]),
            bottom_right: Point::fromArray([$coordinates[2], $coordinates[3]]),
        );
    }

    static function fromBbox(?array $bbox): ?self
    {
        if (empty($bbox) || count($bbox) < 4) {
            return null;
        }
        return self::fromArray($bbox);
    }
}