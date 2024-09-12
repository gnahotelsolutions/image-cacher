<?php

namespace GNAHotelSolutions\ImageCacher\Tests\Adapters\Laravel;

use GNAHotelSolutions\ImageCacher\Adapters\Laravel\Facades\ImageCacher;
use GNAHotelSolutions\ImageCacher\Adapters\Laravel\ImageCacherServiceProvider;
use GNAHotelSolutions\ImageCacher\Cacher;
use GNAHotelSolutions\ImageCacher\Format;
use GNAHotelSolutions\ImageCacher\Image;
use GNAHotelSolutions\ImageCacher\Tests\CacheFolder;
use Orchestra\Testbench\TestCase;

class ImageCacherTest extends TestCase
{
    use CacheFolder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createFolder(__DIR__.'/../../fixtures/cache');
    }

    protected function tearDown(): void
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

    /**
     * @group webp
     * @test
     */
    public function can_transform_images_to_webp_by_default()
    {
        config(['image-cacher' => [
            'cache_path' => 'cache',
            'cache_root_path' => __DIR__.'/../../fixtures',
            'images_root_path' => __DIR__.'/../../fixtures/images',
            'output_format' => Format::WEBP
        ]]);

        $resized = ImageCacher::crop('office/meetings_room/plant.jpg', 300, 300);

        $this->assertFileExists(__DIR__.'/../../fixtures/cache/office/meetings_room/300x300/plant.webp');

        $this->assertInstanceOf(Image::class, $resized);

        $this->assertEquals(Format::WEBP, $resized->getType());
    }

    /**
     * @group webp
     * @test
     */
    public function can_update_webp_quality_image_to_100()
    {
        config(['image-cacher' => [
            'cache_path' => 'cache',
            'cache_root_path' => __DIR__.'/../../fixtures',
            'images_root_path' => __DIR__.'/../../fixtures/images',
            'output_format' => Format::WEBP,
            'quality' => 100
        ]]);

        $resized = ImageCacher::crop('office/meetings_room/plant.jpg', 300, 300);

        $this->assertFileExists(__DIR__.'/../../fixtures/cache/office/meetings_room/300x300/plant.webp');

        $this->assertInstanceOf(Image::class, $resized);

        $this->assertEquals(100, config('image-cacher.quality'));
    }

    public function singleton_is_working()
    {
        $cacher1 = app()->make(Cacher::class);
        $cacher2 = app()->make(Cacher::class);

        $this->assertSame($cacher1, $cacher2);

        $this->assertSame(spl_object_id($cacher1), spl_object_id($cacher2));
    }
}
