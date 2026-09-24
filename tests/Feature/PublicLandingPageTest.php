<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicLandingPageTest extends TestCase
{
    public function test_public_landing_page_explains_the_programme_and_its_limits(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Find guidance for your next professional step.')
            ->assertSee('Community mentoring')
            ->assertSee('does not guarantee employment');
    }
}
