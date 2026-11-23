<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoInserimento extends Model
{
    protected $table = 'periodi_inserimento';

    protected $fillable = [
        'sessione_id',
        'data_inizio',
        'data_fine',
    ];

    protected $casts = [
        'data_inizio' => 'date',
        'data_fine' => 'date',
    ];
}
