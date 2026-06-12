<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    protected $table = 'reservas';

    protected $fillable = [
        'nombre_cliente',
        'telefono_cliente',
        'cantidad_personas',
        'fecha',
        'hora',
        'observaciones',
        'estado',
        'mesa_id',
    ];

    protected $casts = [
        'mesa_id'           => 'integer',
        'cantidad_personas' => 'integer',
        'fecha'             => 'date:Y-m-d',
    ];

    // Estados válidos según el ENUM de la BD
    public const ESTADOS = ['pendiente', 'confirmada', 'cancelada', 'finalizada'];

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }
}
