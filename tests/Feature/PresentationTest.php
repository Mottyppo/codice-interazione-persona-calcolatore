<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresentationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->actingAs(User::where('email', 'admin@esami.test')->firstOrFail());
    }

    public function test_il_form_non_segnala_errori_prima_della_validazione(): void
    {
        $response = $this->get(route('corsi.create'))->assertOk();
        $field = $this->nomeField($response->getContent());

        $this->assertNotContains('ui-invalid', explode(' ', $field->getAttribute('class')));
        $this->assertSame('false', $field->getAttribute('aria-invalid'));
    }

    public function test_il_form_segnala_il_campo_non_valido_dopo_il_rifiuto_del_server(): void
    {
        $response = $this->followingRedirects()
            ->from(route('corsi.create'))
            ->post(route('corsi.store'), ['nome' => ''])
            ->assertOk();
        $field = $this->nomeField($response->getContent());

        $this->assertContains('ui-invalid', explode(' ', $field->getAttribute('class')));
        $this->assertSame('true', $field->getAttribute('aria-invalid'));
        $this->assertDatabaseCount('corsi_studio', 0);
    }

    private function nomeField(string $html): \DOMElement
    {
        $document = new \DOMDocument;
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);

        return (new \DOMXPath($document))->query('//input[@id="nome"]')->item(0);
    }
}
