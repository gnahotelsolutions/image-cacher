<?php

namespace GNAHotelSolutions\ImageCacher\Adapters\Laravel;

use GNAHotelSolutions\ImageCacher\Cacher;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Image resize(string|Image $image, int|null $width, int|null $height)
 * @method static Image crop(string|Image $image, int|null $width, int|null $height)
 *
 * @see \GNAHotelSolutions\ImageCacher\Cacher
 */
class ImageCacher extends Facade
{
    public static function getFacadeAccessor()
    {
        return Cacher::class;
    }
}