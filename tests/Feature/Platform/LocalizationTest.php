<?php

namespace Tests\Feature\Platform;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The product is in Spanish end to end, but half of what a user reads on a
 * failed form comes from the framework, not from our own strings. The locale
 * was left on the skeleton default for a long time, so people were getting
 * "The email field is required." under a Spanish label.
 *
 * A partial translation does not fail visibly — it falls through to
 * fallback_locale and reads as English. These tests are what makes that
 * regression loud.
 */
class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_app_runs_in_spanish(): void
    {
        $this->assertSame('es', config('app.locale'));
    }

    public function test_validation_errors_come_back_in_spanish(): void
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => '',
            'email' => 'no-es-un-correo',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'something-else',
        ]);

        $errors = $response->assertSessionHasErrors(['name', 'email', 'password'])
            ->getSession()
            ->get('errors')
            ->getBag('default');

        // The friendly field name, not the column name: "el correo", never
        // "el email" — see the attributes map in lang/es/validation.php.
        $this->assertSame('El nombre es obligatorio.', $errors->first('name'));
        $this->assertSame('El correo debe ser una dirección de correo válida.', $errors->first('email'));
        $this->assertSame('La confirmación de la contraseña no coincide.', $errors->first('password'));

        foreach (['name', 'email', 'password'] as $field) {
            $this->assertStringNotContainsString('field', $errors->first($field));
            $this->assertStringNotContainsString('must be', $errors->first($field));
        }
    }

    public function test_the_password_broker_answers_in_spanish(): void
    {
        $this->assertSame(
            'Tu contraseña quedó cambiada. Ya puedes ingresar con ella.',
            __('passwords.reset'),
        );
        $this->assertSame('Ese correo y esa contraseña no coinciden.', __('auth.failed'));
    }
}
