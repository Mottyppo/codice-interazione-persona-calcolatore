<?php

namespace App\Services;

use App\Models\Appello;
use App\Models\Insegnamento;
use Illuminate\Support\Collection;

class ConflittoService
{
    /**
     * Coppie di appelli con sovrapposizione per gli studenti. Il confronto prima
     * e dopo una modifica della struttura distingue i nuovi conflitti da quelli
     * già accettati, senza introdurre vincoli sugli esami in modalità avviso.
     *
     * @return array<int, string>
     */
    public function coppieConflittoStudenti(): array
    {
        $appelli = Appello::with('insegnamento')->orderBy('id')->get();
        $coppie = [];
        foreach ($appelli as $indice => $a) {
            foreach ($appelli->slice($indice + 1) as $b) {
                if ((int) $a->insegnamento->corso_studio_id === (int) $b->insegnamento->corso_studio_id
                    && (int) $a->insegnamento->anno_frequenza === (int) $b->insegnamento->anno_frequenza
                    && $this->sonoInConflitto($a, $b)) {
                    $coppie[] = $a->id.':'.$b->id;
                }
            }
        }

        return $coppie;
    }

    /**
     * Trova gli appelli in conflitto con quello descritto dai parametri.
     *
     * Due appelli sono in conflitto quando cadono nella stessa data, le fasce
     * orarie si sovrappongono e si verifica almeno una di queste condizioni:
     *  - conflitto "studenti": stesso corso di studio e stesso anno di frequenza
     *    (gli studenti coinvolti sarebbero gli stessi);
     *  - conflitto "aula": la stessa aula risulterebbe occupata due volte.
     *
     * @param  string|null  $aula  Aula del nuovo appello (per il conflitto sull'aula).
     * @param  int|null  $escludiId  Id di un appello da escludere (utile in modifica).
     * @return Collection<int, Appello>
     */
    public function trovaConflitti(
        Insegnamento $insegnamento,
        string $data,
        string $oraInizio,
        string $oraFine,
        ?string $aula = null,
        ?int $escludiId = null
    ): Collection {
        $aula = $aula !== null ? trim($aula) : '';

        return Appello::with(['insegnamento', 'docente'])
            ->whereDate('data', $data)
            ->when($escludiId !== null, fn ($q) => $q->where('id', '!=', $escludiId))
            ->orderBy('ora_inizio')
            ->get()
            ->filter(fn (Appello $altro) => $this->fasceSovrapposte($oraInizio, $oraFine, $altro->ora_inizio, $altro->ora_fine)
                && (((int) $altro->insegnamento->anno_frequenza === (int) $insegnamento->anno_frequenza
                    && (int) $altro->insegnamento->corso_studio_id === (int) $insegnamento->corso_studio_id)
                    || ($aula !== '' && mb_strtolower(trim((string) $altro->aula)) === mb_strtolower($aula)))
            )->values();
    }

    /**
     * Dato un insieme di appelli già caricati, restituisce gli id di quelli in
     * conflitto con almeno un altro appello. Utile per evidenziare i conflitti
     * già presenti (es. salvati in modalità «avviso») nel calendario e
     * nell'elenco, senza interrogare di nuovo il database.
     *
     * Il confronto avviene contro `$universo`: se omesso coincide con `$appelli`
     * (il caso dell'admin, che vede tutto). Per il docente, che vede solo i
     * propri appelli, va passato l'insieme completo come universo, così da
     * rilevare anche i conflitti con appelli di altri docenti (es. stessa aula).
     *
     * Richiede che la relazione `insegnamento` sia già caricata su entrambi.
     *
     * @param  Collection<int, Appello>  $appelli  appelli da valutare
     * @param  Collection<int, Appello>|null  $universo  insieme contro cui confrontare
     * @return Collection<int, int> id degli appelli in conflitto
     */
    public function idInConflitto(Collection $appelli, ?Collection $universo = null): Collection
    {
        $universo = ($universo ?? $appelli)->values();
        $inConflitto = [];

        foreach ($appelli as $a) {
            foreach ($universo as $b) {
                if ($a->id === $b->id) {
                    continue;
                }
                if ($this->sonoInConflitto($a, $b)) {
                    $inConflitto[$a->id] = true;
                    break;
                }
            }
        }

        return collect(array_keys($inConflitto));
    }

    /**
     * Due appelli sono in conflitto se cadono nello stesso giorno, le fasce si
     * sovrappongono e condividono studenti (stesso corso e anno) oppure l'aula.
     */
    private function sonoInConflitto(Appello $a, Appello $b): bool
    {
        if (! $a->data->isSameDay($b->data)) {
            return false;
        }

        if (! $this->fasceSovrapposte($a->ora_inizio, $a->ora_fine, $b->ora_inizio, $b->ora_fine)) {
            return false;
        }

        $stessiStudenti = (int) $a->insegnamento->anno_frequenza === (int) $b->insegnamento->anno_frequenza
            && (int) $a->insegnamento->corso_studio_id === (int) $b->insegnamento->corso_studio_id;

        $aulaA = mb_strtolower(trim((string) $a->aula));
        $stessaAula = $aulaA !== '' && $aulaA === mb_strtolower(trim((string) $b->aula));

        return $stessiStudenti || $stessaAula;
    }

    private function fasceSovrapposte(string $inizioA, string $fineA, string $inizioB, string $fineB): bool
    {
        return $this->orarioConSecondi($inizioA) < $this->orarioConSecondi($fineB)
            && $this->orarioConSecondi($fineA) > $this->orarioConSecondi($inizioB);
    }

    private function orarioConSecondi(string $ora): string
    {
        // SQLite può conservare entrambi i formati. Le fasce adiacenti
        // rimangono distinte anche se solo uno degli orari include i secondi.
        return strlen($ora) === 5 ? $ora.':00' : $ora;
    }
}
