<?php

namespace App\Http\Controllers;

use App\Models\Appello;
use App\Models\Configurazione;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ConfigurazioneController extends Controller
{
    /**
     * Mostra il form di configurazione (riga unica di impostazioni).
     */
    public function edit()
    {
        $configurazione = Configurazione::firstOrCreate([], ['modalita_conflitto' => 'blocco']);

        return view('configurazione.edit', compact('configurazione'));
    }

    /**
     * Aggiorna la modalità di gestione dei conflitti.
     */
    public function update(Request $request)
    {
        $dati = $request->validate([
            'modalita_conflitto' => 'required|string|in:blocco,warning',
            'giorni_preappello' => 'required|integer|min:0|max:60',
        ], [], [
            'modalita_conflitto' => 'modalità di gestione dei conflitti',
            'giorni_preappello' => 'giorni di anticipo per i preappelli',
        ]);

        $precedente = (int) (Configurazione::query()->value('giorni_preappello') ?? 14);
        if ((int) $dati['giorni_preappello'] < $precedente) {
            $incompatibile = Appello::with('sessione')->get()->contains(fn (Appello $appello) => $appello->data->lt($appello->sessione->data_inizio->copy()->subDays((int) $dati['giorni_preappello']))
            );
            if ($incompatibile) {
                throw ValidationException::withMessages([
                    'giorni_preappello' => 'Il margine indicato escluderebbe preappelli già pianificati. Modifica prima le relative date.',
                ]);
            }
        }

        $configurazione = Configurazione::firstOrCreate([], ['modalita_conflitto' => 'blocco']);
        $configurazione->update($dati);

        return redirect()->route('configurazione.edit')->with('success', 'Configurazione aggiornata.');
    }
}
