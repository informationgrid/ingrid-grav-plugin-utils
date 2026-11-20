<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

class Source
{
    public function __construct(
        public ?string $name,
        public ?string $url,
    )
    {
    }

    static public function fromJson(mixed $json): ?Source
    {
        if (!isset($json)) {
            return null;
        }

        if (is_string($json)) {
            return new self(
                name: $json,
                url: null,
            );
        }

        if (empty($json->name) && empty($json->url)) {
            return null;
        }

        return new self(
            name: $json->name ?? null,
            url: $json->url ?? null,
        );
    }
}