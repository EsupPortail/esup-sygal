<?php

namespace Application;

class Util
{
    /**
     * Create correctly writable folder.
     * Check if folder exist and writable.
     * If not exist try to create it one writable.
     *
     * @param string $folder
     * @param int    $mode
     * @return bool
     *     true: folder has been created or exist and is writable.
     *     false: folder does not exist and cannot be created.
     */
    static public function createWritableFolder($folder, $mode = 0700)
    {
        if($folder !== '.' && $folder !== '/' ) {
            static::createWritableFolder(dirname($folder));
        }
        if (file_exists($folder)) {
            return is_writable($folder);
        }

        return mkdir($folder, $mode, true) && is_writable($folder);
    }
}