<?php

namespace App\Support;

use Spatie\MediaLibrary\Support\UrlGenerator\BaseUrlGenerator;

class MediaUrlGenerator extends BaseUrlGenerator
{
    public function getUrl(): string
    {
        $path = $this->getPath();

        // Build the URL using storage path
        // media/{id}/{filename} → asset('storage/{id}/{filename}')
        return asset('storage/' . $path);
    }

    public function getTemporaryUrl(\DateTimeInterface $expiration, array $options = []): string
    {
        return $this->getUrl();
    }

    public function getPath(): string
    {
        $directory = $this->media->id;
        
        return trim($directory . '/' . $this->media->file_name, '/');
    }

    public function getResponsiveImagesDirectoryUrl(): string
    {
        return asset('storage/' . $this->media->id);
    }
}
