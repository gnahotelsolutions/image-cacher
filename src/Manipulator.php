<?php

namespace GNAHotelSolutions\ImageCacher;

use Exception;

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

        throw new Exception("Image type [{$format}] not supported.");
    }

    public static function save(string $format, $layout, string $name, int $quality = 80): string
    {
        if (self::isJpeg($format, $name)) {
            $image = imagejpeg($layout, $name);

            exec("jpegoptim --max=$quality --strip-all --all-progressive --force $name", $output, $resultCode);

            if ($resultCode !== 0) {
                throw new Exception("Error optimizing image.");
            }

            return $image;
        }

        if ($format === Format::PNG) {
            return imagepng($layout, $name);
        }

        if ($format === Format::GIF) {
            return imagegif($layout, $name);
        }

        if ($format === Format::WEBP) {
            $image = imagewebp($layout, $name);

            exec("cwebp -m 6 -pass 10 -jpeg_like -mt -q $quality $name -o $name", $output, $resultCode);

            if ($resultCode !== 0) {
                throw new Exception('Error optimizing image');
            }

            return $image;
        }

        throw new Exception('Image type [$format] not supported.');
    }

    protected static function isJpeg(string $format, string $name): bool
    {
        return $format === Format::JPEG || in_array(pathinfo($name, PATHINFO_EXTENSION), ['jpg', 'jpeg']);
    }
}
