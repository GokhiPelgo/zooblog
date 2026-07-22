<?php

namespace Tests\Feature;

use App\Mail\ContactMessageMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_mensaje_valido_se_guarda_y_notifica(): void
    {
        Mail::fake();

        $payload = [
            'name'    => 'Ana Torres',
            'email'   => 'ana@ejemplo.com',
            'message' => 'Hola, me gustaría más información sobre los tutoriales.',
        ];

        $response = $this->postJson('/api/contact', $payload);

        $response->assertSuccessful();
        $this->assertDatabaseHas('contact_messages', [
            'email' => 'ana@ejemplo.com',
            'name'  => 'Ana Torres',
        ]);
        Mail::assertSent(ContactMessageMail::class);
    }

    public function test_un_mensaje_invalido_es_rechazado(): void
    {
        $response = $this->postJson('/api/contact', [
            'name'    => 'A',            // muy corto
            'email'   => 'no-es-correo', // formato inválido
            'message' => 'corto',        // menos de 10 caracteres
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_el_listado_de_mensajes_exige_token(): void
    {
        config(['services.admin.token' => 'token-secreto']);

        // Sin token → 401
        $this->getJson('/api/contact-messages')->assertUnauthorized();

        // Con token correcto → 200
        $this->getJson('/api/contact-messages', [
            'X-Admin-Token' => 'token-secreto',
        ])->assertOk();
    }
}
