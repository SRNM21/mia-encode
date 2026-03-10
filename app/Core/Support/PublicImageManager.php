<?php

namespace App\Core\Support;

use App\Core\Contracts\Support\PublicImageManager as PublicImageManagerContract;
use RuntimeException;

class PublicImageManager implements PublicImageManagerContract
{
    protected array $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp'
    ];

    protected int $maxFileSize = 10 * 1024 * 1024; // 10MB

    public function upload(array $file, string $directory = '', ?string $filename = null): string
    {
        $this->validateFile($file);

        $mimeType = $this->getMimeType($file['tmp_name']);

        if (!isset($this->allowedMimeTypes[$mimeType])) 
        {
            throw new RuntimeException('Unsupported image type.');
        }

        $extension = $this->allowedMimeTypes[$mimeType];

        $safeName = $filename
            ? $this->sanitizeFileName($filename)
            : bin2hex(random_bytes(16));

        $finalName = $safeName . '.' . $extension;

        $basePath = config('app.public_img');
        $targetDir = rtrim($basePath . $directory, '/') . '/';

        if (!is_dir($targetDir)) 
        {
            if (!mkdir($targetDir, 0755, true)) 
            {
                throw new RuntimeException('Failed to create image directory.');
            }
        }

        $targetPath = $targetDir . $finalName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) 
        {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        return $targetPath;
    }

    protected function validateFile(array $file): void
    {
        if (!isset($file['error']) || is_array($file['error'])) 
        {
            throw new RuntimeException('Invalid file parameters.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) 
        {
            throw new RuntimeException('File upload error code: ' . $file['error']);
        }

        if ($file['size'] > $this->maxFileSize) 
        {
            throw new RuntimeException('Image exceeds maximum allowed size.');
        }

        if (!is_uploaded_file($file['tmp_name'])) 
        {
            throw new RuntimeException('Possible file upload attack.');
        }
    }

    protected function getMimeType(string $filePath): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($filePath);
    }

    protected function sanitizeFileName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9-_]/', '_', strtolower($name));
    }

    public function delete(string $path): bool
    {
        $fullPath = config('app.public_img') . $path;

        if (file_exists($fullPath)) 
        {
            return unlink($fullPath);
        }

        return false;
    }
}