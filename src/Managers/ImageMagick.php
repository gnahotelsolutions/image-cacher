<?php

namespace GNAHotelSolutions\ImageCacher\Managers;

use GNAHotelSolutions\ImageCacher\Format;
use Exception;

class ImageMagick extends Manager
{
    public function __construct()
    {
        if (!extension_loaded('imagick')) {
            throw new Exception('ImageMagick extension is not loaded');
        }
    }

    public function create(string $format, string $path)
    {
        if (!in_array($format, [Format::JPEG, Format::PNG, Format::GIF, Format::WEBP, Format::AVIF])) {
            throw new Exception("Image type [$format] not supported by ImageMagick.");
        }

        try {
            $imagick = new \Imagick($path);

            if ($format === Format::AVIF) {
                return imagecreatefromstring($imagick->getImageBlob());
            }

            return $imagick;
        } catch (\Exception $e) {
            throw new Exception("Error loading image with ImageMagick: " . $e->getMessage());
        }
    }

    public function save(string $format, $layout, string $name, int $quality = 80): string
    {
        if (!in_array($format, [Format::JPEG, Format::PNG, Format::GIF, Format::WEBP, Format::AVIF])) {
            throw new Exception("Format [$format] not supported by ImageMagick.");
        }

        try {
            $imagick = $this->getImagickObject($layout);

            $imagick->setImageFormat($format);
            $imagick->setCompressionQuality($quality);

            $imagick->writeImage($name);

            return $name;
        } catch (\Exception $e) {
            throw new Exception("Error saving image with ImageMagick: " . $e->getMessage());
        }
    }

    protected function getImagickObject($layout)
    {
        if (is_object($layout) && get_class($layout) === 'Imagick') {
            return $layout;
        }

        if (is_string($layout)) {
            $imagick = new \Imagick();
            $imagick->readImageBlob($layout);
            return $imagick;
        }

        if (is_resource($layout) || (is_object($layout) && get_class($layout) === 'GdImage')) {
            ob_start();
            imagepng($layout);
            $imageBlob = ob_get_clean();

            $imagick = new \Imagick();
            $imagick->readImageBlob($imageBlob);
            return $imagick;
        }

        throw new Exception("Unsupported layout type for ImageMagick.");
    }

    public function process($imageResource, int $width, int $height, array $originalDimensions, bool $crop = false, int $sharpen = 0)
    {
        try {
            $imagick = $this->ensureImagickObject($imageResource);

            $processedImage = clone $imagick;

            if ($crop) {
                [$cutWidth, $cutHeight, $cutX, $cutY] = $this->calculateCropDimensions(
                    $originalDimensions[0],
                    $originalDimensions[1],
                    $width,
                    $height
                );

                $processedImage->cropImage(
                    $cutWidth,
                    $cutHeight,
                    $cutX,
                    $cutY
                );
            }

            $processedImage->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);

            if ($sharpen > 0) {
                $sharpenAmount = $sharpen / 25;
                $processedImage->sharpenImage(0, $sharpenAmount);
            }

            return $processedImage;
        } catch (\Exception $e) {
            throw new Exception("Error processing image with ImageMagick: " . $e->getMessage());
        }
    }

    protected function ensureImagickObject($resource)
    {
        if (is_object($resource) && get_class($resource) === 'Imagick') {
            return $resource;
        }

        if (is_resource($resource) || (is_object($resource) && get_class($resource) === 'GdImage')) {
            ob_start();
            imagepng($resource);
            $imageBlob = ob_get_clean();

            $imagick = new \Imagick();
            $imagick->readImageBlob($imageBlob);
            return $imagick;
        }

        throw new Exception("Unsupported resource type for ImageMagick processing.");
    }
}
