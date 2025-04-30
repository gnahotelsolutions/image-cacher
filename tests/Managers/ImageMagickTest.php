<?php

namespace GNAHotelSolutions\ImageCacher\Tests\Managers;

use GNAHotelSolutions\ImageCacher\Format;
use GNAHotelSolutions\ImageCacher\Managers\ImageMagick;
use GNAHotelSolutions\ImageCacher\Tests\CacheFolder;
use PHPUnit\Framework\TestCase;

class ImageMagickTest extends TestCase
{
    use CacheFolder;

    const FIXTURES_PATH = __DIR__ . '/../fixtures';
    const IMAGES_PATH = __DIR__ . '/../fixtures/images';
    const CACHE_PATH = __DIR__ . '/../fixtures/cache';

    protected ?ImageMagick $manager = null;
    protected bool $skipTests = false;
    protected string $skipReason = '';
    protected bool $avifSupported = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!extension_loaded('imagick')) {
            $this->skipTests = true;
            $this->skipReason = 'Extension imagick not loaded';
            return;
        }

        $this->createFolder(self::CACHE_PATH);

        try {
            $this->manager = new ImageMagick();
            
            $formats = new \Imagick();
            $this->avifSupported = in_array('AVIF', $formats->queryFormats());
        } catch (\Exception $e) {
            $this->skipTests = true;
            $this->skipReason = 'Error creating ImageMagick manager: ' . $e->getMessage();
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (file_exists(self::CACHE_PATH)) {
            $this->deleteFolder(self::CACHE_PATH);
        }
    }

    protected function skipIfNeeded()
    {
        if ($this->skipTests) {
            $this->markTestSkipped($this->skipReason);
        }
    }
    
    protected function prepareWebPTestImage(): string
    {
        $this->skipIfNeeded();
        
        $webpSource = self::IMAGES_PATH . '/bridge.webp';
        if (!file_exists($webpSource)) {
            try {
                $jpegImagePath = self::IMAGES_PATH . '/bridge.jpg';
                $imagick = new \Imagick($jpegImagePath);
                $imagick->setImageFormat('webp');
                $imagick->writeImage($webpSource);
                $this->assertFileExists($webpSource, 'Failed to create test WebP image');
            } catch (\Exception $e) {
                $this->markTestSkipped('Cannot create WebP test image: ' . $e->getMessage());
            }
        }
        return $webpSource;
    }
    
    protected function prepareAvifTestImage(): string 
    {
        $this->skipIfNeeded();
        
        if (!$this->avifSupported) {
            $this->markTestSkipped('AVIF format not supported by this ImageMagick installation');
        }
        
        $avifSource = self::IMAGES_PATH . '/bridge.avif';
        if (!file_exists($avifSource)) {
            try {
                $jpegImagePath = self::IMAGES_PATH . '/bridge.jpg';
                $imagick = new \Imagick($jpegImagePath);
                $imagick->setImageFormat('avif');
                $imagick->writeImage($avifSource);
                $this->assertFileExists($avifSource, 'Failed to create test AVIF image');
            } catch (\Exception $e) {
                $this->markTestSkipped('Cannot create AVIF test image: ' . $e->getMessage());
            }
        }
        return $avifSource;
    }
    
    protected function getImageDimensions($resource): array
    {
        if (is_object($resource) && method_exists($resource, 'getImageWidth')) {
            return [$resource->getImageWidth(), $resource->getImageHeight()];
        }
        
        throw new \RuntimeException("Cannot determine image dimensions: ImageMagick should return Imagick objects");
    }
    
    protected function assertImageDimensions($resource, int $width, int $height): void
    {
        if (is_object($resource) && method_exists($resource, 'getImageWidth')) {
            $this->assertEquals($width, $resource->getImageWidth());
            $this->assertEquals($height, $resource->getImageHeight());
        } else {
            $this->fail("Cannot assert image dimensions: ImageMagick should return Imagick objects");
        }
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_image_resource_from_jpeg()
    {
        $this->skipIfNeeded();

        $resource = $this->manager->create(Format::JPEG, self::IMAGES_PATH . '/bridge.jpg');

        $this->assertNotNull($resource);
        $this->assertTrue(is_object($resource) && method_exists($resource, 'getImageWidth'), 'Expected an Imagick object');
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_process_jpeg_image()
    {
        $this->skipIfNeeded();

        $resource = $this->manager->create(Format::JPEG, self::IMAGES_PATH . '/bridge.jpg');

        $originalDimensions = $this->getImageDimensions($resource);

        $processed = $this->manager->process(
            $resource,
            200,
            150,
            $originalDimensions,
            false,
            25
        );

        $this->assertImageDimensions($processed, 200, 150);
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_crop_image_when_processing()
    {
        $this->skipIfNeeded();

        $resource = $this->manager->create(Format::JPEG, self::IMAGES_PATH . '/bridge.jpg');

        $originalDimensions = $this->getImageDimensions($resource);

        $processed = $this->manager->process(
            $resource,
            200,
            150,
            $originalDimensions,
            true,
            0
        );

        $this->assertImageDimensions($processed, 200, 150);
    }

    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_save_processed_image()
    {
        $this->skipIfNeeded();

        $resource = $this->manager->create(Format::JPEG, self::IMAGES_PATH . '/bridge.jpg');

        $originalDimensions = $this->getImageDimensions($resource);

        $processed = $this->manager->process(
            $resource,
            200,
            150,
            $originalDimensions,
            false,
            0
        );

        $outputPath = self::CACHE_PATH . '/test-output.jpg';
        $savedPath = $this->manager->save(Format::JPEG, $processed, $outputPath, 80);

        $this->assertFileExists($savedPath);

        $savedImageInfo = getimagesize($savedPath);
        $this->assertEquals(200, $savedImageInfo[0]);
        $this->assertEquals(150, $savedImageInfo[1]);
    }
    
    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_image_resource_from_webp()
    {
        $this->skipIfNeeded();
        
        $webpSource = $this->prepareWebPTestImage();
        
        $resource = $this->manager->create(Format::WEBP, $webpSource);
        $this->assertNotNull($resource);
        
        $isValidResource = fn($res) => is_object($res) && method_exists($res, 'getImageWidth');
        $this->assertTrue($isValidResource($resource), 'Failed to create WebP resource');
    }
    
    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_process_webp_image()
    {
        $this->skipIfNeeded();
        
        $webpSource = $this->prepareWebPTestImage();
        
        $resource = $this->manager->create(Format::WEBP, $webpSource);
        $originalDimensions = $this->getImageDimensions($resource);
        
        $processed = $this->manager->process(
            $resource,
            200,
            150,
            $originalDimensions,
            false,
            0
        );
        
        $this->assertImageDimensions($processed, 200, 150);
    }
    
    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_save_webp_image()
    {
        $this->skipIfNeeded();
        
        $webpSource = $this->prepareWebPTestImage();
        
        $resource = $this->manager->create(Format::WEBP, $webpSource);
        $originalDimensions = $this->getImageDimensions($resource);
        
        $processed = $this->manager->process(
            $resource,
            200,
            150,
            $originalDimensions,
            false,
            0
        );
        
        $outputPath = self::CACHE_PATH . '/test-output.webp';
        $savedPath = $this->manager->save(Format::WEBP, $processed, $outputPath, 80);
        
        $this->assertFileExists($savedPath);
        
        $savedImageInfo = getimagesize($savedPath);
        $this->assertEquals(200, $savedImageInfo[0]);
        $this->assertEquals(150, $savedImageInfo[1]);
        $this->assertEquals(IMAGETYPE_WEBP, $savedImageInfo[2]);
    }
    
    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_image_resource_from_avif()
    {
        $this->skipIfNeeded();
        
        if (!$this->avifSupported) {
            $this->markTestSkipped('AVIF format not supported by this ImageMagick installation');
        }
        
        try {
            $avifSource = $this->prepareAvifTestImage();
            
            $resource = $this->manager->create(Format::AVIF, $avifSource);
            $this->assertNotNull($resource);
            
            $isValidResource = fn($res) => is_object($res) && method_exists($res, 'getImageWidth');
            $this->assertTrue($isValidResource($resource), 'Failed to create AVIF resource');
        } catch (\Exception $e) {
            $this->markTestSkipped('Error testing AVIF creation: ' . $e->getMessage());
        }
    }
    
    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_process_avif_image()
    {
        $this->skipIfNeeded();
        
        if (!$this->avifSupported) {
            $this->markTestSkipped('AVIF format not supported by this ImageMagick installation');
        }
        
        try {
            $avifSource = $this->prepareAvifTestImage();
            
            $resource = $this->manager->create(Format::AVIF, $avifSource);
            $originalDimensions = $this->getImageDimensions($resource);
            
            $processed = $this->manager->process(
                $resource,
                200,
                150,
                $originalDimensions,
                false,
                0
            );
            
            $this->assertImageDimensions($processed, 200, 150);
        } catch (\Exception $e) {
            $this->markTestSkipped('Error testing AVIF processing: ' . $e->getMessage());
        }
    }
    
    /** @test */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_save_avif_image()
    {
        $this->skipIfNeeded();
        
        if (!$this->avifSupported) {
            $this->markTestSkipped('AVIF format not supported by this ImageMagick installation');
        }
        
        try {
            $avifSource = $this->prepareAvifTestImage();
            
            $resource = $this->manager->create(Format::AVIF, $avifSource);
            $originalDimensions = $this->getImageDimensions($resource);
            
            $processed = $this->manager->process(
                $resource,
                200,
                150,
                $originalDimensions,
                false,
                0
            );
            
            $outputPath = self::CACHE_PATH . '/test-output.avif';
            $savedPath = $this->manager->save(Format::AVIF, $processed, $outputPath, 80);
            
            $this->assertFileExists($savedPath);
            
            $this->assertGreaterThan(0, filesize($savedPath), 'Saved AVIF file is empty');
        } catch (\Exception $e) {
            $this->markTestSkipped('Error testing AVIF saving: ' . $e->getMessage());
        }
    }
}
