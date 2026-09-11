<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

class Source
{
    public function __construct(
        public string  $name,
        public ?string $url,
    )
    {
    }

    static public function fromJson(?object $json): ?Source
    {
        if (!isset($json)) {
            return null;
        }

        return new self(
            $json->name,
            $json->url ?? null,
        );
    }
}