<?php

namespace GNAHotelSolutions\ImageCacher\Tests;

trait CacheFolder
{
    public function deleteFolder(string $path)
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

    public function createFolder(string $folder)
    {
        @mkdir($folder);
    }
}