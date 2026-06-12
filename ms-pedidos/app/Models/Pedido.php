<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'numero_pedido',
        'mesa_id',
        'fecha',
        'total',
        'estado',
    ];

    protected $casts = [
        'mesa_id' => 'integer',
        'total'   => 'float',
        'fecha'   => 'datetime',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }
}
