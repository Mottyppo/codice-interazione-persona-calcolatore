<?php

namespace App\Http\Controllers;

use App\Models\Configurazione;
use App\Models\Sessione;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class SessioneController extends Controller
{
    public function index()
    {
        $sessioni = Sessione::withCount(['periodiInserimento', 'appelli'])
            ->orderByDesc('data_inizio')
            ->get();

        return view('sessioni.index', compact('sessioni'));
    }

    public function create()
    {
        return view('sessioni.create', ['sessione' => new Sessione]);
    }

    public function store(Request $request)
    {
        $dati = $this->validateRequest($request);

        Sessione::create($dati);

        return redirect()->route('sessioni.index')->with('success', 'Sessione creata.');
    }

    public function show(Sessione $sessione)
    {
        $sessione->load('periodiInserimento');

        return view('sessioni.show', compact('sessione'));
    }

    public function edit(Sessione $sessione)
    {
        return view('sessioni.edit', compact('sessione'));
    }

    public function update(Request $request, Sessione $sessione)
    {
        $dati = $this->validateRequest($request, $sessione);

        $sessione->update($dati);

        return redirect()->route('sessioni.index')->with('success', 'Sessione aggiornata.');
    }

    public function destroy(Sessione $sessione)
    {
        $sessione->delete();

        return redirect()->route('sessioni.index')->with('success', 'Sessione eliminata.');
    }

    /**
     * Giorni minimi di durata di una sessione (una settimana, estremi inclusi).
     */
    private const DURATA_MINIMA_GIORNI = 7;

    /**
     * Regole di validazione condivise tra creazione e modifica.
     */
    private function validateRequest(Request $request, ?Sessione $sessione = null): array
    {
        // Prima si controlla il formato di entrambi gli estremi: i confronti
        // successivi non devono mai interpretare array o date malformate.
        $dati = $request->validate([
            'nome' => 'required|string|max:255',
            'data_inizio' => ['bail', 'required', 'date_format:Y-m-d', 'after:today'],
            'data_fine' => ['bail', 'required', 'date_format:Y-m-d'],
        ], [
            'data_inizio.after' => 'La sessione deve iniziare da domani in poi.',
        ], [
            'data_inizio' => 'data di inizio',
            'data_fine' => 'data di fine',
        ]);

        $inizio = Carbon::parse($dati['data_inizio']);
        $fine = Carbon::parse($dati['data_fine']);
        $minima = $inizio->copy()->addDays(self::DURATA_MINIMA_GIORNI - 1);
        $errori = [];
        if ($fine->lt($minima)) {
            $errori['data_fine'] = 'La sessione deve durare almeno una settimana: la data di fine non può precedere il '
                .$minima->format('d/m/Y').'.';
        }

        if ($sessione !== null) {
            $margine = (int) (Configurazione::query()->value('giorni_preappello') ?? 14);
            if ($sessione->appelli()->whereDate('data', '<', $inizio->copy()->subDays($margine))->exists()) {
                $errori['data_inizio'] = 'La nuova data di inizio escluderebbe appelli già pianificati, anche considerando i preappelli.';
            }
            if ($sessione->appelli()->whereDate('data', '>', $fine)->exists()) {
                $errori['data_fine'] = 'La nuova data di fine escluderebbe appelli già pianificati.';
            }
            if ($sessione->periodiInserimento()->whereDate('data_fine', '>', $fine)->exists()) {
                $errori['data_fine'] = 'La nuova data di fine precederebbe la chiusura di una finestra di inserimento esistente.';
            }
        }

        if ($errori !== []) {
            throw ValidationException::withMessages($errori);
        }

        return $dati;
    }
}
