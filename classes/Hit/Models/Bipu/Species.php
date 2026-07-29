<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

class Species
{
    public function __construct(
        public string $name,
        public string $url,

        /** @var SpeciesItem[] */
        public array  $items
    )
    {
    }


    static public function fromJsonList(?array $jsonList): array
    {
        $species = array_filter($jsonList ?? [],
            fn($json) => !empty($json->url) && !empty($json->items)
        );

        return array_map(fn($item) => new self(
            name: $item->name,
            url: $item->url,
            items: SpeciesItem::fromJsonList($item->items),
        ), $species);
    }
}

class SpeciesItem
{
    public function __construct(
        public string  $name,
        public string  $url,
        public ?string $image_url,
        public ?string $observed_date,
    )
    {
    }

    static function fromJsonList(array $jsonList): array
    {
        return array_map(fn($json) => new self(
            name: $json->name,
            url: $json->url,
            image_url: $json->image?->url ?? null,
            observed_date: $json->temporal?->date ?? null,
        ), $jsonList);
    }
}