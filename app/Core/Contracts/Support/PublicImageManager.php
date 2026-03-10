<?php

namespace App\Core\Contracts\Support;

use RuntimeException;

interface PublicImageManager
{
    /**
     * Upload an image file.
     *
     * @param array $file file input name
     * @param string $directory Target subdirectory inside resources/images/
     * @param string|null $filename Custom filename (without extension)
     *
     * @return string Relative public path to the uploaded image
     *
     * @throws RuntimeException
     */
    public function upload(array $file, string $directory = '', ?string $filename = null): string;

    /**
     * Delete the image.
     *
     * @param string $path Relative path to the file from resources/images/
     *
     * @return bool True if the file was deleted, false otherwise
     */
    public function delete(string $path): bool;
}