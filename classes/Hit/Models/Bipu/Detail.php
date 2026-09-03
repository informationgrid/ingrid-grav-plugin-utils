<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Models\Bipu;

class Detail
{
    public string $oid;

    /** @var string[] */
    public array $tags;

    public ?string $icon;

    public string $name;

    public ?string $address;

    public ?string $info_text;

    public ?string $long_text;

    public ?Point $centroid;

    public ?Bounds $bounds;

    public ?string $geoJson;

    /** @var Image[] */
    public array $images;

    /** @var Content[] */
    public array $contents;

    /** @var Relation[] */
    public array $relations;

    /** @var Species[] */
    public array $species;

    /** @var Statistic[] */
    public array $statistics;

    /** @var Sensor[] */
    public array $sensors;

    /** @var LayerService[] */
    public array $services;

    public ?DataOrigin $dataOrigin;

    /** @var string[] List of partnered federal stats, e.g. ni, rp */
    public array $partners;

    public ?string $topic;

    public ?string $subtopic;

    public ?string $selectcategory;

    public string $category;

    public string $category_name;

    public ?string $import_date;

    public ?string $modified_date;

    public ?string $meta_url;

    public function __construct(string $oid)
    {
        $this->oid = $oid;
    }
}