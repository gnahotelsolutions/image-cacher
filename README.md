# Create smaller versions of the same image

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gnahotelsolutions/image-cacher.svg?style=flat-square)](https://packagist.org/packages/gnahotelsolutions/image-cacher)
![Build status](https://github.com/gnahotelsolutions/image-cacher/actions/workflows/php.yml/badge.svg)
[![Quality Score](https://img.shields.io/scrutinizer/g/gnahotelsolutions/image-cacher.svg?style=flat-square)](https://scrutinizer-ci.com/g/gnahotelsolutions/image-cacher)
[![Total Downloads](https://img.shields.io/packagist/dt/gnahotelsolutions/image-cacher.svg?style=flat-square)](https://packagist.org/packages/gnahotelsolutions/image-cacher)

With this package you can create smaller versions of the same image to optimize user's transfer data. What's the point of downloading a 3000x3000 image on a 200x200 thumbnail? This package is very helpful when dealing with [responsive images](https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images)

## Installation

You can install the package via composer:

```bash
composer require gnahotelsolutions/image-cacher
```

## Usage

```php

use GNAHotelSolutions\ImageCacher\Cacher;
use GNAHotelSolutions\ImageCacher\Image;

// Image located in public/img/hotel/rooms/double-room.jpg
$image = new Image('hotel/rooms/double-room.jpg', 'img');

$resized = (new Cacher())->resize($image, 1920, 1080); // Get a smaller version of the image or the same if the size is smaller.

$cropped = (new Cacher())->crop($image, 1920, 1080); // Get a cropped version of the image.
```

### Generating a thumbnail

```php
<?php 
$cacher = new Cacher();
$image = new Image('hotel/rooms/double-room.jpg', 'img'); 
?>

<img src="<?= $cacher->crop($image, 200, 200)->getOriginalName(); ?>" class="thumbnail">
```

### Combining with srcset attribute

```php
<?php 
$cacher = new Cacher();
$image = new Image('hotel/rooms/double-room.jpg', 'img'); 
?>

<img src="<?= $cacher->crop($image, 800, 600)->getOriginalName(); ?>"
     srcset="<?= $cacher->crop($image, 400, 280)->getOriginalName(); ?> 400w,
             <?= $cacher->crop($image, 800, 600)->getOriginalName(); ?> 800w"
     sizes="(max-width: 480px) 400px), 800px"
>
```

### Using with Laravel

The package comes with a Facade you can use on your Laravel projects. Use the configuration file to tell where the cache path and the images root path are.

First of all, publish the package configuration file:

```
php artisan vendor:publish --provider="GNAHotelSolutions\ImageCacher\Adapters\Laravel\ImageCacherServiceProvider"
```

Adapt the `config/image-cacher.php` file variables to your needs. Default ones works well with a standard Laravel app.

```php
<img src="{{ ImageCacher::crop('hotel/rooms/double-room.jpg', 800, 600)->getOriginalName() }}">
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email dllop@gnahs.com instead of using the issue tracker.

## Credits

- [David Llop](https://github.com/lloople)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
