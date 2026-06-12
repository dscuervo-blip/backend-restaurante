<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mesa extends Model
{
    protected $table = 'mesas';

    protected $fillable = [
        'numero',
        'capacidad',
        'estado',
    ];

    protected $casts = [
        'capacidad' => 'integer',
    ];

    // Estados válidos según el ENUM de la BD
    public const ESTADOS = ['disponible', 'reservada', 'ocupada', 'fuera_servicio'];

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'mesa_id');
    }

    public function estaDisponible(): bool
    {
        return $this->estado === 'disponible';
    }
}
