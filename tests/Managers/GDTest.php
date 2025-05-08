<?php

namespace GNAHotelSolutions\ImageCacher\Tests\Managers;

use GNAHotelSolutions\ImageCacher\Format;
use GNAHotelSolutions\ImageCacher\Managers\GD;
use GNAHotelSolutions\ImageCacher\Tests\CacheFolder;
use PHPUnit\Framework\TestCase;

class GDTest extends TestCase
{
    use CacheFolder;

    const FIXTURES_PATH = __DIR__ . '/../fixtures';
    const IMAGES_PATH = __DIR__ . '/../fixtures/images';
    const CACHE_PATH = __DIR__ . '/../fixtures/cache';

    protected GD $manager;
    protected bool $avifSupported;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createFolder(self::CACHE_PATH);
        $this->manager = new GD();
        $this->avifSupported = function_exists('imagecreatefromavif') && function_exists('imageavif');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->deleteFolder(self::CACHE_PATH);
    }
    
    protected function prepareWebPTestImage(): string
    {
        $webpSource = self::IMAGES_PATH . '/bridge.webp';
        if (!file_exists($webpSource)) {
            $jpegSource = self::IMAGES_PATH . '/bridge.jpg';
            $jpegResource = imagecreatefromjpeg($jpegSource);
            imagewebp($jpegResource, $webpSource, 80);
            imagedestroy($jpegResource);
            
            $this->assertFileExists($webpSource, 'Failed to create test WebP image');
        }
        return $webpSource;
    }
    
    protected function prepareAvifTestImage(): string
    {
        if (!$this->avifSupported) {
            $this->markTestSkipped('AVIF support not available in this PHP/GD installation');
        }
        
        $avifSource = self::IMAGES_PATH . '/bridge.avif';
        if (!file_exists($avifSource)) {
            $jpegSource = self::IMAGES_PATH . '/bridge.jpg';
            $jpegResource = imagecreatefromjpeg($jpegSource);
            imageavif($jpegResource, $avifSource, 80);
            imagedestroy($jpegResource);
            
            $this->assertFileExists($avifSource, 'Failed to create test AVIF image');
        }
        return $avifSource;
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_image_resource_from_jpeg()
    {
        $source = self::IMAGES_PATH . '/bridge.jpg';
        $resource = $this->manager->create(Format::JPEG, $source);

        $this->assertNotNull($resource);
        $isValidImage = fn($img) => is_resource($img) || (is_object($img) && get_class($img) === 'GdImage');
        $this->assertTrue($isValidImage($resource));
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_process_jpeg_image()
    {
        $source = self::IMAGES_PATH . '/bridge.jpg';
        $resource = $this->manager->create(Format::JPEG, $source);

        $originalDimensions = [imagesx($resource), imagesy($resource)];
        $processed = $this->manager->process($resource, 200, 150, $originalDimensions, false);

        $this->assertNotNull($processed);

        $this->assertEquals(200, imagesx($processed));
        $this->assertEquals(150, imagesy($processed));
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_crop_image_when_processing()
    {
        $source = self::IMAGES_PATH . '/bridge.jpg';
        $resource = $this->manager->create(Format::JPEG, $source);

        $originalDimensions = [imagesx($resource), imagesy($resource)];
        $processed = $this->manager->process($resource, 200, 150, $originalDimensions, true);

        $this->assertNotNull($processed);

        $this->assertEquals(200, imagesx($processed));
        $this->assertEquals(150, imagesy($processed));
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_save_processed_image()
    {
        $source = self::IMAGES_PATH . '/bridge.jpg';
        $resource = $this->manager->create(Format::JPEG, $source);

        $originalDimensions = [imagesx($resource), imagesy($resource)];
        $processed = $this->manager->process($resource, 200, 150, $originalDimensions, false);

        $destination = self::CACHE_PATH . '/resized-bridge.jpg';
        $this->manager->save(Format::JPEG, $processed, $destination, 80);

        $this->assertFileExists($destination);

        $imageInfo = getimagesize($destination);
        $this->assertEquals(200, $imageInfo[0]);
        $this->assertEquals(150, $imageInfo[1]);
    }
    
    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_image_resource_from_webp()
    {
        $webpSource = $this->prepareWebPTestImage();
        
        $resource = $this->manager->create(Format::WEBP, $webpSource);
        $isValidImage = fn($img) => is_resource($img) || (is_object($img) && get_class($img) === 'GdImage');
        $this->assertTrue($isValidImage($resource), 'Failed to create WebP resource');
    }
    
    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_process_webp_image()
    {
        $webpSource = $this->prepareWebPTestImage();
        
        $resource = $this->manager->create(Format::WEBP, $webpSource);
        $originalDimensions = [imagesx($resource), imagesy($resource)];
        $processed = $this->manager->process($resource, 200, 150, $originalDimensions, false);
        
        $this->assertEquals(200, imagesx($processed));
        $this->assertEquals(150, imagesy($processed));
    }
    
    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_save_webp_image()
    {
        $webpSource = $this->prepareWebPTestImage();
        
        $resource = $this->manager->create(Format::WEBP, $webpSource);
        $originalDimensions = [imagesx($resource), imagesy($resource)];
        $processed = $this->manager->process($resource, 200, 150, $originalDimensions, false);
        
        $destination = self::CACHE_PATH . '/resized-bridge.webp';
        $this->manager->save(Format::WEBP, $processed, $destination, 80);
        
        $this->assertFileExists($destination);
        
        $imageInfo = getimagesize($destination);
        $this->assertEquals(200, $imageInfo[0]);
        $this->assertEquals(150, $imageInfo[1]);
        $this->assertEquals(IMAGETYPE_WEBP, $imageInfo[2]);
    }
    
    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_image_resource_from_avif()
    {
        if (!$this->avifSupported) {
            $this->markTestSkipped('AVIF support not available in this PHP/GD installation');
        }
        
        $avifSource = $this->prepareAvifTestImage();
        
        $resource = $this->manager->create(Format::AVIF, $avifSource);
        $isValidImage = fn($img) => is_resource($img) || (is_object($img) && get_class($img) === 'GdImage');
        $this->assertTrue($isValidImage($resource), 'Failed to create AVIF resource');
    }
    
    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_process_avif_image()
    {
        if (!$this->avifSupported) {
            $this->markTestSkipped('AVIF support not available in this PHP/GD installation');
        }
        
        $avifSource = $this->prepareAvifTestImage();
        
        $resource = $this->manager->create(Format::AVIF, $avifSource);
        $originalDimensions = [imagesx($resource), imagesy($resource)];
        $processed = $this->manager->process($resource, 200, 150, $originalDimensions, false);
        
        $this->assertEquals(200, imagesx($processed));
        $this->assertEquals(150, imagesy($processed));
    }
    
    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_save_avif_image()
    {
        if (!$this->avifSupported) {
            $this->markTestSkipped('AVIF support not available in this PHP/GD installation');
        }
        
        $avifSource = $this->prepareAvifTestImage();
        
        $resource = $this->manager->create(Format::AVIF, $avifSource);
        $originalDimensions = [imagesx($resource), imagesy($resource)];
        $processed = $this->manager->process($resource, 200, 150, $originalDimensions, false);
        
        $destination = self::CACHE_PATH . '/resized-bridge.avif';
        $this->manager->save(Format::AVIF, $processed, $destination, 80);
        
        $this->assertFileExists($destination);
        
        // En algunas versiones, PHP getimagesize podría no identificar AVIF correctamente
        // así que verificamos básicamente que exista y tenga un tamaño
        $this->assertGreaterThan(0, filesize($destination), 'Saved AVIF file is empty');
    }
}
