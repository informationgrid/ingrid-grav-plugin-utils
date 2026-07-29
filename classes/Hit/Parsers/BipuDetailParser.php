<?php

namespace Grav\Plugin\InGridGravUtils\Hit\Parsers;

use Grav\Plugin\InGridGravUtils\Hit\Models\Bipu\Content;
use Grav\Plugin\InGridGravUtils\Hit\Models\Bipu\Detail;
use Grav\Plugin\InGridGravUtils\Hit\Models\Bipu\Image;
use Grav\Plugin\InGridGravUtils\Hit\Models\Bipu\LayerService;
use Grav\Plugin\InGridGravUtils\Hit\Models\Bipu\DataOrigin;
use Grav\Plugin\InGridGravUtils\Hit\Models\Bipu\Relation;
use Grav\Plugin\InGridGravUtils\Hit\Models\Bipu\Sensor;
use Grav\Plugin\InGridGravUtils\Hit\Models\Bipu\Species;
use Grav\Plugin\InGridGravUtils\Hit\Models\Bipu\Statistic;
use Grav\Plugin\InGridGravUtils\Hit\Models\Bipu\Bounds;
use Grav\Plugin\InGridGravUtils\Hit\Models\Bipu\Point;

class BipuDetailParser
{
    public static function parse(object $json): Detail
    {
        $metadata = $json->metadata ?? null;
        $umweltnavi = $json->umweltnavi;
        $spatial = $json->spatials[0] ?? null;

        $detail = new Detail($umweltnavi->oid);

        $detail->tags = $umweltnavi->tags ?? [];
        $detail->icon = $umweltnavi->icon ?? null;
        $detail->name = $json->title;
        $detail->address = $umweltnavi->contact ?? null;
        $detail->info_text = $umweltnavi->info_text ?? null;
        $detail->long_text = $json->description ?? null;

        $detail->centroid = Point::fromCentroid($spatial?->centroid ?? null);
        $detail->bounds = Bounds::fromBbox($spatial?->bbox ?? null);
        $detail->geoJson = isset($spatial?->geometry) ? json_encode($spatial->geometry) : null;

        $detail->images = Image::fromJsonList($umweltnavi->content_items ?? null);
        $detail->contents = Content::fromJsonList($umweltnavi->content_items ?? null);
        $detail->relations = Relation::fromJsonList($umweltnavi->relations ?? null);
        $detail->species = Species::fromJsonList($umweltnavi->collections[0]?->groups ?? null);
        $detail->statistics = Statistic::fromJsonList($umweltnavi->statistics ?? null);
        $detail->sensors = Sensor::fromJsonList($umweltnavi->sensor?->measurements ?? null);
        $detail->services = LayerService::fromJsonList($umweltnavi->service ?? null);

        $detail->dataOrigin = DataOrigin::fromJson($umweltnavi);
        $detail->partners = $umweltnavi->assigned_to ?? [];

        $detail->topic = $umweltnavi->topic_hierarchy?->topics[0] ?? null;
        $detail->subtopic = $umweltnavi->topic_hierarchy?->subtopics[0] ?? null;
        $detail->selectcategory = $umweltnavi->topic_hierarchy?->selectcategory ?? null;
        $detail->category = $umweltnavi->category->slug;
        $detail->category_name = $umweltnavi->category->name;

        $detail->import_date = $metadata?->created ?? null;
        $detail->modified_date = $metadata?->modified ?? null;
        $detail->meta_url = $umweltnavi->meta_url ?? null;

        return $detail;
    }
}