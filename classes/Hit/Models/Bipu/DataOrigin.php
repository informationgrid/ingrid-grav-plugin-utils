<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

class DataOrigin
{
    public ?Source $source = null;
    public ?Source $author = null;
    public ?Source $owner = null;
    public ?Source $license = null;

    static public function fromJson(object $json): ?DataOrigin
    {
        $origin = new self();

        $origin->source = Source::fromJson($json->source ?? null);
        $origin->author = Source::fromJson($json->author ?? null);
        $origin->owner = Source::fromJson($json->owner ?? null);
        $origin->license = Source::fromJson($json->license ?? null);

        if (!isset($origin->source) && !isset($origin->author) && !isset($origin->owner) && !isset($origin->license)) {
            return null;
        }

        return $origin;
    }
}