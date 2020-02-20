<?php

namespace GNAHotelSolutions\ImageCacher;

class Cacher
{
    /** @var string  */
    protected $cachePath;
    
    /** @var string  */
    protected $cacheRootPath;

    /** @var string */
    protected $imagesRootPath;

    public function __construct(string $cachePath = 'cache/images', string $cacheRootPath = '', string $imagesRootPath = '')
    {
        $this->cachePath = $cachePath;
        $this->cacheRootPath = rtrim($cacheRootPath, '/');
        $this->imagesRootPath = rtrim($imagesRootPath, '/');
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

        if ($this->isSmallerThanRequested($image, $width, $height)) {
            return $image;
        }

        $resizedWidth = $width ?? round($height * $image->getAspectRatio());
        $resizedHeight = $height ?? round($width / $image->getAspectRatio());

        if ($this->isAlreadyCached($image, $resizedWidth, $resizedHeight)) {
            return new Image($this->getCachedImagePathName($image, $resizedWidth, $resizedHeight), $this->cacheRootPath);
        }

        $layout = imagecreatetruecolor($resizedWidth, $resizedHeight);

        if ($this->isAlpha($image)) {
            imagealphablending($layout, false);
            imagesavealpha($layout, true);
        }

        if ($cropImage) {
            [$cutWidth, $cutHeight] = $this->getCutEdges($image, $resizedWidth, $resizedHeight);
            $cutX = ($image->getWidth() - $cutWidth) / 2;
            $cutY = ($image->getHeight() - $cutHeight) / 2;
        } else {
            $cutWidth = $image->getWidth();
            $cutHeight = $image->getHeight();
            $cutX = 0;
            $cutY = 0;
        }

        imagecopyresampled($layout, $this->getImageResource($image), 0, 0, $cutX, $cutY, $resizedWidth, $resizedHeight, $cutWidth, $cutHeight);

        $this->saveImage($image, $layout, $resizedWidth, $resizedHeight);

        return new Image($this->getCachedImagePathName($image, $resizedWidth, $resizedHeight), $this->cacheRootPath);
    }

    protected function isSmallerThanRequested(Image $image, $width, $height): bool
    {
        return (! $width && ! $height) || $image->isSmallerThan($width, $height);
    }

    protected function isAlreadyCached(Image $image, $width, $height): bool
    {
        return file_exists($this->getCachedImageFullName($image, $width, $height));
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
        return in_array($image->getType(), ['gif', 'png']);
    }

    protected function getImageResource(Image $image)
    {
        if ($image->getType() === 'jpeg') {
            return imagecreatefromjpeg($image->getOriginalFullPath());
        }

        if ($image->getType() === 'png') {
            return imagecreatefrompng($image->getOriginalFullPath());
        }

        if ($image->getType() === 'gif') {
            return imagecreatefromgif($image->getOriginalFullPath());
        }

        throw new \Exception("Image type [{$image->getType()}] not supported.");
    }

    protected function getCutEdges(Image $image, int $width, int $height): array
    {
        $aspectRatio = $width / $height;

        $cutEdgeWidth = round($image->getHeight() * $aspectRatio);

        if ($cutEdgeWidth > $image->getWidth()) {
            $cutEdgeWidth = $image->getWidth();
        }

        $cutEdgeHeight = round($cutEdgeWidth / $aspectRatio);

        return [$cutEdgeWidth, $cutEdgeHeight];
    }

    protected function saveImage(Image $image, $layout, $width, $height): string
    {
        $this->createCacheDirectoryIfNotExists($image, $width, $height);

        if ($image->getType() === 'jpeg') {
            return imagejpeg($layout, $this->getCachedImageFullName($image, $width, $height));
        }

        if ($image->getType() === 'png') {
            return imagepng($layout, $this->getCachedImageFullName($image, $width, $height));
        }

        if ($image->getType() === 'gif') {
            return imagegif($layout, $this->getCachedImageFullName($image, $width, $height));
        }

        throw new \Exception("Image type [{$image->getType()}] not supported.");
    }

    protected function createCacheDirectoryIfNotExists(Image $image, $width, $height): void
    {
        $cachePath = "{$this->cacheRootPath}/{$this->cachePath}/{$this->getCacheImagePath($image->getPath(), $width, $height)}";

        if (substr($cachePath, 0, 1) !== '') {
            $cachePath = "/{$cachePath}";
        }

        if (! file_exists($cachePath)) {
            mkdir($cachePath, 0777, true);
        }
    }
}
