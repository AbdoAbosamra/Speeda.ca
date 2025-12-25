<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleControllerTest extends TestCase
{
    public function test_post_locale_updates_session_and_redirects(): void
    {
        $response = $this->post(route('locale.update'), [
            'locale' => 'fr',
            'redirect_to' => route('home'),
        ]);

        $response->assertRedirect(route('home'));
        $this->assertSame('fr', session('locale'));
    }

    public function test_get_locale_route_switches_language(): void
    {
        $response = $this->get(route('locale.switch', [
            'locale' => 'ar',
            'redirect' => route('categories'),
        ]));

        $response->assertRedirect(route('categories'));
        $this->assertSame('ar', session('locale'));
    }

    public function test_invalid_locale_returns_error(): void
    {
        $response = $this->post(route('locale.update'), [
            'locale' => 'de',
        ]);

        $response->assertStatus(400);
    }
}

