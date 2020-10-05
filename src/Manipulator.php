<?php

namespace GNAHotelSolutions\ImageCacher;

class Manipulator
{
    public static function create(string $format, string $path)
    {
        if ($format === Format::JPEG) {
            return imagecreatefromjpeg($path);
        }

        if ($format === Format::PNG) {
            return imagecreatefrompng($path);
        }

        if ($format === Format::GIF) {
            return imagecreatefromgif($path);
        }

        if ($format === Format::WEBP) {
            return imagecreatefromwebp($path);
        }

        throw new \Exception("Image type [{$format}] not supported.");
    }

    public static function save(string $format, $layout, string $name): string
    {
        if ($format === Format::JPEG) {
            return imagejpeg($layout, $name);
        }

        if ($format === Format::PNG) {
            return imagepng($layout, $name);
        }

        if ($format === Format::GIF) {
            return imagegif($layout, $name);
        }

        if ($format === Format::WEBP) {
            return imagewebp($layout, $name);
        }

        throw new \Exception("Image type [{$format}] not supported.");
    }
}