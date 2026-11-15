<?php

namespace Tests\Feature;

use App\Models\Feriante;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Recuperación de contraseña del feriante por SMS (código de 6 números vía Twilio).
 */
class RecuperarContrasenaSmsTest extends TestCase
{
    use RefreshDatabase;

    private Feriante $feriante;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategoriaSeeder::class);

        config([
            'services.twilio.sid' => 'AC123',
            'services.twilio.token' => 'secreto',
            'services.twilio.desde' => '+15005550006',
        ]);

        $this->feriante = Feriante::factory()->create([
            'email' => 'ana@gmail.com',
            'telefono' => '11 5555-5555',
        ]);
    }

    public function test_manda_el_codigo_por_sms_al_telefono_de_la_cuenta(): void
    {
        Http::fake();

        $this->post('/mi-cuenta/olvide-contrasena/sms', ['email' => 'ana@gmail.com'])
            ->assertRedirect('/mi-cuenta/restablecer-sms?email=ana%40gmail.com')
            ->assertSessionHas('success');

        Http::assertSent(fn ($pedido) => $pedido->url() === 'https://api.twilio.com/2010-04-01/Accounts/AC123/Messages.json'
            && $pedido['To'] === '+5491155555555'
            && str_contains($pedido['Body'], 'código'));
    }

    public function test_no_manda_nada_si_el_email_no_tiene_cuenta(): void
    {
        Http::fake();

        $this->post('/mi-cuenta/olvide-contrasena/sms', ['email' => 'nadie@gmail.com'])
            ->assertRedirect()
            ->assertSessionHas('success');

        Http::assertNothingSent();
    }

    public function test_con_el_codigo_correcto_cambia_la_contrasena(): void
    {
        Http::fake();
        $this->post('/mi-cuenta/olvide-contrasena/sms', ['email' => 'ana@gmail.com']);

        $this->post('/mi-cuenta/restablecer-sms', [
            'email' => 'ana@gmail.com',
            'codigo' => $this->codigoEnviado(),
            'password' => 'contrasena-nueva',
            'password_confirmation' => 'contrasena-nueva',
        ])->assertRedirect('/mi-cuenta/login');

        $this->assertTrue(Hash::check('contrasena-nueva', $this->feriante->fresh()->password));
    }

    public function test_un_codigo_equivocado_no_cambia_la_contrasena(): void
    {
        Http::fake();
        $this->post('/mi-cuenta/olvide-contrasena/sms', ['email' => 'ana@gmail.com']);
        $codigoEquivocado = $this->codigoEnviado() === '000000' ? '111111' : '000000';

        $this->post('/mi-cuenta/restablecer-sms', [
            'email' => 'ana@gmail.com',
            'codigo' => $codigoEquivocado,
            'password' => 'contrasena-nueva',
            'password_confirmation' => 'contrasena-nueva',
        ])->assertSessionHasErrors('codigo');

        $this->assertFalse(Hash::check('contrasena-nueva', $this->feriante->fresh()->password));
    }

    public function test_el_codigo_se_bloquea_tras_varios_intentos_fallidos(): void
    {
        Http::fake();
        $this->post('/mi-cuenta/olvide-contrasena/sms', ['email' => 'ana@gmail.com']);
        $codigo = $this->codigoEnviado();
        $codigoEquivocado = $codigo === '000000' ? '111111' : '000000';

        for ($intento = 0; $intento < 5; $intento++) {
            $this->post('/mi-cuenta/restablecer-sms', [
                'email' => 'ana@gmail.com',
                'codigo' => $codigoEquivocado,
                'password' => 'contrasena-nueva',
                'password_confirmation' => 'contrasena-nueva',
            ]);
        }

        // Aunque ahora acierte, el pedido ya fue anulado
        $this->post('/mi-cuenta/restablecer-sms', [
            'email' => 'ana@gmail.com',
            'codigo' => $codigo,
            'password' => 'contrasena-nueva',
            'password_confirmation' => 'contrasena-nueva',
        ])->assertSessionHasErrors('codigo');
    }

    public function test_avisa_si_twilio_no_puede_mandar_el_sms(): void
    {
        Http::fake(['api.twilio.com/*' => Http::response(['message' => 'error'], 400)]);

        $this->post('/mi-cuenta/olvide-contrasena/sms', ['email' => 'ana@gmail.com'])
            ->assertSessionHasErrors('email');

        $this->assertNull(Cache::get('recuperacion-sms:'.sha1('ana@gmail.com')));
    }

    /** Saca el código de 6 números del SMS que mandó el test. */
    private function codigoEnviado(): string
    {
        [$pedido] = Http::recorded()->last();

        preg_match('/es (\d{6})\./', $pedido->data()['Body'], $coincidencias);

        return $coincidencias[1];
    }
}
