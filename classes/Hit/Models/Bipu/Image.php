<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

class Image
{
    public function __construct(
        public string      $url,
        public ?string     $title,
        public ?string     $description,
        public ?DataOrigin $dataOrigin
    )
    {
    }

    static function fromJsonList(?array $jsonList): array
    {
        // Find out "Bilder" content.
        $contents = array_filter($jsonList ?? [], fn($json) => ($json->name ?? null) === "Bilder");

        $images = array();
        foreach ($contents as $content) {
            $validItems = array_filter($content->items,
                fn($item) => isset($item->url)
            );
            $validImages = array_map(fn($item) => new Image(
                url: $item->url,
                title: $item->title,
                description: $item->description ?? null,
                dataOrigin: DataOrigin::fromJson($item)
            ), $validItems
            );
            $images = array_merge($images, $validImages);
        }

        return $images;
    }
}