<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

class Relation
{

    public function __construct(
        public string $name,

        /** @var RelationItem[] */
        public array  $items,
    )
    {
    }


    static public function fromJsonList(
        ?array $jsonList
    ): array
    {
        $relations = $jsonList ?? [];

        return array_map(fn($json) => new self(
            name: $json->name,
            items: RelationItem::fromJsonList($json->items),
        ), $relations);

    }
}

class RelationItem
{
    public function __construct(
        public string $oid,
        public string $name,
    )
    {
    }


    static public function fromJsonList(?array $jsonList): array
    {
        $items = $jsonList ?? [];

        return array_map(fn($json) => new self(
            oid: $json->oid,
            name: $json->name,
        ), $items);
    }
}