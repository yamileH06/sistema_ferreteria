<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reabastecimiento extends Model
{
    use HasFactory;

    protected $table = 'reabastecimientos';

    protected $fillable = [
        'proveedor_id',
        'producto_id',
        'user_id',
        'cantidad',
        'costo_unitario',
        'costo_total',
        'codigo_lote',
        'fecha_recepcion',
        'notas',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:3',
            'costo_unitario' => 'decimal:2',
            'costo_total' => 'decimal:2',
            'fecha_recepcion' => 'datetime',
        ];
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
