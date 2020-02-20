<?php

namespace GNAHotelSolutions\ImageCacher\Tests;

use GNAHotelSolutions\ImageCacher\Cacher;
use GNAHotelSolutions\ImageCacher\Image;
use PHPUnit\Framework\TestCase;

class CacherTest extends TestCase
{
    const CACHE_PATH = 'cache';
    const CACHE_ROOT_PATH = __DIR__.'/fixtures';
    const IMAGES_ROOT_PATH = __DIR__.'/fixtures/images';

    public function setUp(): void
    {
        parent::setUp();

        @mkdir(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->deleteFolder(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH);
    }

    /** @test */
    public function it_does_not_create_cached_image_without_sizes()
    {
        $cacher = new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH);

        $resized = $cacher->crop(new Image('office/river.jpg', self::IMAGES_ROOT_PATH));

        $this->assertInstanceOf(Image::class, $resized);
        $this->assertDirectoryNotExists(self::CACHE_ROOT_PATH . '/office');
    }

    /** @test */
    public function it_creates_cached_image_from_width()
    {
        $cacher = new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH);

        $resized = $cacher->crop(new Image('bridge.jpg', self::IMAGES_ROOT_PATH), 200);

        $this->assertFileExists(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/200x200/bridge.jpg');

        $this->assertInstanceOf(Image::class, $resized);
    }

    /** @test */
    public function it_creates_cached_image_scaling_the_height()
    {
        $resized = (new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH))
            ->crop(new Image('office/meetings_room/plant.jpg', self::IMAGES_ROOT_PATH), 310);

        $this->assertEquals(310, $resized->getWidth());
        $this->assertEquals(233, $resized->getHeight());

        $this->assertFileExists(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/office/meetings_room/310x233/plant.jpg');
    }

    /** @test */
    public function it_creates_cached_image_scaling_the_width()
    {
        $resized = (new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH))
            ->crop(new Image('office/meetings_room/plant.jpg', self::IMAGES_ROOT_PATH), null, 233);

        $this->assertEquals(310, $resized->getWidth());
        $this->assertEquals(233, $resized->getHeight());

        $this->assertFileExists(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/office/meetings_room/310x233/plant.jpg');
    }

    /** @test */
    public function can_use_image_as_string_with_default_root_path()
    {
        $cacher = new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH, self::IMAGES_ROOT_PATH);

        $resized = $cacher->crop('office/meetings_room/plant.jpg', 200, 200);

        $this->assertEquals(200, $resized->getWidth());
        $this->assertEquals(200, $resized->getHeight());

        $this->assertFileExists(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/office/meetings_room/200x200/plant.jpg');
    }

    /** @test */
    public function can_use_cache_root_path()
    {
        $cacher = new Cacher('cache', self::CACHE_ROOT_PATH.'/v2', self::IMAGES_ROOT_PATH);

        $resized = $cacher->crop('office/meetings_room/plant.jpg', 200, 200);

        $this->assertEquals(200, $resized->getWidth());
        $this->assertEquals(200, $resized->getHeight());

        $this->assertFileExists(self::CACHE_ROOT_PATH.'/v2/'.self::CACHE_PATH.'/office/meetings_room/200x200/plant.jpg');

        $this->deleteFolder(self::CACHE_ROOT_PATH.'/v2/'.self::CACHE_PATH);
    }

    private function deleteFolder(string $path): void
    {
        foreach (scandir($path) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (is_dir("{$path}/{$file}")) {
                $this->deleteFolder("{$path}/{$file}");
            } else {
                unlink("{$path}/{$file}");
            }
        }

        rmdir($path);
    }
}
