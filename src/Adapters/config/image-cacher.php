<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Path
    |--------------------------------------------------------------------------
    |
    | The path where the manipulated images where will be saved. Sub-directories
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

    'images_root_path' => 'public/storage'
];