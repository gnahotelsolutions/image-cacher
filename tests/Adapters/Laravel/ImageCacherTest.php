<?php

namespace GNAHotelSolutions\ImageCacher\Tests\Adapters\Laravel;

use GNAHotelSolutions\ImageCacher\Adapters\Laravel\Facades\ImageCacher;
use GNAHotelSolutions\ImageCacher\Adapters\Laravel\ImageCacherServiceProvider;
use GNAHotelSolutions\ImageCacher\Image;
use GNAHotelSolutions\ImageCacher\Tests\CacheFolder;
use Orchestra\Testbench\TestCase;

class ImageCacherTest extends TestCase
{
    use CacheFolder;

    public function setUp(): void
    {
        parent::setUp();

        $this->createFolder(__DIR__.'/../../fixtures/cache');
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->deleteFolder(__DIR__.'/../../fixtures/cache');
    }

    protected function getPackageProviders($app)
    {
        return [ImageCacherServiceProvider::class];
    }

    /** @test */
    public function can_generate_image_using_facade()
    {
        config(['image-cacher' => [
            'cache_path' => 'cache',
            'cache_root_path' => __DIR__.'/../../fixtures',
            'images_root_path' => __DIR__.'/../../fixtures/images',
        ]]);

        $resized = ImageCacher::crop('office/meetings_room/plant.jpg', 300, 300);

        $this->assertFileExists(__DIR__.'/../../fixtures/cache/office/meetings_room/300x300/plant.jpg');

        $this->assertInstanceOf(Image::class, $resized);
    }
}
