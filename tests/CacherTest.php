<?php

namespace GNAHotelSolutions\ImageCacher\Tests;

use GNAHotelSolutions\ImageCacher\Cacher;
use GNAHotelSolutions\ImageCacher\Format;
use GNAHotelSolutions\ImageCacher\Image;
use PHPUnit\Framework\TestCase;

class CacherTest extends TestCase
{
    use CacheFolder;

    const CACHE_PATH = 'cache';
    const CACHE_ROOT_PATH = __DIR__.'/fixtures';
    const IMAGES_ROOT_PATH = __DIR__.'/fixtures/images';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createFolder(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->deleteFolder(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH);
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_create_cached_image_without_sizes()
    {
        $cacher = new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH);

        $resized = $cacher->crop(new Image('office/river.jpg', self::IMAGES_ROOT_PATH));

        $this->assertInstanceOf(Image::class, $resized);
        $this->assertDirectoryDoesNotExist(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH . '/office');
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_cached_image_from_width()
    {
        $cacher = new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH);

        $resized = $cacher->crop(new Image('bridge.jpg', self::IMAGES_ROOT_PATH), 200);

        $this->assertFileExists(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/200x200/bridge.jpg');

        $this->assertInstanceOf(Image::class, $resized);
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_cached_image_scaling_the_height()
    {
        $resized = (new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH))
            ->crop(new Image('office/meetings_room/plant.jpg', self::IMAGES_ROOT_PATH), 310);

        $this->assertSame(310, $resized->getWidth());
        $this->assertSame(233, $resized->getHeight());

        $this->assertFileExists(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/office/meetings_room/310x233/plant.jpg');
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_cached_image_scaling_the_width()
    {
        $resized = (new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH))
            ->crop(new Image('office/meetings_room/plant.jpg', self::IMAGES_ROOT_PATH), null, 233);

        $this->assertSame(310, $resized->getWidth());
        $this->assertSame(233, $resized->getHeight());

        $this->assertFileExists(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/office/meetings_room/310x233/plant.jpg');
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function can_use_image_as_string_with_default_root_path()
    {
        $cacher = new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH, self::IMAGES_ROOT_PATH);

        $resized = $cacher->crop('office/meetings_room/plant.jpg', 200, 200);

        $this->assertSame(200, $resized->getWidth());
        $this->assertSame(200, $resized->getHeight());

        $this->assertFileExists(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/office/meetings_room/200x200/plant.jpg');
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function can_use_cache_root_path()
    {
        $cacher = new Cacher('cache', self::CACHE_ROOT_PATH.'/v2', self::IMAGES_ROOT_PATH);

        $resized = $cacher->crop('office/meetings_room/plant.jpg', 200, 200);

        $this->assertSame(200, $resized->getWidth());
        $this->assertSame(200, $resized->getHeight());

        $this->assertFileExists(self::CACHE_ROOT_PATH.'/v2/'.self::CACHE_PATH.'/office/meetings_room/200x200/plant.jpg');

        $this->deleteFolder(self::CACHE_ROOT_PATH.'/v2');
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_overrides_already_cached_image_if_modified()
    {
        // Create a cached version of the image first, using original image
        mkdir(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/office/meetings_room/200x200', 0777, true);
        copy(self::IMAGES_ROOT_PATH.'/office/meetings_room/plant.jpg', self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/office/meetings_room/200x200/plant.jpg');

        // Set older modification date for the cached image
        touch(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/office/meetings_room/200x200/plant.jpg', now()->subYears(20)->timestamp);

        $previousCachedImage = new Image('office/meetings_room/200x200/plant.jpg', self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH);
        $this->assertNotEquals(200, $previousCachedImage->getWidth());

        $cacher = new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH, self::IMAGES_ROOT_PATH);

        $resized = $cacher->crop('office/meetings_room/plant.jpg', 200, 200);

        $this->assertSame(200, $resized->getWidth());
        $this->assertSame(200, $resized->getHeight());

        $this->assertFileExists(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/office/meetings_room/200x200/plant.jpg');
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_overrides_already_cached_image_if_not_modified()
    {
        // Create a cached version of the image first, using original image
        mkdir(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/office/meetings_room/200x200', 0777, true);
        copy(self::IMAGES_ROOT_PATH.'/office/meetings_room/plant.jpg', self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/office/meetings_room/200x200/plant.jpg');

        $previousCachedImage = new Image('office/meetings_room/200x200/plant.jpg', self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH);

        $cacher = new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH, self::IMAGES_ROOT_PATH);

        $resized = $cacher->crop('office/meetings_room/plant.jpg', 200, 200);

        $this->assertNotEquals(200, $resized->getWidth());
        $this->assertSame($previousCachedImage->getWidth(), $resized->getWidth());
        $this->assertSame($previousCachedImage->getHeight(), $resized->getHeight());

        $this->assertFileExists(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/office/meetings_room/200x200/plant.jpg');
    }

    /**
     * @group webp
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_cached_image_from_webp_format()
    {
        $resized = (new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH))
            ->crop(new Image('office/meetings_room/plant.webp', self::IMAGES_ROOT_PATH), null, 233);

        $this->assertSame(310, $resized->getWidth());
        $this->assertSame(233, $resized->getHeight());

        $this->assertFileExists(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/office/meetings_room/310x233/plant.webp');
    }

    /**
     * @group webp
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_webp_cached_image_from_jpg_format()
    {
        $resized = (new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH))
            ->setOutputFormat(Format::WEBP)
            ->crop(new Image('office/meetings_room/plant.jpg', self::IMAGES_ROOT_PATH), null, 233);

        $this->assertSame(310, $resized->getWidth());
        $this->assertSame(233, $resized->getHeight());

        $this->assertFileExists(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/office/meetings_room/310x233/plant.webp');
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_cannot_create_image_from_unsupported_format()
    {
        $this->expectExceptionMessage('Cannot transform files into `gnahs` because is not a supported format.');

        $resized = (new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH))
            ->setOutputFormat('gnahs')
            ->crop(new Image('office/meetings_room/plant.jpg', self::IMAGES_ROOT_PATH), null, 233);

    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_use_gd_manager()
    {
        $cacher = new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH, self::IMAGES_ROOT_PATH, 80, null, 25, 'gd');

        $resized = $cacher->resize('bridge.jpg', 200, 150);

        $this->assertInstanceOf(Image::class, $resized);
        $this->assertFileExists(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/200x150/bridge.jpg');

        // Verificar dimensiones del archivo generado
        $imageInfo = getimagesize(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/200x150/bridge.jpg');
        $this->assertEquals(200, $imageInfo[0]);
        $this->assertEquals(150, $imageInfo[1]);
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_use_imagick_manager_if_available()
    {
        $imageMagickAvailable = extension_loaded('imagick');

        if (!$imageMagickAvailable) {
            $this->markTestSkipped('ImageMagick extension not available');
        }

        $cacher = new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH, self::IMAGES_ROOT_PATH, 80, null, 25, 'image-magick');

        $cacher->resize('bridge.jpg', 200, 150);

        $this->assertFileExists(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/200x150/bridge.jpg');

        // Verificar dimensiones del archivo generado
        $imageInfo = getimagesize(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/200x150/bridge.jpg');
        $this->assertEquals(200, $imageInfo[0]);
        $this->assertEquals(150, $imageInfo[1]);
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_set_manager_after_initialization()
    {
        $cacher = new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH, self::IMAGES_ROOT_PATH);

        $cacher->setManager('gd');

        $cacher->resize('bridge.jpg', 200, 150);

        $this->assertFileExists(self::CACHE_ROOT_PATH.'/'.self::CACHE_PATH.'/200x150/bridge.jpg');
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_crop_using_selected_manager()
    {
        $cacher = new Cacher(
            self::CACHE_PATH,
            self::CACHE_ROOT_PATH,
            self::IMAGES_ROOT_PATH,
            80,
            null,
            25,
            'gd'
        );

        $cacher->crop('bridge.jpg', 200, 150);

        $this->assertFileExists(self::CACHE_ROOT_PATH . '/' . self::CACHE_PATH . '/200x150/bridge.jpg');

        $imageInfo = getimagesize(self::CACHE_ROOT_PATH . '/' . self::CACHE_PATH . '/200x150/bridge.jpg');
        $this->assertEquals(200, $imageInfo[0]);
        $this->assertEquals(150, $imageInfo[1]);
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_on_invalid_manager()
    {
        $this->expectExceptionMessage('Unsupported image manager: invalid-manager');

        $this->expectException(\Exception::class);

        $cacher = new Cacher(self::CACHE_PATH, self::CACHE_ROOT_PATH, self::IMAGES_ROOT_PATH);

        $cacher->setManager('invalid-manager');
    }
}
