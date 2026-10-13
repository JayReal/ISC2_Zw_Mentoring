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
            ->assertSee('Practical guidance for your next professional step.')
            ->assertSee('Community mentoring')
            ->assertSee('does not guarantee employment');
    }

    public function test_full_programme_details_are_available_on_a_dedicated_page(): void
    {
        $this->get(route('programme'))
            ->assertOk()
            ->assertSee('How the programme works')
            ->assertSee('Entrepreneurship')
            ->assertSee('Programme safeguards');
    }

    public function test_landing_page_links_to_programme_details_and_configured_chapter_platform(): void
    {
        config(['services.cmms.url' => 'https://chapter.example.test/portal']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('programme'))
            ->assertSee('https://chapter.example.test/portal')
            ->assertSee('Chapter platform');
    }
}
