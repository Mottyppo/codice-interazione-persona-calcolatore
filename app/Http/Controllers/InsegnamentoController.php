<?php

namespace App\Http\Controllers;

use App\Models\Configurazione;
use App\Models\CorsoStudio;
use App\Models\Insegnamento;
use App\Models\User;
use App\Services\ConflittoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InsegnamentoController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'corso' => ['nullable', 'integer', 'exists:corsi_studio,id'],
            'anno' => ['nullable', 'integer', 'between:1,3'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $insegnamenti = Insegnamento::with(['corsoStudio', 'docenti'])
            ->withCount('appelli')
            ->when($request->filled('corso'), fn ($q) => $q->where('corso_studio_id', (int) $request->query('corso')))
            ->when($request->filled('anno'), fn ($q) => $q->where('anno_frequenza', (int) $request->query('anno')))
            ->when($request->filled('q'), fn ($q) => $q->where('nome', 'like', '%'.$request->query('q').'%'))
            ->orderBy('nome')
            ->get();

        return view('insegnamenti.index', [
            'insegnamenti' => $insegnamenti,
            'corsi' => CorsoStudio::orderBy('nome')->get(),
            'filtri' => [
                'corso' => $request->query('corso'),
                'anno' => $request->query('anno'),
                'q' => $request->query('q'),
            ],
        ]);
    }

    public function create()
    {
        return view('insegnamenti.create', [
            'insegnamento' => new Insegnamento,
            'corsi' => CorsoStudio::orderBy('nome')->get(),
            'docenti' => $this->docenti(),
            'docentiSelezionati' => [],
        ]);
    }

    public function store(Request $request)
    {
        $dati = $this->validateRequest($request);

        DB::transaction(function () use ($dati) {
            $insegnamento = Insegnamento::create($dati);
            $insegnamento->docenti()->sync($dati['docenti'] ?? []);
        });

        return redirect()->route('insegnamenti.index')->with('success', 'Insegnamento creato.');
    }

    public function edit(Insegnamento $insegnamento)
    {
        return view('insegnamenti.edit', [
            'insegnamento' => $insegnamento,
            'corsi' => CorsoStudio::orderBy('nome')->get(),
            'docenti' => $this->docenti(),
            'docentiSelezionati' => $insegnamento->docenti->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Insegnamento $insegnamento)
    {
        $dati = $this->validateRequest($request);

        DB::transaction(function () use ($dati, $insegnamento) {
            $conflitti = app(ConflittoService::class);
            $verifica = (Configurazione::query()->value('modalita_conflitto') ?? 'blocco') === 'blocco'
                && ((int) $dati['anno_frequenza'] !== $insegnamento->anno_frequenza
                    || (int) $dati['corso_studio_id'] !== (int) $insegnamento->corso_studio_id);
            $precedenti = $verifica ? $conflitti->coppieConflittoStudenti() : [];
            $campo = (int) $dati['corso_studio_id'] !== (int) $insegnamento->corso_studio_id ? 'corso_studio_id' : 'anno_frequenza';
            $insegnamento->update($dati);
            if ($verifica && array_diff($conflitti->coppieConflittoStudenti(), $precedenti) !== []) {
                throw ValidationException::withMessages([
                    $campo => 'La modifica creerebbe sovrapposizioni tra appelli dello stesso corso e anno. Modifica prima gli appelli interessati.',
                ]);
            }
            $insegnamento->docenti()->sync($dati['docenti'] ?? []);
        });

        return redirect()->route('insegnamenti.index')->with('success', 'Insegnamento aggiornato.');
    }

    public function destroy(Insegnamento $insegnamento)
    {
        $insegnamento->delete();

        return redirect()->route('insegnamenti.index')->with('success', 'Insegnamento eliminato.');
    }

    /**
     * Elenco dei docenti selezionabili come titolari.
     */
    private function docenti()
    {
        return User::role('docente')->orderBy('name')->get();
    }

    /**
     * Regole di validazione condivise tra creazione e modifica.
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'nome' => 'required|string|max:255',
            'anno_frequenza' => 'required|integer|min:1|max:3',
            'corso_studio_id' => 'bail|required|integer|exists:corsi_studio,id',
            'docenti' => 'array',
            'docenti.*' => ['bail', 'required', 'integer', Rule::in(User::role('docente')->pluck('id')->all())],
        ], ['docenti.*.in' => 'Seleziona un utente con ruolo docente.'], [
            'docenti' => 'docenti',
            'docenti.*' => 'docente',
            'corso_studio_id' => 'corso di studio',
            'anno_frequenza' => 'anno di frequenza',
        ]);
    }
}
