<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo {{ $venta->codigo_venta }}</title>
    <style>
        @page {
            margin: 32px;
        }

        body {
            color: #1e293b;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        .header {
            border-bottom: 2px solid #f59e0b;
            margin-bottom: 20px;
            padding-bottom: 14px;
        }

        .brand {
            color: #0f172a;
            font-size: 22px;
            font-weight: bold;
            margin: 0;
        }

        .muted {
            color: #64748b;
        }

        .summary {
            margin-bottom: 20px;
            width: 100%;
        }

        .summary td {
            padding: 3px 0;
            vertical-align: top;
        }

        table.items {
            border-collapse: collapse;
            width: 100%;
        }

        .items th {
            background: #0f172a;
            color: #fff;
            font-size: 9px;
            padding: 9px 7px;
            text-align: left;
        }

        .items td {
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 7px;
        }

        .right {
            text-align: right;
        }

        .totals {
            margin-top: 16px;
            margin-left: auto;
            width: 260px;
        }

        .totals td {
            padding: 5px 0;
        }

        .grand-total {
            border-top: 2px solid #f59e0b;
            color: #0f172a;
            font-size: 14px;
            font-weight: bold;
        }

        .notice {
            border-top: 1px solid #e2e8f0;
            margin-top: 28px;
            padding-top: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header class="header">
        <p class="brand">Ferretería El Constructor</p>
        <p class="muted">Ipiales, Nariño, Colombia</p>
        <h1>Comprobante de venta</h1>
    </header>

    <table class="summary">
        <tr>
            <td><strong>Comprobante:</strong> {{ $venta->codigo_venta }}</td>
            <td class="right"><strong>Fecha:</strong> {{ $venta->fecha_venta?->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Cliente:</strong> {{ $venta->cliente?->nombre ?? 'Cliente General' }}</td>
            <td class="right"><strong>Atendido por:</strong> {{ $venta->user?->name ?? 'Personal de caja' }}</td>
        </tr>
        @if ($venta->cliente?->cedula_nit)
            <tr>
                <td><strong>Cédula / NIT:</strong> {{ $venta->cliente->cedula_nit }}</td>
                <td class="right"><strong>Teléfono:</strong> {{ $venta->cliente->telefono ?? '—' }}</td>
            </tr>
        @endif
        <tr>
            <td><strong>Método de pago:</strong> {{ $venta->metodo_pago }}</td>
            <td class="right"><strong>Moneda:</strong> {{ $venta->moneda }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Producto</th>
                <th>SKU</th>
                <th class="right">Cantidad</th>
                <th class="right">Precio unitario</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($venta->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->producto?->nombre ?? 'Producto no disponible' }}</td>
                    <td>{{ $detalle->producto?->codigo_sku ?? '—' }}</td>
                    <td class="right">{{ rtrim(rtrim(number_format((float) $detalle->cantidad, 3, ',', '.'), '0'), ',') }}</td>
                    <td class="right">$ {{ number_format((float) $detalle->precio_unitario, 2, ',', '.') }}</td>
                    <td class="right">$ {{ number_format((float) $detalle->subtotal, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted">No hay líneas de producto disponibles para esta venta.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="right">$ {{ number_format((float) $venta->subtotal, 2, ',', '.') }} {{ $venta->moneda }}</td>
        </tr>
        <tr>
            <td>Descuento</td>
            <td class="right">- $ {{ number_format((float) ($venta->descuento ?? 0), 2, ',', '.') }} {{ $venta->moneda }}</td>
        </tr>
        <tr class="grand-total">
            <td>Total a pagar</td>
            <td class="right">$ {{ number_format((float) $venta->total, 2, ',', '.') }} {{ $venta->moneda }}</td>
        </tr>
    </table>

    <footer class="notice muted">
        <p>Gracias por su compra.</p>
        <p>Este documento es un comprobante interno de venta y no reemplaza una factura electrónica validada por la DIAN.</p>
    </footer>
</body>
</html>
