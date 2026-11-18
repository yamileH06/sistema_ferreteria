<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Venta extends Model
{
    public const PORCENTAJE_DESCUENTO = 0.05;

    public const MONTO_MINIMO_DESCUENTO = 2_000_000;

    protected $fillable = [
        'codigo_venta',
        'cliente_id',
        'user_id',
        'subtotal',
        'descuento',
        'total',
        'moneda',
        'metodo_pago',
        'estado',
        'fecha_venta',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'descuento' => 'decimal:2',
            'total' => 'decimal:2',
            'fecha_venta' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Venta $venta): void {
            $venta->codigo_venta ??= 'VTA-TMP-'.Str::uuid();
            $venta->subtotal ??= $venta->total;
            $venta->descuento ??= '0.00';
            $venta->moneda ??= 'COP';
            $venta->fecha_venta ??= $venta->created_at ?? now();
        });

        static::created(function (Venta $venta): void {
            if (Str::startsWith($venta->codigo_venta, 'VTA-TMP-')) {
                $venta->codigo_venta = 'VTA-'.str_pad((string) $venta->id, 8, '0', STR_PAD_LEFT);
                $venta->saveQuietly();
            }
        });
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class);
    }
}
