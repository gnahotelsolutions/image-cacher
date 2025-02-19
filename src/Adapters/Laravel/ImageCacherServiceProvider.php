<?php

namespace GNAHotelSolutions\ImageCacher\Adapters\Laravel;

use GNAHotelSolutions\ImageCacher\Cacher;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ImageCacherServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/image-cacher.php', 'image-cacher');

        $this->app->singleton(Cacher::class, function (Application $app, array $params) {
            return new Cacher(
                $params['cache_path'] ?? config('image-cacher.cache_path'),
                $params['cache_root_path'] ?? config('image-cacher.cache_root_path'),
                $params['images_root_path'] ?? config('image-cacher.images_root_path'),
                $params['quality'] ?? config('image-cacher.quality', 80),
                $params['output_format'] ?? null,
                    $params['sharpen'] ?? config('image-cacher.sharpen', 25),
            );
        });
    }

    public function boot()
    {
        $this->publishes([__DIR__ . '/config/image-cacher.php' => config_path('image-cacher.php')]);
    }
}
