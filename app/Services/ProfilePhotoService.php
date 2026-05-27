<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProfilePhotoService
{
    public function storeIfBase64(?string $foto): ?string
    {
        if (! $foto || ! str_starts_with($foto, 'data:image')) {
            return $foto;
        }

        if (! preg_match('/^data:image\/(png|jpe?g|webp);base64,(.+)$/', $foto, $matches)) {
            throw ValidationException::withMessages([
                'foto' => 'La foto debe ser una imagen valida.',
            ]);
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $contenido = base64_decode($matches[2], true);

        if ($contenido === false) {
            throw ValidationException::withMessages([
                'foto' => 'La foto debe ser una imagen valida.',
            ]);
        }

        $path = 'avatars/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($path, $contenido);

        return $path;
    }

    public function publicValue(?string $foto): ?string
    {
        if (! $foto || str_starts_with($foto, 'data:image')) {
            return $foto;
        }

        if (str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://') || str_starts_with($foto, '/storage/')) {
            return $foto;
        }

        return Storage::disk('public')->url($foto);
    }
}
