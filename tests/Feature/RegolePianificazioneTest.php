<?php

namespace Tests\Feature;

use App\Models\Appello;
use App\Models\CorsoStudio;
use App\Models\Insegnamento;
use App\Models\Sessione;
use App\Models\User;
use App\Services\MonitoraggioService;
use App\Support\CalendarioFestivita;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegolePianificazioneTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Insegnamento $insegnamento;

    private Sessione $sessione;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2027-09-27 12:00:00', 'UTC'));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create()->assignRole('amministratore');
        $this->actingAs($this->admin);
        $this->insegnamento = CorsoStudio::create(['nome' => 'Informatica'])->insegnamenti()
            ->create(['nome' => 'Programmazione', 'anno_frequenza' => 1]);
        $this->sessione = $this->sessione('Sessione A');
    }

    private function sessione(string $nome): Sessione
    {
        return Sessione::create(['nome' => $nome, 'data_inizio' => '2027-09-29', 'data_fine' => '2027-10-30']);
    }

    private function dati(array $modifiche = []): array
    {
        return array_replace([
            'insegnamento_id' => $this->insegnamento->id,
            'sessione_id' => $this->sessione->id,
            'data' => '2027-10-01',
            'ora_inizio' => '09:00',
            'ora_fine' => '11:00',
        ], $modifiche);
    }

    private function appello(array $modifiche = []): Appello
    {
        return Appello::create($this->dati($modifiche) + ['user_id' => $this->admin->id]);
    }

    public static function dateNonValide(): array
    {
        return [
            'sabato' => ['2027-10-02'],
            'domenica' => ['2027-10-03'],
            'festività nazionale' => ['2027-10-04'],
            'fuori sessione' => ['2027-11-02'],
        ];
    }

    #[DataProvider('dateNonValide')]
    public function test_anteprima_creazione_e_modifica_rifiutano_le_stesse_date(string $data): void
    {
        $dati = $this->dati(['data' => $data]);
        $this->post(route('appelli.store'), $dati)->assertSessionHasErrors('data');
        $this->assertDatabaseCount('appelli', 0);
        $appello = $this->appello();
        $this->put(route('appelli.update', $appello), $dati)->assertSessionHasErrors('data');
        $this->assertSame('2027-10-01', $appello->refresh()->data->toDateString());
        $this->getJson(route('appelli.verifica-conflitto', $dati + ['appello_id' => $appello->id]))
            ->assertUnprocessable()->assertJsonValidationErrors('data');
    }

    public function test_san_francesco_compare_nel_calendario_festivo_dal_2026(): void
    {
        $this->assertArrayNotHasKey('2025-10-04', CalendarioFestivita::festivitaDellAnno(2025));
        $this->assertArrayHasKey('2026-10-04', CalendarioFestivita::festivitaDellAnno(2026));
    }

    public function test_la_modifica_di_un_appello_chiuso_segnala_la_finestra_di_origine(): void
    {
        $docente = User::factory()->create()->assignRole('docente');
        $this->insegnamento->docenti()->attach($docente);
        $appello = $this->appello();
        $destinazione = $this->sessione('Sessione B');
        $destinazione->periodiInserimento()->create([
            'data_inizio' => '2027-09-26',
            'data_fine' => '2027-10-06',
        ]);
        $dati = $this->dati(['sessione_id' => $destinazione->id, 'data' => '2027-10-05']);

        $this->actingAs($docente)->getJson(route('appelli.verifica-conflitto', $dati + ['appello_id' => $appello->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sessione_id' => 'La finestra di inserimento della sessione di origine è chiusa.']);
        $this->put(route('appelli.update', $appello), $dati)
            ->assertSessionHasErrors(['sessione_id' => 'La finestra di inserimento della sessione di origine è chiusa.']);
        $this->assertSame('2027-10-01', $appello->refresh()->data->toDateString());
        $this->assertSame($this->sessione->id, $appello->sessione_id);

        $this->actingAs($this->admin)->put(route('appelli.update', $appello), $dati)
            ->assertRedirect(route('appelli.index'));
        $this->assertSame('2027-10-05', $appello->refresh()->data->toDateString());
        $this->assertSame($destinazione->id, $appello->sessione_id);
    }

    public function test_il_calendario_evidenzia_un_conflitto_fra_sessioni_senza_mostrare_appelli_esterni(): void
    {
        $altraSessione = $this->sessione('Sessione B');
        $visibile = $this->appello();
        $esterno = $this->appello(['sessione_id' => $altraSessione->id]);

        $this->get(route('calendario.index', ['sessione' => $this->sessione->id]))
            ->assertOk()
            ->assertViewHas('idConflitto', fn ($ids) => $ids->all() === [$visibile->id])
            ->assertViewHas('perData', fn ($giorni) => ! $giorni->flatten(1)->contains('id', $esterno->id));
    }

    public function test_la_scadenza_usa_tutte_le_finestre_continue_fino_alla_prima_interruzione(): void
    {
        $this->sessione->periodiInserimento()->createMany([
            ['data_inizio' => '2027-09-25', 'data_fine' => '2027-09-28'],
            ['data_inizio' => '2027-10-04', 'data_fine' => '2027-10-10'],
            ['data_inizio' => '2027-09-27', 'data_fine' => '2027-10-03'],
            ['data_inizio' => '2027-10-12', 'data_fine' => '2027-10-20'],
        ]);
        $monitoraggio = app(MonitoraggioService::class);
        $this->assertSame('aperta', $monitoraggio->statoFinestra($this->sessione));
        $this->travelTo(Carbon::parse('2027-10-03 12:00:00', 'UTC'));
        $this->assertSame('in_scadenza', $monitoraggio->statoFinestra($this->sessione));
    }

    public function test_orari_adiacenti_con_e_senza_secondi_non_sono_in_conflitto(): void
    {
        $this->appello(['ora_inizio' => '09:00:00', 'ora_fine' => '11:00:00']);
        $dati = $this->dati(['ora_inizio' => '11:00', 'ora_fine' => '12:00']);
        $this->getJson(route('appelli.verifica-conflitto', $dati))->assertOk()->assertJsonPath('conflitto', false);
        $this->post(route('appelli.store'), $dati)->assertRedirect(route('appelli.index'));
        $this->assertDatabaseCount('appelli', 2);
        $this->get(route('appelli.index'))->assertViewHas('idConflitto', fn ($ids) => $ids->isEmpty());
    }

    public function test_dopo_la_mezzanotte_italiana_la_data_precedente_e_passata(): void
    {
        $this->travelTo(Carbon::parse('2027-09-30 22:30:00', 'UTC'));
        $this->get(route('appelli.create'))->assertSee('min="2027-10-01"', false);
        $this->post(route('appelli.store'), $this->dati(['data' => '2027-09-30']))
            ->assertSessionHasErrors(['data' => 'Non è possibile fissare un appello in una data già passata.']);
        $this->post(route('appelli.store'), $this->dati())->assertRedirect(route('appelli.index'));
        $this->assertDatabaseCount('appelli', 1);
        $this->assertSame('UTC', config('app.timezone'));
    }
}
