<?php

namespace GNAHotelSolutions\ImageCacher\Tests;

use Exception;
use GNAHotelSolutions\ImageCacher\Image;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    const IMAGE_ROOT_PATH = __DIR__.'/fixtures/images';

    /** @test */
    public function not_existing_image_throws_exception()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('file [not-found-image.jpg] not found.');

        $image = new Image('not-found-image.jpg');
    }

    /** @test */
    public function can_extract_all_the_information()
    {
        $image = new Image('office/meetings_room/plant.jpg', self::IMAGE_ROOT_PATH);

        $this->assertEquals('office/meetings_room', $image->getPath());
        $this->assertEquals('plant.jpg', $image->getName());
        $this->assertEquals(1080, $image->getWidth());
        $this->assertEquals(810, $image->getHeight());
        $this->assertEquals('jpeg', $image->getType());
        $this->assertEquals(1.33, $image->getAspectRatio());
        $this->assertEquals(self::IMAGE_ROOT_PATH.'/office/meetings_room/plant.jpg', $image->getOriginalFullPath());
        $this->assertEquals('office/meetings_room/plant.jpg', $image->getOriginalName());
    }

    /** @test */
    public function can_determine_if_image_is_smaller_than()
    {
        $image = new Image('office/meetings_room/plant.jpg', self::IMAGE_ROOT_PATH);

        $this->assertTrue($image->isSmallerThan(1081, 812)); // Both greater
        $this->assertTrue($image->isSmallerThan(1080, 810)); // Both equal
        $this->assertFalse($image->isSmallerThan(1080, 700)); // Height smaller
        $this->assertFalse($image->isSmallerThan(1070, 809)); // Both smaller
    }

    /** @test */
    public function can_return_image_content()
    {
        $image = new Image('office/meetings_room/plant.jpg', self::IMAGE_ROOT_PATH);

        $this->assertNotNull($image->content());
    }
}
