<?php

namespace App\Support;

use Illuminate\Support\Carbon;

final class CalendarioEsami
{
    public static function oggi(): Carbon
    {
        // Usa il giorno civile italiano con la stessa rappresentazione delle
        // date di Eloquent, senza cambiare il fuso dei timestamp tecnici.
        return Carbon::parse(Carbon::now('Europe/Rome')->toDateString());
    }
}
