<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

use Grav\Common\Grav;

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
        $services = array();

        foreach ($jsonList ?? [] as $json) {
            try {
                $services[] = new LayerService(
                    type: $json->type,
                    url: $json->url,
                    layers: Layer::fromJsonList($json->layer ?? []),
                );
            } catch (\Throwable $e) {
                Grav::instance()['log']->error(
                    'Failed to create a layer service: ' . $e->getMessage()
                );
            }
        }

        return $services;
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
        $layers = array();

        foreach ($jsonList ?? [] as $json) {
            try {
                $layers[] = new Layer(
                    name: $json->name,
                    title: $json->title ?? null,
                    legend: $json->legend ?? null,
                );
            } catch (\Throwable $e) {
                Grav::instance()['log']->error(
                    'Failed to create a layer: ' . $e->getMessage()
                );
            }
        }

        return $layers;
    }
}