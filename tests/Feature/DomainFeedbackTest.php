<?php

namespace Tests\Feature;

use App\Models\Appello;
use App\Models\Configurazione;
use App\Models\CorsoStudio;
use App\Models\Insegnamento;
use App\Models\Sessione;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DomainFeedbackTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Insegnamento $insegnamento;

    private Sessione $sessione;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2030, 6, 3)->startOfDay());
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('amministratore');
        $this->insegnamento = Insegnamento::factory()->create();
        $this->sessione = Sessione::create(['nome' => 'Estiva', 'data_inizio' => '2030-06-10', 'data_fine' => '2030-07-20']);
        $this->actingAs($this->admin);
    }

    private function payload(string $area): array
    {
        return match ($area) {
            'corsi' => ['nome' => 'Nuovo corso'],
            'insegnamenti' => ['nome' => 'Nuovo insegnamento', 'anno_frequenza' => 1, 'corso_studio_id' => $this->insegnamento->corso_studio_id],
            'sessioni' => ['nome' => 'Autunnale', 'data_inizio' => '2030-09-02', 'data_fine' => '2030-09-30'],
            'periodi' => ['data_inizio' => '2030-06-03', 'data_fine' => '2030-07-01'],
            'appelli' => ['insegnamento_id' => $this->insegnamento->id, 'sessione_id' => $this->sessione->id, 'data' => '2030-06-10', 'ora_inizio' => '09:00', 'ora_fine' => '11:00'],
            'configurazione' => ['modalita_conflitto' => 'blocco', 'giorni_preappello' => 14],
        };
    }

    public static function invalidFields(): iterable
    {
        foreach (['corsi' => ['nome'], 'insegnamenti' => ['nome', 'anno_frequenza', 'corso_studio_id'], 'sessioni' => ['nome', 'data_inizio', 'data_fine'], 'periodi' => ['data_inizio', 'data_fine'], 'appelli' => ['insegnamento_id', 'sessione_id', 'data', 'ora_inizio', 'ora_fine'], 'configurazione' => ['modalita_conflitto', 'giorni_preappello']] as $area => $fields) {
            foreach ($fields as $field) {
                yield "$area $field obbligatorio" => [$area, $field, null];
                yield "$area $field scalare" => [$area, $field, ['non valido']];
            }
        }
        foreach ([['sessioni', 'data_fine', 'non-data'], ['sessioni', 'data_inizio', '2030-02-30'], ['appelli', 'data', '2030-02-30'], ['appelli', 'ora_inizio', '07:59'], ['appelli', 'ora_fine', '18:01'], ['appelli', 'note', ['testo']], ['appelli', 'aula', ['A1']], ['insegnamenti', 'docenti', '1'], ['insegnamenti', 'anno_frequenza', 4], ['configurazione', 'giorni_preappello', 61], ['configurazione', 'modalita_conflitto', 'altro']] as $case) {
            yield implode(' ', array_slice($case, 0, 2)).' invalido '.count($case) => $case;
        }
    }

    #[DataProvider('invalidFields')]
    public function test_errori_per_campo_non_scrivono_dati_parziali(string $area, string $field, mixed $value): void
    {
        $tables = ['corsi_studio', 'insegnamenti', 'sessioni', 'periodi_inserimento', 'appelli', 'configurazione', 'docente_insegnamento'];
        $before = collect($tables)->mapWithKeys(fn ($table) => [$table => \DB::table($table)->count()]);
        $payload = array_replace($this->payload($area), [$field => $value]);
        $formUrl = match ($area) {
            'configurazione' => route('configurazione.edit'),
            'periodi' => route('sessioni.show', $this->sessione),
            default => route($area.'.create'),
        };
        $this->from($formUrl);
        $response = $area === 'configurazione'
            ? $this->put(route('configurazione.update'), $payload)
            : $this->post(route($area.'.store', $area === 'periodi' ? $this->sessione : []), $payload);
        // Inspect the serialized bag without starting the next JSON session early.
        $messages = session('errors')['default']['messages'];
        $this->assertArrayHasKey($field, $messages);
        foreach ($before as $table => $count) {
            $this->assertDatabaseCount($table, $count);
        }
        $message = $messages[$field][0];
        $response->assertRedirect($formUrl);
        $this->get($formUrl)->assertOk()->assertSee($message);
    }

    public function test_non_si_assegnano_utenti_senza_ruolo_docente_ne_array_di_id(): void
    {
        foreach ([[$this->admin->id], [[$this->admin->id]], [99999], ['1.5']] as $docenti) {
            $this->post(route('insegnamenti.store'), $this->payload('insegnamenti') + ['docenti' => $docenti])
                ->assertSessionHasErrors('docenti.0');
        }
        $this->assertDatabaseCount('insegnamenti', 1);
        $this->assertDatabaseCount('docente_insegnamento', 0);
    }

    public function test_update_insegnamento_invalido_conserva_campi_e_titolarita(): void
    {
        $docente = User::factory()->create()->assignRole('docente');
        $this->insegnamento->docenti()->attach($docente);
        $original = $this->insegnamento->fresh()->getRawOriginal();
        $this->put(route('insegnamenti.update', $this->insegnamento), $this->payload('insegnamenti') + ['docenti' => [$this->admin->id]])
            ->assertSessionHasErrors('docenti.0');
        $this->assertSame($original, $this->insegnamento->fresh()->getRawOriginal());
        $this->assertSame([$docente->id], $this->insegnamento->docenti()->pluck('users.id')->all());
    }

    public function test_un_periodo_non_puo_essere_eliminato_tramite_un_altra_sessione(): void
    {
        $other = Sessione::factory()->create();
        $periodo = $other->periodiInserimento()->create(['data_inizio' => '2030-06-01', 'data_fine' => '2030-06-05']);
        $this->delete(route('periodi.destroy', [$this->sessione, $periodo]))->assertNotFound();
        $this->assertDatabaseHas('periodi_inserimento', ['id' => $periodo->id]);
    }

    public function test_modifiche_sessione_non_possono_escludere_appelli_e_finestre(): void
    {
        Appello::create($this->payload('appelli') + ['user_id' => $this->admin->id]);
        $this->sessione->periodiInserimento()->create(['data_inizio' => '2030-06-01', 'data_fine' => '2030-07-19']);
        foreach ([['data_inizio' => '2030-07-01'], ['data_fine' => '2030-07-15']] as $override) {
            $field = array_key_first($override);
            $this->put(route('sessioni.update', $this->sessione), array_replace(['nome' => 'Cambiata', 'data_inizio' => '2030-06-10', 'data_fine' => '2030-07-20'], $override))
                ->assertSessionHasErrors($field);
            $this->assertSame('Estiva', $this->sessione->fresh()->nome);
        }
    }

    public static function invalidFilters(): iterable
    {
        yield ['insegnamenti.index', 'q', ['testo']];
        yield ['insegnamenti.index', 'corso', ['1']];
        yield ['insegnamenti.index', 'corso', [['1']]];
        yield ['calendario.index', 'sessione', [['1']]];
        yield ['insegnamenti.index', 'anno', '4'];
        yield ['calendario.index', 'sessione', ['1']];
        yield ['appelli.create', 'sessione', ['1']];
        yield ['appelli.create', 'insegnamento', 'abc'];
        yield ['appelli.index', 'conflitti', ['1']];
    }

    #[DataProvider('invalidFilters')]
    public function test_filtri_non_validi_restituiscono_feedback_senza_500(string $route, string $field, mixed $value): void
    {
        $this->getJson(route($route, [$field => $value]))->assertUnprocessable()->assertJsonValidationErrors($field);
    }

    public static function invalidCsv(): iterable
    {
        yield 'colonne eccessive' => ["corso,insegnamento,anno_frequenza\nValido,Valido,1\nCorso,Insegnamento,2,extra\n"];
        yield 'nome lungo' => ["corso,insegnamento,anno_frequenza\nValido,Valido,1\n".str_repeat('a', 256).",Insegnamento,2\n"];
        yield 'intestazioni duplicate' => ["corso,insegnamento,anno_frequenza,corso\nValido,Valido,1,Altro\n"];
        yield 'docenti vuoti' => ["corso,insegnamento,anno_frequenza,docenti\nCorso,Insegnamento,1,|\n"];
    }

    #[DataProvider('invalidCsv')]
    public function test_csv_non_valido_non_importa_nulla(string $content): void
    {
        $before = CorsoStudio::count();
        $response = $this->post(route('import.store'), ['file' => UploadedFile::fake()->createWithContent('struttura.csv', $content)]);
        $response->assertRedirect();
        $this->assertTrue(session()->has('error') || session()->has('import_errori') || session('errors')?->has('file'));
        $this->assertDatabaseCount('corsi_studio', $before);
        $this->assertDatabaseCount('insegnamenti', 1);
    }

    public function test_ridurre_il_margine_non_deve_invalidare_preappelli_esistenti(): void
    {
        Configurazione::create(['modalita_conflitto' => 'blocco', 'giorni_preappello' => 14]);
        Appello::create(array_replace($this->payload('appelli'), ['data' => '2030-06-06', 'user_id' => $this->admin->id]));
        $this->put(route('configurazione.update'), ['modalita_conflitto' => 'warning', 'giorni_preappello' => 0])
            ->assertSessionHasErrors('giorni_preappello');
        $this->assertDatabaseHas('configurazione', ['giorni_preappello' => 14, 'modalita_conflitto' => 'blocco']);
    }

    public function test_cambiare_anno_insegnamento_non_crea_nuovi_conflitti_in_blocco(): void
    {
        $this->insegnamento->update(['anno_frequenza' => 1]);
        $altro = Insegnamento::factory()->create(['corso_studio_id' => $this->insegnamento->corso_studio_id, 'anno_frequenza' => 2]);
        Appello::create($this->payload('appelli') + ['user_id' => $this->admin->id]);
        Appello::create(array_replace($this->payload('appelli'), ['insegnamento_id' => $altro->id, 'user_id' => $this->admin->id]));
        $this->put(route('insegnamenti.update', $this->insegnamento), array_replace($this->payload('insegnamenti'), ['anno_frequenza' => 2]))
            ->assertSessionHasErrors('anno_frequenza');
        $this->assertSame(1, $this->insegnamento->fresh()->anno_frequenza);
        Configurazione::create(['modalita_conflitto' => 'warning', 'giorni_preappello' => 14]);
        $this->put(route('insegnamenti.update', $this->insegnamento), array_replace($this->payload('insegnamenti'), ['anno_frequenza' => 2]))
            ->assertSessionHasNoErrors();
        $this->assertSame(2, $this->insegnamento->fresh()->anno_frequenza);
    }

    public function test_import_che_cambia_anno_non_crea_conflitti_ne_scritture_parziali(): void
    {
        $this->insegnamento->update(['anno_frequenza' => 1]);
        $altro = Insegnamento::factory()->create(['corso_studio_id' => $this->insegnamento->corso_studio_id, 'anno_frequenza' => 2]);
        Appello::create($this->payload('appelli') + ['user_id' => $this->admin->id]);
        Appello::create(array_replace($this->payload('appelli'), ['insegnamento_id' => $altro->id, 'user_id' => $this->admin->id]));
        $content = "corso,insegnamento,anno_frequenza\nNuovo,Nuovo,1\n"
            .$this->insegnamento->corsoStudio->nome.','.$this->insegnamento->nome.",2\n";
        $before = CorsoStudio::count();
        $this->post(route('import.store'), ['file' => UploadedFile::fake()->createWithContent('struttura.csv', $content)])
            ->assertSessionHasErrors('file');
        $this->assertDatabaseCount('corsi_studio', $before);
        $this->assertSame(1, $this->insegnamento->fresh()->anno_frequenza);
    }

    public function test_ajax_non_puo_escludere_appelli_altrui_e_valida_orari(): void
    {
        $docente = User::factory()->create()->assignRole('docente');
        $this->insegnamento->docenti()->attach($docente);
        $altro = Insegnamento::factory()->create();
        $appello = Appello::create(array_replace($this->payload('appelli'), ['insegnamento_id' => $altro->id, 'user_id' => $this->admin->id]));
        $this->actingAs($docente)->getJson(route('appelli.verifica-conflitto', $this->payload('appelli') + ['appello_id' => $appello->id]))
            ->assertUnprocessable()->assertJsonValidationErrors('appello_id');
        $this->getJson(route('appelli.verifica-conflitto', array_replace($this->payload('appelli'), ['ora_inizio' => '07:00'])))
            ->assertUnprocessable()->assertJsonValidationErrors('ora_inizio');
    }

    public function test_conflitti_aula_normalizzano_maiuscole_unicode_e_spazi_come_elenco(): void
    {
        $altro = Insegnamento::factory()->create();
        Appello::create(array_replace($this->payload('appelli'), ['insegnamento_id' => $altro->id, 'aula' => "\tAULA È\t", 'user_id' => $this->admin->id]));
        $this->post(route('appelli.store'), $this->payload('appelli') + ['aula' => 'aula è'])
            ->assertSessionHasErrors('conflitto');
        $this->assertDatabaseCount('appelli', 1);
    }

    public function test_messaggio_orari_e_italiano_in_salvataggio_e_ajax(): void
    {
        $dati = array_replace($this->payload('appelli'), ['ora_fine' => '09:00']);
        $this->post(route('appelli.store'), $dati)->assertSessionHasErrors([
            'ora_fine' => 'L’ora di fine deve essere successiva all’ora di inizio.',
        ]);
        $this->getJson(route('appelli.verifica-conflitto', $dati))->assertUnprocessable()
            ->assertJsonPath('errors.ora_fine.0', 'L’ora di fine deve essere successiva all’ora di inizio.');
    }

    public function test_import_vuoto_o_senza_colonne_segnala_il_campo_file(): void
    {
        foreach (['', "nome,anno\nCorso,1\n"] as $content) {
            $this->post(route('import.store'), ['file' => UploadedFile::fake()->createWithContent('struttura.csv', $content)])
                ->assertSessionHasErrors('file');
        }
    }

    public function test_rinomina_sessione_compatibile_e_rinomina_insegnamento_con_conflitti_esistenti_restano_consentite(): void
    {
        Appello::create($this->payload('appelli') + ['user_id' => $this->admin->id]);
        Appello::create($this->payload('appelli') + ['user_id' => $this->admin->id]);
        $this->put(route('sessioni.update', $this->sessione), ['nome' => 'Nuovo nome', 'data_inizio' => '2030-06-10', 'data_fine' => '2030-07-20'])
            ->assertSessionHasNoErrors();
        $this->put(route('insegnamenti.update', $this->insegnamento), array_replace($this->payload('insegnamenti'), ['anno_frequenza' => $this->insegnamento->anno_frequenza]))
            ->assertSessionHasNoErrors();
        $this->assertSame('Nuovo nome', $this->sessione->fresh()->nome);
    }
}
