<div class="ui-row ui-gutter-3">
    <div class="ui-col-md-8">
        <label for="nome" class="ui-label">Nome dell'insegnamento</label>
        <x-ui.input type="text" :invalid="$errors->has('nome')" id="nome" name="nome" maxlength="255"
            value="{{ \App\Support\FormValue::old('nome', $insegnamento->nome) }}" required />
        <x-ui.field-error for="nome" />
    </div>

    <div class="ui-col-md-4">
        <label for="anno_frequenza" class="ui-label">Anno di frequenza</label>
        <x-ui.select :invalid="$errors->has('anno_frequenza')" id="anno_frequenza" name="anno_frequenza" required>
            @foreach ([1, 2, 3] as $anno)
                <option value="{{ $anno }}" @selected((int) \App\Support\FormValue::old('anno_frequenza', $insegnamento->anno_frequenza) === $anno)>
                    {{ $anno }}° anno
                </option>
            @endforeach
        </x-ui.select>
        <x-ui.field-error for="anno_frequenza" />
    </div>

    <div class="w-full">
        <label for="corso_studio_id" class="ui-label">Corso di studio</label>
        <x-ui.select :invalid="$errors->has('corso_studio_id')" id="corso_studio_id" name="corso_studio_id" required>
            <option value="">— Seleziona un corso —</option>
            @foreach ($corsi as $corso)
                <option value="{{ $corso->id }}" @selected((int) \App\Support\FormValue::old('corso_studio_id', $insegnamento->corso_studio_id) === $corso->id)>
                    {{ $corso->nome }}
                </option>
            @endforeach
        </x-ui.select>
        <x-ui.field-error for="corso_studio_id" />
    </div>

    <div class="w-full">
        <label for="docenti" class="ui-label">Docenti titolari</label>
        <x-ui.select :invalid="$errors->has('docenti') || $errors->has('docenti.*')" id="docenti" name="docenti[]" aria-describedby="docenti-help" multiple size="5">
            @foreach ($docenti as $docente)
                <option value="{{ $docente->id }}" @selected(in_array($docente->id, (session()->hasOldInput() ? (array) old('docenti', []) : $docentiSelezionati)))>
                    {{ $docente->name }} ({{ $docente->email }})
                </option>
            @endforeach
        </x-ui.select>
        <div id="docenti-help" class="ui-field-help">Tieni premuto Ctrl (Cmd su Mac) per selezionare più docenti.</div>
        <x-ui.field-error for="docenti" />
    </div>
</div>

<div class="flex gap-2 mt-6">
    <x-ui.button variant="primary" type="submit">Salva</x-ui.button>
    <x-ui.button variant="outline-secondary" href="{{ route('insegnamenti.index') }}">Annulla</x-ui.button>
</div>
