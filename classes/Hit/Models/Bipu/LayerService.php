<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

class LayerService
{
    public function __construct(
        public string $type,
        public string $url,

        /** @var Layer[] */
        public array  $layers,
    )
    {
    }

    static public function fromJsonList(?array $jsonList): array
    {
        // Filter out services without layers
        $services = array_filter($jsonList ?? [], fn($json) => !empty($json->layer));

        return array_map(fn($service) => new LayerService(
            type: $service->type,
            url: $service->url,
            layers: Layer::fromJsonList($service->layer),
        ), $services);
    }
}

class Layer
{
    public function __construct(
        public string  $name,
        public ?string $title,
        public ?string $legend,
    )
    {
    }

    public static function fromJsonList(array $jsonList): array
    {
        return array_map(fn($json) => new Layer(
            name: $json->name,
            title: $json->title ?? null,
            legend: $json->legend ?? null,
        ), $jsonList);
    }
}