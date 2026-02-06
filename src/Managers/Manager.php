<?php

namespace GNAHotelSolutions\ImageCacher\Managers;

use GNAHotelSolutions\ImageCacher\Format;

abstract class Manager
{
    public abstract function create(string $format, string $path);
    public abstract function save(string $format, $layout, string $name, int $quality = 80, int $speed = -1);
    public abstract function process($imageResource, int $width, int $height, array $originalDimensions, bool $crop = false, int $sharpen = 0);

    protected static function isJpeg(string $format, string $name): bool
    {
        return $format === Format::JPEG || in_array(pathinfo($name, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'JPG',  'JPEG']);
    }

    protected function calculateCropDimensions(int $srcWidth, int $srcHeight, int $destWidth, int $destHeight): array
    {
        $aspectRatio = $destWidth / $destHeight;

        $cutWidth = round($srcHeight * $aspectRatio);

        if ($cutWidth > $srcWidth) {
            $cutWidth = $srcWidth;
        }

        $cutHeight = round($cutWidth / $aspectRatio);

        $cutX = ($srcWidth - $cutWidth) / 2;
        $cutY = ($srcHeight - $cutHeight) / 2;

        return [$cutWidth, $cutHeight, $cutX, $cutY];
    }
}
