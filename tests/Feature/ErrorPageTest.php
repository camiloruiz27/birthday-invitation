<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.debug', false);

        Route::get('/__test/error/unhandled', function () {
            throw new RuntimeException('Test exception');
        });

        Route::get('/__test/error/{status}', function (int $status) {
            abort($status);
        })->whereNumber('status');
    }

    public function test_missing_pages_render_the_branded_404_screen(): void
    {
        $this->get('/__test/does-not-exist')
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error')
                ->where('status', 404)
            );
    }

    public function test_supported_http_errors_render_the_branded_error_screen(): void
    {
        foreach ([403, 419, 502] as $status) {
            $this->get("/__test/error/{$status}")
                ->assertStatus($status)
                ->assertInertia(fn (Assert $page) => $page
                    ->component('Error')
                    ->where('status', $status)
                );
        }
    }

    public function test_unhandled_exceptions_render_the_branded_500_screen_when_debug_is_disabled(): void
    {
        $this->get('/__test/error/unhandled')
            ->assertStatus(500)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error')
                ->where('status', 500)
            );
    }

    public function test_inertia_visits_receive_the_same_branded_error_screen(): void
    {
        $this->withHeader('X-Inertia', 'true')
            ->get('/__test/does-not-exist')
            ->assertNotFound()
            ->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Error')
            ->assertJsonPath('props.status', 404);
    }

    public function test_debug_mode_keeps_laravels_standard_exception_screen(): void
    {
        config()->set('app.debug', true);

        $this->get('/__test/error/unhandled')
            ->assertStatus(500)
            ->assertSee('Test exception');
    }
}
