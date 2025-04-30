<?php

namespace GNAHotelSolutions\ImageCacher;

use Exception;
use GNAHotelSolutions\ImageCacher\Managers\ImageMagick;
use GNAHotelSolutions\ImageCacher\Managers\Manager;
use GNAHotelSolutions\ImageCacher\Managers\GD;

class Cacher
{
    /** @var string  */
    protected $cachePath;

    /** @var string  */
    protected $cacheRootPath;

    /** @var string */
    protected $imagesRootPath;

    /** @var string */
    protected $outputFormat = null;

    /** @var int */
    protected $quality = 80;

    /** @var int */
    protected $sharpen = 25;

    /** @var Manager */
    protected $manager = 'gd';

    const SUPPORTED_OUTPUT_FORMATS = [Format::WEBP, Format::AVIF, Format::PNG, Format::JPEG, Format::GIF];

    public function __construct(
        string $cachePath = 'cache/images',
        string $cacheRootPath = '',
        string $imagesRootPath = '',
        int $quality = 80,
        ?string $outputFormat = null,
        ?int $sharpen = 25,
        ?string $manager = 'image-magick'
    ) {
        $this->cachePath = $cachePath;
        $this->cacheRootPath = rtrim($cacheRootPath, '/');
        $this->imagesRootPath = rtrim($imagesRootPath, '/');
        $this->quality = $quality;
        $this->outputFormat = $outputFormat;
        $this->sharpen = $sharpen;

        $this->setManager($manager);
    }

    public function setManager(string $manager): void
    {
        $this->manager = match ($manager) {
            'gd' => new GD(),
            'image-magick' => new ImageMagick(),
            default => throw new Exception("Unsupported image manager: $manager"),
        };
    }

    public function setOutputFormat(string $format): self
    {
        if (! in_array($format, self::SUPPORTED_OUTPUT_FORMATS)) {
            throw new Exception("Cannot transform files into `{$format}` because is not a supported format.");
        }

        $this->outputFormat = $format;

        return $this;
    }

    public function resize($image, $width = null, $height = null): Image
    {
        return $this->manipulate($image, $width, $height, false);
    }

    public function crop($image, $width = null, $height = null): Image
    {
        return $this->manipulate($image, $width, $height, true);
    }

    protected function manipulate($image, $width = null, $height = null, bool $cropImage = false): Image
    {
        if (is_string($image)) {
            $image = new Image($image, $this->imagesRootPath);
        }

        if ($this->originalSizeIsRequested($width, $height)) {
            return $image;
        }

        $resizedWidth = $width ?: round($height * $image->getAspectRatio());
        $resizedHeight = $height ?: round($width / $image->getAspectRatio());

        if ($this->isSmallerThanRequested($image, $resizedWidth, $resizedHeight)) {
            return $image;
        }

        if ($this->outputFormat !== null && $this->outputFormat !== $image->getOutputFormat()) {
            $image->setOutputFormat($this->outputFormat);
        }

        if ($this->isAlreadyCached($image, $resizedWidth, $resizedHeight)) {
            return new Image($this->getCachedImagePathName($image, $resizedWidth, $resizedHeight), $this->cacheRootPath);
        }

        $imageResource = $this->getImageResource($image);
        
        $processedImage = $this->manager->process(
            $imageResource,
            $resizedWidth,
            $resizedHeight,
            [$image->getWidth(), $image->getHeight()],
            $cropImage,
            $this->sharpen
        );
        
        $this->saveImage($image, $processedImage, $resizedWidth, $resizedHeight);

        return new Image($this->getCachedImagePathName($image, $resizedWidth, $resizedHeight), $this->cacheRootPath);
    }

    protected function saveImage(Image $image, $processedImage, $width, $height): string
    {
        $this->createCacheDirectoryIfNotExists($image, $width, $height);

        if (!$this->hasValidName($image->getName())) {
            throw new Exception("Image name is not supported.");
        }

        try {
            return $this->manager->save(
                $image->getOutputFormat(),
                $processedImage,
                $this->getCachedImageFullName($image, $width, $height),
                $this->quality
            );
        } catch (Exception $e) {
            throw new Exception("Error al guardar la imagen: " . $e->getMessage());
        }
    }

    protected function isSmallerThanRequested(Image $image, $width, $height): bool
    {
        return $image->isSmallerThan($width, $height);
    }

    protected function originalSizeIsRequested($width, $height): bool
    {
        return !$width && !$height;
    }

    protected function isAlreadyCached(Image $image, $width, $height): bool
    {
        return file_exists($this->getCachedImageFullName($image, $width, $height))
            && $this->cachedImageIsTheSame($image, $width, $height);
    }

    protected function cachedImageIsTheSame(Image $image, $width, $height): bool
    {
        $cachedImageUpdatedAt = filemtime($this->getCachedImageFullName($image, $width, $height));
        $imageUpdatedAt = filemtime($image->getOriginalFullPath());

        return $cachedImageUpdatedAt >= $imageUpdatedAt;
    }

    protected function getCachedImageFullName(Image $image, $width, $height): string
    {
        if ($this->cacheRootPath === '') {
            return $this->getCachedImagePathName($image, $width, $height);
        }

        return "{$this->cacheRootPath}/{$this->getCachedImagePathName($image, $width, $height)}";
    }

    protected function getCachedImagePathName(Image $image, $width, $height): string
    {
        return "{$this->cachePath}/{$this->getCachedImageName($image, $width, $height)}";
    }

    protected function getCachedImageName(Image $image, $width, $height): string
    {
        return "{$this->getCacheImagePath($image->getPath(), $width, $height)}/{$image->getName()}";
    }

    protected function getCacheImagePath(string $path, $width, $height): string
    {
        return ltrim("{$path}/{$width}x{$height}", '/');
    }

    protected function isAlpha(Image $image): bool
    {
        return in_array($image->getType(), [Format::GIF, Format::PNG]);
    }

    protected function getImageResource(Image $image)
    {
        return $this->manager->create($image->getType(), $image->getOriginalFullPath());
    }

    protected function hasValidName(string $name): bool
    {
        return preg_match('/^[a-zA-Z0-9_\-\.]+$/', $name);
    }

    protected function createCacheDirectoryIfNotExists(Image $image, $width, $height): void
    {
        $cachePath = "{$this->cacheRootPath}/{$this->cachePath}/{$this->getCacheImagePath($image->getPath(), $width, $height)}";

        if (! in_array(substr($cachePath, 0, 1), ['', '/'])) {
            $cachePath = "/{$cachePath}";
        }

        if (! file_exists($cachePath)) {
            mkdir($cachePath, 0777, true);
        }
    }
}
