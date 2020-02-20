<?php

namespace GNAHotelSolutions\ImageCacher\Adapters\Laravel;

use GNAHotelSolutions\ImageCacher\Cacher;
use Illuminate\Support\ServiceProvider;

class ImageCacherServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/image-cacher.php', 'image-cacher');

        $this->app->singleton(Cacher::class, function () {
            return new Cacher(
                config('image-cacher.cache_path'), 
                config('image-cacher.cache_root_path'), 
                config('image-cacher.images_root_path')
            ); 
        });
    }

    public function boot()
    {
        $this->publishes([__DIR__.'/config/image-cacher.php' => config_path('image-cacher.php')]);
    }
}