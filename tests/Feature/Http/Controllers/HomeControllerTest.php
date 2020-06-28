<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;

/**
 * @covers \App\Http\Controllers\Controller\HomeController;
 */
class HomeControllerTest extends TestCase
{
    /** @test */
    public function it_displays_the_home_page()
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('home');
    }
}
