<?php

namespace GNAHotelSolutions\ImageCacher;

class Image
{
    /** @var string */
    protected $rootPath;

    /** @var string */
    protected $name;

    /** @var string */
    protected $path;

    /** @var int */
    protected $width;

    /** @var int */
    protected $height;

    /** @var string */
    protected $type;

    public function __construct(string $image, string $rootPath = '')
    {
        $this->rootPath = rtrim($rootPath, '/');

        [$this->name, $this->path] = $this->splitNameAndPath($image);

        $this->verify();

        $this->extractImageInformation();
    }

    public function verify(): void
    {
        if (! file_exists($this->getOriginalFullPath())) {
            throw new \Exception("file [{$this->getOriginalFullPath()}] not found.");
        }
    }

    protected function splitNameAndPath(string $image): array
    {
        $pieces = explode('/', $image);

        return [array_pop($pieces), implode('/', $pieces)];
    }

    protected function extractImageInformation(): self
    {
        $information = getimagesize($this->getOriginalFullPath());

        $this->width = $information[0];
        $this->height = $information[1];
        $this->type = explode('/', $information['mime'])[1];

        return $this;
    }

    public function getOriginalName(): string
    {
        if ($this->getPath() === '') {
            return $this->getName();
        }

        return "{$this->getPath()}/{$this->getName()}";
    }

    public function getOriginalFullPath(): string
    {
        if ($this->rootPath === '') {
            return $this->getOriginalName();
        }

        return "{$this->rootPath}/{$this->getOriginalName()}";
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getAspectRatio(): float
    {
        return round($this->width / $this->height, 2);
    }

    public function isSmallerThan(?int $width, ?int $height): bool
    {
        return $this->width <= $width && $this->height <= $height;
    }
}
