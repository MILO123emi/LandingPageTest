<?php

namespace App\Support;

use App\Models\Media;
use App\Models\Site;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Simple photo upload: keeps the original under a server-chosen name. Resizing,
 * WebP variants and the focal point arrive with the image pipeline (F4).
 */
final class MediaStore
{
    private const EXTENSIONS = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public static function store(Site $site, UploadedFile $file, string $alt): Media
    {
        $path = (string) $file->getRealPath();
        $size = getimagesize($path);

        if ($size === false || ! isset(self::EXTENSIONS[$size['mime']])) {
            throw new RuntimeException('La foto no es una imagen JPG, PNG o WebP.');
        }

        $stored = "sites/{$site->id}/media/".substr((string) sha1_file($path), 0, 16).'/original.'.self::EXTENSIONS[$size['mime']];
        $disk = Storage::disk('public');

        if (! $disk->exists($stored)) {
            $disk->putFileAs(dirname($stored), $file, basename($stored));
        }

        $media = Media::query()->firstOrNew(['path_original' => $stored]);
        $media->fill([
            'disk' => 'public',
            'width' => $size[0],
            'height' => $size[1],
            'mime' => $size['mime'],
            'size_bytes' => (int) $file->getSize(),
        ]);

        if ((string) $media->getAttribute('alt') === '') {
            $media->alt = mb_substr($alt, 0, 200);
        }

        $media->save();

        return $media;
    }
}
