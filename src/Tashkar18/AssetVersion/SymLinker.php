<?php namespace Tashkar18\AssetVersion;

use File;

class SymLinker
{

    public function link($target, $link)
    {
        $base_dir = explode('/', $link);
        array_pop($base_dir);
        $base_dir = implode('/', $base_dir);

        if (! is_dir($base_dir)) {
            File::makeDirectory($base_dir, 0755, true);
        }

        symlink($target, $link);
    }

    public function unlink($link)
    {
        if (is_link($link)) {
            unlink($link);
        }
    }
}