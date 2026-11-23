<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configurazione extends Model
{
    protected $table = 'configurazione';

    protected $fillable = [
        'modalita_conflitto',
        'giorni_preappello',
    ];

    protected $casts = [
        'giorni_preappello' => 'integer',
    ];
}
