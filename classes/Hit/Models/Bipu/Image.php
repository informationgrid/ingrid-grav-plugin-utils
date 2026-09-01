<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

use Grav\Common\Grav;

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
            // Filter out invalid content.
            $validItems = array_filter($content->items,
                fn($item) => isset($item->url)
            );

            // Filter out problematic images.
            $validImages = array();
            foreach ($validItems as $item) {
                try {
                    $validImages[] = new Image(
                        url: $item->url,
                        title: $item->title,
                        description: $item->description ?? null,
                        dataOrigin: DataOrigin::fromJson($item)
                    );
                } catch (\Throwable $e) {
                    Grav::instance()['log']->error(
                        'Failed to create Image: ' . $e->getMessage()
                    );
                }
            }
            $images = array_merge($images, $validImages);
        }

        return $images;
    }
}