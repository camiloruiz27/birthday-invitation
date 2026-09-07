<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_redirects_to_the_game_master_login()
    {
        $response = $this->get('/');

        $response->assertRedirect('/gm/login');
    }
}
