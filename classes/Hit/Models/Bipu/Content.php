<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

class Content
{
    public function __construct(
        public string $name,

        /** @var ContentItem[] */
        public array  $items,
    )
    {
    }

    static public function fromJsonList(?array $jsonList): array
    {
        // Filter out "Bilder" content which belongs to images.
        $contents = array_filter($jsonList ?? [],
            fn($json) => ($json->name ?? null) !== "Bilder" && ($json->name ?? null) !== "Beschreibung" && !empty($json->items)
        );

        return array_map(fn($json) => new self(
            name: $json->name,
            items: ContentItem::fromJsonList($json->items),
        ), $contents);
    }
}


class ContentItem
{
    public function __construct(
        public string  $type,
        public ?string $url,
        public ?string $title,
        public ?string $description,
    )
    {
    }

    public static function fromJsonList(array $jsonList): array
    {
        return array_map(fn($json) => new self(
            type: $json->type,
            url: $json->url ?? null,
            title: $json->title ?? null,
            description: $json->content ?? null,
        ), $jsonList);
    }
}