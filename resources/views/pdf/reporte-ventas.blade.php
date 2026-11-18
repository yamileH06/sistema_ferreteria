<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas - Ferretería El Constructor</title>
    <style>
        @page {
            margin: 28px 30px 30px 30px;
        }

        body {
            color: #0f172a;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            background-color: #ffffff;
        }

        /* Header */
        .header {
            border-bottom: 2.5px solid #d97706;
            margin-bottom: 16px;
            padding-bottom: 12px;
        }

        .header table {
            width: 100%;
        }

        .brand-title {
            color: #0f172a;
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 2px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .brand-subtitle {
            color: #d97706;
            font-size: 11px;
            font-weight: bold;
            margin: 0 0 2px 0;
        }

        .brand-location {
            color: #64748b;
            font-size: 9px;
            margin: 0;
        }

        .report-badge {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            padding: 6px 12px;
            text-align: right;
            border-radius: 4px;
            display: inline-block;
        }

        .report-meta {
            color: #475569;
            font-size: 9px;
            margin-top: 4px;
            text-align: right;
        }

        /* Section Titles */
        .section-title {
            color: #0f172a;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin: 14px 0 6px 0;
            padding-bottom: 3px;
            border-bottom: 1px solid #cbd5e1;
        }

        /* Executive Summary Grid / Table */
        .kpi-table {
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-left: -6px;
            margin-right: -6px;
            margin-bottom: 14px;
            width: calc(100% + 12px);
        }

        .kpi-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #d97706;
            border-radius: 4px;
            padding: 8px 10px;
            vertical-align: top;
            width: 25%;
        }

        .kpi-label {
            color: #64748b;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .kpi-value {
            color: #0f172a;
            font-size: 13px;
            font-weight: bold;
        }

        .kpi-sub {
            color: #94a3b8;
            font-size: 8px;
            margin-top: 2px;
        }

        /* Standard Data Tables */
        table.data-table {
            border-collapse: collapse;
            margin-bottom: 14px;
            width: 100%;
        }

        table.data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            padding: 6px 8px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        table.data-table td {
            border-bottom: 1px solid #e2e8f0;
            padding: 5px 8px;
            font-size: 9px;
            vertical-align: middle;
        }

        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        table.data-table tr.total-row td {
            background-color: #f1f5f9;
            border-top: 2px solid #cbd5e1;
            border-bottom: 2px solid #0f172a;
            font-weight: bold;
            color: #0f172a;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge-method {
            background-color: #e2e8f0;
            color: #334155;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-stock-low {
            color: #b45309;
            font-weight: bold;
        }

        .badge-stock-out {
            color: #e11d48;
            font-weight: bold;
        }

        /* Signature & Footer */
        .signatures {
            margin-top: 28px;
            width: 100%;
        }

        .sig-box {
            border-top: 1px solid #94a3b8;
            padding-top: 6px;
            text-align: center;
            width: 40%;
        }

        .footer {
            border-top: 1px solid #e2e8f0;
            color: #94a3b8;
            font-size: 8px;
            margin-top: 20px;
            padding-top: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Encabezado Oficial -->
    <header class="header">
        <table>
            <tr>
                <td style="vertical-align: top; width: 55%;">
                    <p class="brand-title">Ferretería El Constructor</p>
                    <p class="brand-subtitle">ControlPro · Sistema de Gestión Comercial</p>
                    <p class="brand-location">NIT: 900.284.195-3 · Ipiales, Nariño, Colombia</p>
                </td>
                <td style="text-align: right; vertical-align: top; width: 45%;">
                    <div class="report-badge">INFORME DE RENDIMIENTO DE VENTAS</div>
                    <div class="report-meta">
                        <strong>Periodo:</strong> {{ $periodo_label }}<br>
                        <strong>Emisión:</strong> {{ $fecha_emision }}<br>
                        <strong>Generado por:</strong> {{ $usuario }}
                    </div>
                </td>
            </tr>
        </table>
    </header>

    <!-- Resumen Ejecutivo de 4 Indicadores Clave -->
    <div class="section-title">1. Resumen Ejecutivo del Periodo</div>
    <table class="kpi-table">
        <tr>
            <td class="kpi-box">
                <div class="kpi-label">Ingresos Netos</div>
                <div class="kpi-value">$ {{ number_format($metricas['total_ingresos'], 2, ',', '.') }}</div>
                <div class="kpi-sub">
                    @if($metricas['total_descuentos'] > 0)
                        Desc: $ {{ number_format($metricas['total_descuentos'], 2, ',', '.') }}
                    @else
                        Total facturado COP
                    @endif
                </div>
            </td>
            <td class="kpi-box">
                <div class="kpi-label">Transacciones</div>
                <div class="kpi-value">{{ number_format($metricas['total_transacciones']) }}</div>
                <div class="kpi-sub">Ventas completadas</div>
            </td>
            <td class="kpi-box">
                <div class="kpi-label">Unidades Vendidas</div>
                <div class="kpi-value">
                    {{ (float)$metricas['total_productos_vendidos'] == (int)$metricas['total_productos_vendidos']
                        ? number_format($metricas['total_productos_vendidos'], 0, ',', '.')
                        : number_format($metricas['total_productos_vendidos'], 3, ',', '.') }}
                </div>
                <div class="kpi-sub">Volumen de mercancía</div>
            </td>
            <td class="kpi-box">
                <div class="kpi-label">Ticket Promedio</div>
                <div class="kpi-value">$ {{ number_format($metricas['ticket_promedio'], 2, ',', '.') }}</div>
                <div class="kpi-sub">Promedio por venta</div>
            </td>
        </tr>
    </table>

    <!-- Desglose de Métodos de Pago -->
    <div class="section-title">2. Desglose por Método de Pago</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%;">Método de Pago</th>
                <th class="text-center" style="width: 20%;">Operaciones</th>
                <th class="text-right" style="width: 25%;">Total Recaudado (COP)</th>
                <th class="text-right" style="width: 20%;">Participación (%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($metodos_pago as $m)
                <tr>
                    <td>
                        <span class="badge-method">{{ $m['metodo'] }}</span>
                    </td>
                    <td class="text-center">{{ number_format($m['transacciones']) }}</td>
                    <td class="text-right font-bold">$ {{ number_format($m['monto'], 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($m['porcentaje'], 1, ',', '.') }} %</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="color: #64748b; padding: 12px;">
                        No se registraron cobros en el periodo seleccionado.
                    </td>
                </tr>
            @endforelse
            @if(count($metodos_pago) > 0)
                <tr class="total-row">
                    <td>TOTAL GENERAL</td>
                    <td class="text-center">{{ number_format($metricas['total_transacciones']) }}</td>
                    <td class="text-right">$ {{ number_format($metricas['total_ingresos'], 2, ',', '.') }}</td>
                    <td class="text-right">100.0 %</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Detalle de Ventas y Rotación de Productos -->
    <div class="section-title">3. Detalle de Ventas y Rotación de Productos</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">#</th>
                <th style="width: 14%;">Código SKU</th>
                <th style="width: 33%;">Descripción del Producto</th>
                <th style="width: 18%;">Categoría</th>
                <th class="text-right" style="width: 12%;">Cant. Vendida</th>
                <th class="text-right" style="width: 18%;">Subtotal Recaudado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productos_vendidos as $index => $prod)
                <tr>
                    <td class="text-center" style="color: #64748b;">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-size: 8.5px;">{{ $prod['codigo_sku'] ?? 'S/C' }}</td>
                    <td>
                        <strong>{{ $prod['nombre'] }}</strong>
                        @if((float)$prod['stock'] <= 0)
                            <span class="badge-stock-out" style="font-size: 7.5px;"> [Agotado]</span>
                        @elseif((float)$prod['stock'] <= 5)
                            <span class="badge-stock-low" style="font-size: 7.5px;"> [Stock: {{ number_format($prod['stock'], 0) }}]</span>
                        @endif
                    </td>
                    <td style="color: #475569;">{{ $prod['categoria'] }}</td>
                    <td class="text-right">
                        {{ (float)$prod['total_vendido'] == (int)$prod['total_vendido']
                            ? number_format($prod['total_vendido'], 0, ',', '.')
                            : number_format($prod['total_vendido'], 3, ',', '.') }}
                    </td>
                    <td class="text-right font-bold">$ {{ number_format($prod['total_recaudado'], 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="color: #64748b; padding: 14px;">
                        No se registraron ventas de productos en el periodo seleccionado.
                    </td>
                </tr>
            @endforelse
            @if(count($productos_vendidos) > 0)
                <tr class="total-row">
                    <td colspan="4" class="text-right">TOTALES DEL PERIODO:</td>
                    <td class="text-right">
                        {{ (float)$metricas['total_productos_vendidos'] == (int)$metricas['total_productos_vendidos']
                            ? number_format($metricas['total_productos_vendidos'], 0, ',', '.')
                            : number_format($metricas['total_productos_vendidos'], 3, ',', '.') }}
                    </td>
                    <td class="text-right">$ {{ number_format($metricas['total_ingresos'], 2, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Bloque de Firmas y Validación -->
    <table class="signatures">
        <tr>
            <td style="width: 10%;"></td>
            <td class="sig-box">
                <strong>{{ $usuario }}</strong><br>
                <span style="color: #64748b; font-size: 8.5px;">Responsable de Emisión / Caja</span><br>
                <span style="color: #94a3b8; font-size: 8px;">C.C. _______________________________</span>
            </td>
            <td style="width: 20%;"></td>
            <td class="sig-box">
                <strong>Gerencia / Administración</strong><br>
                <span style="color: #64748b; font-size: 8.5px;">Revisado y Aprobado</span><br>
                <span style="color: #94a3b8; font-size: 8px;">Firma y Sello Oficial</span>
            </td>
            <td style="width: 10%;"></td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        Ferretería El Constructor · Reporte Oficial generado automáticamente por ControlPro el {{ $fecha_emision }}<br>
        Este documento es un comprobante de auditoría contable interna válido para soporte administrativo y tributario.
    </div>
</body>
</html>
