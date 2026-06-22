<?php

namespace Tests\Unit;

use App\Services\ProfilePhotoService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProfilePhotoServiceTest extends TestCase
{
    private ProfilePhotoService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ProfilePhotoService();
    }

    public function test_guarda_imagen_base64_en_disco_publico(): void
    {
        Storage::fake('public');

        $path = $this->service->storeIfBase64('data:image/png;base64,'.base64_encode('contenido-imagen'));

        $this->assertStringStartsWith('avatars/', $path);
        $this->assertStringEndsWith('.png', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_retorna_valor_original_si_no_es_base64(): void
    {
        $this->assertSame('/storage/avatar.png', $this->service->storeIfBase64('/storage/avatar.png'));
        $this->assertNull($this->service->storeIfBase64(null));
    }

    public function test_lanza_validacion_si_la_imagen_base64_es_invalida(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->storeIfBase64('data:image/png;base64,no-es-base64-valido');
    }

    public function test_public_value_convierte_paths_locales_a_url_publica(): void
    {
        Storage::fake('public');

        $this->assertSame('/storage/avatar.png', $this->service->publicValue('/storage/avatar.png'));
        $this->assertStringContainsString('/storage/avatars/avatar.png', $this->service->publicValue('avatars/avatar.png'));
    }
}
