<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProfileNavigationTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('users')]
    public function test_il_profilo_mantiene_la_navigazione_delle_pagine_applicative(string $email): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->actingAs(User::where('email', $email)->firstOrFail());

        $dashboard = $this->get(route('dashboard'))->assertOk();
        $profile = $this->get(route('profile.show'))->assertOk();
        $expected = $this->navigationLinks($dashboard->getContent());

        $this->assertContains(route('calendario.index'), array_column($expected, 'href'));
        $this->assertSame($expected, $this->navigationLinks($profile->getContent()));
    }

    public static function users(): array
    {
        return [
            'amministratore' => ['admin@esami.test'],
            'docente' => ['docente1@esami.test'],
        ];
    }

    private function navigationLinks(string $html): array
    {
        $document = new \DOMDocument;
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $links = (new \DOMXPath($document))->query('(//nav)[1]//a');
        $result = [];

        foreach ($links as $link) {
            $result[] = [
                'href' => $link->getAttribute('href'),
                'label' => preg_replace('/\s+/u', ' ', trim($link->textContent)),
            ];
        }

        return $result;
    }
}
