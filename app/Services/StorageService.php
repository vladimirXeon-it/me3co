<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class StorageService
{
    /**
     * Determina si se debe usar S3 (producción o switch activo).
     */
    public static function useS3(): bool
    {
        return env('STORAGE_ON_S3', false);
    }

    /**
     * Retorna el disco de Laravel a utilizar.
     */
    public static function getDisk(): string
    {
        return self::useS3() ? 's3' : 'local';
    }

    /**
     * Limpia el path eliminando prefijos de "uploads" o "/uploads"
     * para que coincida con la raíz del disco de almacenamiento.
     */
    public static function cleanPath(string $path): string
    {
        $path = ltrim($path, '/\\');
        if (str_starts_with($path, 'uploads/')) {
            $path = substr($path, 8);
        } elseif (str_starts_with($path, 'uploads\\')) {
            $path = substr($path, 8);
        }
        return ltrim($path, '/\\');
    }

    /**
     * Guarda un archivo en el disco correspondiente.
     */
    public static function put(string $path, $content, string $visibility = 'public'): bool
    {
        $cleanPath = self::cleanPath($path);
        return Storage::disk(self::getDisk())->put($cleanPath, $content, $visibility);
    }

    /**
     * Guarda un archivo subido (UploadedFile) en el disco correspondiente.
     */
    public static function putFileAs(string $path, $file, string $name, string $visibility = 'public')
    {
        $path = self::cleanPath($path);

        if (!$file || !$file->isValid()) {
            throw new \Exception('Invalid uploaded file.');
        }

        $sourcePath = $file->getRealPath();

        if (!$sourcePath) {
            $sourcePath = $file->getPathname();
        }

        if (!$sourcePath || !file_exists($sourcePath)) {
            throw new \Exception('Uploaded temp file not found.');
        }

        $fullPath = self::cleanPath($path . '/' . $name);

        if (self::useS3()) {
            return Storage::disk(self::getDisk())->put(
                $fullPath,
                fopen($sourcePath, 'r')
            );
        }

        return Storage::disk(self::getDisk())->put(
            $fullPath,
            fopen($sourcePath, 'r'),
            $visibility
        );
    }

    /**
     * Obtiene la URL pública del archivo.
     */
    public static function url(string $path): string
    {
        if (empty($path)) {
            return '';
        }

        // Si ya es una URL absoluta, la devolvemos tal cual
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $cleanPath = self::cleanPath($path);

        if (self::useS3()) {
            return Storage::disk('s3')->url($cleanPath);
        }

        return asset('uploads/' . $cleanPath);
    }

    /**
     * Comprueba si el archivo existe.
     */
    public static function exists(string $path): bool
    {
        $cleanPath = self::cleanPath($path);
        return Storage::disk(self::getDisk())->exists($cleanPath);
    }

    /**
     * Elimina un archivo.
     */
    public static function delete(string $path): bool
    {
        $cleanPath = self::cleanPath($path);
        return Storage::disk(self::getDisk())->delete($cleanPath);
    }

    /**
     * Elimina un directorio completo.
     */
    public static function deleteDirectory(string $path): bool
    {
        $cleanPath = self::cleanPath($path);
        return Storage::disk(self::getDisk())->deleteDirectory($cleanPath);
    }

    /**
     * Limpia un directorio (borra todos los archivos dentro).
     */
    public static function cleanDirectory(string $path): bool
    {
        $cleanPath = self::cleanPath($path);
        $disk = Storage::disk(self::getDisk());

        $files = $disk->allFiles($cleanPath);
        foreach ($files as $file) {
            $disk->delete($file);
        }

        $directories = $disk->allDirectories($cleanPath);
        foreach ($directories as $dir) {
            $disk->deleteDirectory($dir);
        }

        return true;
    }

    /**
     * Lista los archivos en un directorio (no recursivo).
     */
    public static function files(string $path): array
    {
        $cleanPath = self::cleanPath($path);
        $disk = Storage::disk(self::getDisk());

        $files = $disk->files($cleanPath);

        // Devolvemos las rutas en formato "uploads/projects/project123/archivo.png"
        return array_map(function ($file) {
            return 'uploads/' . $file;
        }, $files);
    }
}
