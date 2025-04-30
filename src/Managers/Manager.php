<?php

namespace GNAHotelSolutions\ImageCacher\Managers;

use GNAHotelSolutions\ImageCacher\Format;

abstract class Manager
{
    public abstract function create(string $format, string $path);
    public abstract function save(string $format, $layout, string $name, int $quality = 80);
    public abstract function process($imageResource, int $width, int $height, array $originalDimensions, bool $crop = false, int $sharpen = 0);

    protected static function isJpeg(string $format, string $name): bool
    {
        return $format === Format::JPEG || in_array(pathinfo($name, PATHINFO_EXTENSION), ['jpg', 'jpeg']);
    }
}
