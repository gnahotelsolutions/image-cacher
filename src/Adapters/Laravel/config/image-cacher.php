<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Path
    |--------------------------------------------------------------------------
    |
    | The path where the manipulated images will be saved. Sub-directories
    | will be created following the original image path:
    |
    | Actual image is in:
    |   `office/meetings_room/views/river.jpg`
    | Cache image will be generated in:
    |   `cache/images/office/meetings_room/views/widthxheight/river.jpg`
    |
    */

    'cache_path' => 'cache/images',

    /*
    |--------------------------------------------------------------------------
    | Cache Root Path
    |--------------------------------------------------------------------------
    |
    | The root path where the cache is. Sub-directories will be created following
    | the original image path. It is used to save the images in the correct direction
    | but keeping it hidden from the image name.
    |
    */

    'cache_root_path' => 'public',

    /*
    |--------------------------------------------------------------------------
    | Images Root Path
    |--------------------------------------------------------------------------
    |
    | Use this option to specify what is the root path of all images. This is used
    | to get the images and cache them without this path in the final result.
    |
    | Actual image is in:
    |   `public/storage/office/meetings_room/views/river.jpg`
    | images.root is:
    |   `public/storage`
    | cache image will be created in:
    |   `cache/images/office/meetings_room/views/widthxheight/river.jpg`
    |
    */

    'images_root_path' => 'public/storage',

    /*
    |--------------------------------------------------------------------------
    | Output Images Format
    |--------------------------------------------------------------------------
    |
    | Use this variable to always transform the given images to a preferred format.
    | You can use this option to always transform your images to `webp` extension.
    |
    | Currently supported options are: webp
    |
    */

    'output_format' => null,

    /*
    |--------------------------------------------------------------------------
    | Output Image quality
    |--------------------------------------------------------------------------
    |
    | Use this variable to select the preferred output quality.
    |
    | Default option: 80
    |
    */
    'quality' => 80,

    /*
    |--------------------------------------------------------------------------
    | Php Image Manager
    |--------------------------------------------------------------------------
    |
    | Use this variable to select the preferred manager between `gd` and `image-magick`.
    |
    | Default option: gd
    |
    */
    'manager' => env('IMAGE_CACHER_MANAGER', 'gd'),
];
