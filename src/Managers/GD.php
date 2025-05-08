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

    public function process($imageResource, int $width, int $height, array $originalDimensions, bool $crop = false, int $sharpen = 0)
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

    public function save(string $format, $layout, string $name, int $quality = 80)
    {
        if (self::isJpeg($format, $name)) {
            $format = Format::JPEG;
        }

        return match($format) {
            Format::JPEG => $this->saveJpeg($layout, $name, $quality),
            Format::PNG => imagepng($layout, $name),
            Format::GIF => imagegif($layout, $name),
            Format::WEBP => $this->saveWebp($layout, $name, $quality),
            Format::AVIF => function_exists('imageavif') ? imageavif($layout, $name, $quality) : throw new Exception("AVIF not supported by this GD installation"),
            default => throw new Exception("Image type [$format] not supported.")
        };
    }

    protected function saveJpeg($layout, string $name, int $quality)
    {
        $image = imagejpeg($layout, $name, $quality);

        if (!$image) {
            throw new Exception("Failed to save JPEG image");
        }


        $jpegoptimAvailable = fn() => !empty(shell_exec("command -v jpegoptim"));

        if ($jpegoptimAvailable()) {
            exec("jpegoptim --max=$quality --strip-all --all-progressive --force $name", $output, $resultCode);


            if ($resultCode !== 0) {
                error_log("Warning: jpegoptim optimization failed for $name");
            }
        }

        return $image;
    }

    protected function saveWebp($layout, string $name, int $quality)
    {
        $image = imagewebp($layout, $name, $quality);

        if (!$image) {
            throw new Exception("Failed to save WebP image");
        }

        $cwebpAvailable = fn() => !empty(shell_exec("command -v cwebp"));

        if ($cwebpAvailable()) {
            exec("cwebp -m 6 -pass 10 -jpeg_like -mt -q $quality $name -o $name", $output, $resultCode);

            if ($resultCode !== 0) {
                error_log("Warning: cwebp optimization failed for $name");
            }
        }

        return $image;
    }

    protected function preserveTransparency($layout, $source)
    {
        if (imageistruecolor($source) && imagecolortransparent($source) >= 0) {
            imagealphablending($layout, false);
            imagesavealpha($layout, true);
        }
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
