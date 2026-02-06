<?php

namespace GNAHotelSolutions\ImageCacher\Managers;

use GNAHotelSolutions\ImageCacher\Format;
use Exception;

class GD extends Manager
{
    public function __construct()
    {
    }

    public function create(string $format, string $path)
    {
        if (! file_exists($path)) {
            throw new Exception("Image file does not exist: {$path}");
        }

        return match($format) {
            Format::JPEG => imagecreatefromjpeg($path),
            Format::PNG => imagecreatefrompng($path),
            Format::GIF => imagecreatefromgif($path),
            Format::WEBP => imagecreatefromwebp($path),
            Format::AVIF => function_exists('imagecreatefromavif') ? imagecreatefromavif($path) : throw new Exception("AVIF not supported by this GD installation"),
            default => throw new Exception("Image type [$format] not supported.")
        };
    }

    public function process($imageResource, int $width, int $height, array $originalDimensions, bool $crop = false, int $sharpen = 25)
    {
        $layout = imagecreatetruecolor($width, $height);

        $this->preserveTransparency($layout, $imageResource);

        if ($crop) {
            [$cutWidth, $cutHeight, $cutX, $cutY] = $this->calculateCropDimensions(
                $originalDimensions[0],
                $originalDimensions[1],
                $width,
                $height
            );
        } else {
            $cutWidth = $originalDimensions[0];
            $cutHeight = $originalDimensions[1];
            $cutX = 0;
            $cutY = 0;
        }

        imagecopyresampled(
            $layout,
            $imageResource,
            0, 0,
            $cutX, $cutY,
            $width, $height,
            $cutWidth, $cutHeight
        );

        if ($sharpen > 0) {
            $this->applySharpen($layout, $sharpen);
        }

        return $layout;
    }

    public function save(string $format, $layout, string $name, int $quality = 80, int $speed = -1)
    {
        if (self::isJpeg($format, $name)) {
            $format = Format::JPEG;
        }

        return match($format) {
            Format::JPEG => imagejpeg($layout, $name, $quality),
            Format::PNG => imagepng($layout, $name),
            Format::GIF => imagegif($layout, $name),
            Format::WEBP => imagewebp($layout, $name, $quality),
            Format::AVIF => function_exists('imageavif') ? imageavif($layout, $name, $quality, $speed) : throw new Exception("AVIF not supported by this GD installation"),
            default => throw new Exception("Image type [$format] not supported.")
        };
    }

    protected function preserveTransparency($layout, $source)
    {
        imagealphablending($layout, false);
        imagesavealpha($layout, true);
    }

    protected function applySharpen($layout, int $sharpen): void
    {
        $sharpenMatrix = [
            [-1, -1, -1],
            [-1, $sharpen, -1],
            [-1, -1, -1],
        ];

        $divisor = array_sum(array_map('array_sum', $sharpenMatrix));

        imageconvolution($layout, $sharpenMatrix, $divisor, 0);
    }
}
