<?php

namespace Tests\Feature;

use App\Jobs\PublishSiteJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PublishRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_invitado_no_puede_publicar(): void
    {
        // El middleware 'auth' redirige al invitado al login,
        // por lo que el build nunca se ejecuta.
        $this->post('/publish')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_modo_local_encola_el_build_sin_bloquear(): void
    {
        Bus::fake();
        config(['services.astro.publish_mode' => 'local']);

        $response = $this->actingAs(User::factory()->create())->postJson('/publish');

        $response->assertOk()->assertJson(['state' => 'building']);
        Bus::assertDispatched(PublishSiteJob::class);
    }

    public function test_el_endpoint_de_estado_devuelve_el_avance(): void
    {
        Cache::put(PublishSiteJob::STATUS_KEY, ['state' => 'done', 'message' => 'listo']);

        $response = $this->actingAs(User::factory()->create())->getJson('/publish/status');

        $response->assertOk()->assertJson(['state' => 'done', 'message' => 'listo']);
    }

    public function test_modo_hook_sin_url_avisa_que_falta_configurar(): void
    {
        config([
            'services.astro.publish_mode'      => 'hook',
            'services.prismic.deploy_hook_url' => null,
        ]);

        $response = $this->actingAs(User::factory()->create())->postJson('/publish');

        $response->assertOk()->assertJson(['state' => 'error']);
        $this->assertStringContainsString('Falta configurar', $response->json('message'));
    }
}
