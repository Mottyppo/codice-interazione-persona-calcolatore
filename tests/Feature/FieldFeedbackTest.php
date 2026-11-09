<?php

namespace Tests\Feature;

use App\Models\CorsoStudio;
use App\Models\Insegnamento;
use App\Models\Sessione;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldFeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('amministratore');
        $this->actingAs($admin);
    }

    private function assertFieldError(string $html, string $id, string $message): void
    {
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        $field = $document->getElementById($id);
        $this->assertNotNull($field, "Campo {$id} assente");
        $this->assertStringContainsString($message, html_entity_decode($html));
        $this->assertSame('true', $field->getAttribute('aria-invalid'));
        $descriptions = preg_split('/\s+/', trim($field->getAttribute('aria-describedby')));
        $connected = array_filter(array_map(fn ($description) => $document->getElementById($description), $descriptions));
        $this->assertTrue(collect($connected)->contains(fn ($description) => str_contains($description->textContent, $message)), "Errore non associato al campo {$id}");
    }

    public function test_errors_on_every_appello_field_are_connected_and_values_are_preserved(): void
    {
        $messages = [
            'insegnamento_id' => 'Seleziona un insegnamento valido.',
            'sessione_id' => 'Seleziona una sessione valida.',
            'data' => 'La data non è valida.',
            'ora_inizio' => 'L’ora di inizio non è valida.',
            'ora_fine' => 'L’ora di fine deve essere successiva all’ora di inizio.',
            'aula' => 'Il nome dell’aula è troppo lungo.',
            'note' => 'Le note sono troppo lunghe.',
        ];
        $response = $this->withSession([
            'errors' => ['default' => ['messages' => $messages, 'format' => ':message']],
            '_old_input' => ['data' => '2027-05-10', 'ora_inizio' => '11:00', 'ora_fine' => '09:00', 'aula' => 'Aula conservata', 'note' => 'Nota conservata'],
        ])->get(route('appelli.create'))->assertOk();

        foreach ($messages as $field => $message) {
            $this->assertFieldError($response->getContent(), $field, $message);
        }
        $response->assertSee('value="11:00"', false)->assertSee('value="09:00"', false)
            ->assertSee('Aula conservata')->assertSee('Nota conservata');
    }

    public function test_invalid_window_dates_are_rendered_next_to_the_fields_after_redirect(): void
    {
        $sessione = Sessione::create(['nome' => 'Estiva', 'data_inizio' => '2027-06-01', 'data_fine' => '2027-06-30']);
        $url = route('sessioni.show', $sessione);
        $this->from($url)->post(route('periodi.store', $sessione), [
            'data_inizio' => '2027-07-01', 'data_fine' => '2027-07-02',
        ])->assertRedirect($url);
        $errors = session('errors')['default']['messages'];
        $response = $this->get($url)->assertOk();
        foreach (['data_inizio', 'data_fine'] as $field) {
            $this->assertFieldError($response->getContent(), $field, $errors[$field][0]);
        }
        $response->assertSee('value="2027-07-01"', false)->assertSee('value="2027-07-02"', false);
    }

    public function test_invalid_docente_item_is_connected_to_the_multiple_select(): void
    {
        $message = 'Il docente selezionato non è valido.';
        $response = $this->withSession([
            'errors' => ['default' => ['messages' => ['docenti.0' => [$message]], 'format' => ':message']],
            '_old_input' => ['nome' => 'Reti conservato', 'docenti' => [9999]],
        ])->get(route('insegnamenti.create'))->assertOk();
        $this->assertFieldError($response->getContent(), 'docenti', $message);
        $response->assertSee('value="Reti conservato"', false);
    }

    public function test_a_failed_edit_preserves_an_explicitly_empty_docenti_selection(): void
    {
        $corso = CorsoStudio::create(['nome' => 'Informatica']);
        $insegnamento = Insegnamento::create(['nome' => 'Reti', 'anno_frequenza' => 1, 'corso_studio_id' => $corso->id]);
        $docente = User::factory()->create();
        $docente->assignRole('docente');
        $insegnamento->docenti()->attach($docente);
        $url = route('insegnamenti.edit', $insegnamento);

        $this->from($url)->put(route('insegnamenti.update', $insegnamento), [
            'nome' => '', 'anno_frequenza' => 1, 'corso_studio_id' => $corso->id,
        ])->assertRedirect($url);
        $response = $this->get($url)->assertOk();
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $selected = (new \DOMXPath($document))->query('//select[@id="docenti"]/option[@selected]');
        $this->assertCount(0, $selected);
        $this->assertTrue($insegnamento->fresh()->docenti->contains($docente));
    }

    public function test_catalogue_and_configuration_errors_are_connected(): void
    {
        foreach ([['corsi.create', ['nome']], ['insegnamenti.create', ['nome', 'anno_frequenza', 'corso_studio_id']], ['sessioni.create', ['nome', 'data_inizio', 'data_fine']], ['configurazione.edit', ['modalita_conflitto', 'giorni_preappello']], ['import.index', ['file']]] as [$route, $fields]) {
            $messages = array_fill_keys($fields, 'Correggi questo valore.');
            $response = $this->withSession(['errors' => ['default' => ['messages' => $messages, 'format' => ':message']]])->get(route($route))->assertOk();
            foreach ($fields as $field) {
                $this->assertFieldError($response->getContent(), $field === 'modalita_conflitto' ? 'modalita_blocco' : $field, $messages[$field]);
            }
        }
    }
}
