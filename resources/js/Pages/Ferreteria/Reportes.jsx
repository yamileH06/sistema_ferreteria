import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import {
    AlertCircle,
    ArrowDownRight,
    ArrowUpRight,
    BarChart3,
    Calendar,
    CalendarDays,
    CheckCircle2,
    Clock,
    CreditCard,
    DollarSign,
    FileDown,
    Filter,
    Package,
    PackageMinus,
    PackagePlus,
    RotateCcw,
    Search,
    ShoppingBag,
    ShoppingCart,
    TrendingDown,
    TrendingUp,
    Zap,
} from 'lucide-react';
import Sidebar from '../../Components/Sidebar';
import ProductImage from '../../Components/ProductImage';
import { getProductImage } from '../../Utils/productImage';

const currency = new Intl.NumberFormat('es-CO', {
    maximumFractionDigits: 2,
});

function formatPrice(value) {
    return `$ ${currency.format(Number(value || 0))} COP`;
}

function formatQuantity(value) {
    const num = Number(value || 0);
    return num % 1 === 0
        ? num.toLocaleString('es-CO')
        : num.toLocaleString('es-CO', { minimumFractionDigits: 1, maximumFractionDigits: 3 });
}

export default function Reportes({
    metricas,
    metodos_pago = [],
    ventas_por_dia = [],
    mas_vendidos = [],
    menos_vendidos = [],
    filtros = {},
}) {
    const [desde, setDesde] = useState(filtros.desde || '');
    const [hasta, setHasta] = useState(filtros.hasta || '');
    const [activeTab, setActiveTab] = useState('ambos'); // 'ambos', 'mas', 'menos'

    const handlePresetChange = (preset) => {
        router.get(
            '/reportes',
            { periodo: preset },
            { preserveState: true, preserveScroll: true },
        );
    };

    const handleCustomFilter = (e) => {
        e.preventDefault();
        router.get(
            '/reportes',
            {
                periodo: 'personalizado',
                desde: desde || undefined,
                hasta: hasta || undefined,
            },
            { preserveState: true, preserveScroll: true },
        );
    };

    const handleReset = () => {
        setDesde('');
        setHasta('');
        router.get(
            '/reportes',
            { periodo: 'este_mes' },
            { preserveState: true, preserveScroll: true },
        );
    };

    const getPdfUrl = () => {
        const params = new URLSearchParams();
        if (filtros.periodo) {
            params.set('periodo', filtros.periodo);
        }
        if (desde) {
            params.set('desde', desde);
        }
        if (hasta) {
            params.set('hasta', hasta);
        }
        return `/reportes/pdf?${params.toString()}`;
    };

    // Calculate max daily total for the visual bar chart
    const maxDailyTotal = Math.max(...ventas_por_dia.map((d) => d.total), 1);

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 lg:flex">
            <Head title="Reportes y Estadísticas de Ventas - Ferretería El Constructor" />
            <Sidebar />

            <main className="min-w-0 flex-1 p-4 lg:ml-64 lg:p-8">
                <div className="mx-auto max-w-7xl">
                    {/* Header */}
                    <header className="mb-6">
                        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p className="text-sm font-semibold tracking-wider text-amber-400 uppercase">
                                    Ferretería El Constructor
                                </p>
                                <h1 className="mt-1 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                                    Reportes y Rendimiento de Ventas
                                </h1>
                                <p className="mt-1 text-sm text-slate-400">
                                    Métricas en tiempo real, rotación de inventario y estadísticas financieras.
                                </p>
                            </div>
                            <div className="inline-flex items-center gap-2 self-start rounded-xl border border-slate-800 bg-slate-900/90 px-3.5 py-2 text-xs font-medium text-slate-300">
                                <CalendarDays size={16} className="text-amber-400" />
                                <span>
                                    {filtros.desde && filtros.hasta
                                        ? `${filtros.desde} al ${filtros.hasta}`
                                        : filtros.desde
                                          ? `Desde ${filtros.desde}`
                                          : filtros.hasta
                                            ? `Hasta ${filtros.hasta}`
                                            : 'Periodo completo'}
                                </span>
                            </div>
                        </div>
                    </header>

                    {/* Filter Bar with Presets & Date Pickers */}
                    <section className="mb-6 rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-xl backdrop-blur-md">
                        <div className="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                            {/* Preset Buttons */}
                            <div className="flex flex-wrap items-center gap-2">
                                <span className="mr-1 flex items-center gap-1.5 text-xs font-medium text-slate-400">
                                    <Clock size={14} className="text-amber-400" />
                                    Periodo rápido:
                                </span>
                                {[
                                    { id: 'hoy', label: 'Hoy' },
                                    { id: 'ayer', label: 'Ayer' },
                                    { id: '7dias', label: 'Últimos 7 días' },
                                    { id: 'este_mes', label: 'Este mes' },
                                    { id: 'mes_anterior', label: 'Mes anterior' },
                                    { id: 'todo', label: 'Histórico total' },
                                ].map((p) => {
                                    const isSelected = filtros.periodo === p.id;
                                    return (
                                        <button
                                            key={p.id}
                                            type="button"
                                            onClick={() => handlePresetChange(p.id)}
                                            className={`rounded-xl px-3 py-1.5 text-xs font-semibold transition ${
                                                isSelected
                                                    ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20'
                                                    : 'border border-slate-800 bg-slate-800/60 text-slate-300 hover:border-slate-700 hover:bg-slate-800 hover:text-white'
                                            }`}
                                        >
                                            {p.label}
                                        </button>
                                    );
                                })}
                            </div>

                            {/* Date Picker Form */}
                            <form
                                onSubmit={handleCustomFilter}
                                className="flex flex-wrap items-center gap-2.5 pt-2 xl:pt-0"
                            >
                                <div className="flex items-center gap-2">
                                    <label className="text-xs text-slate-400">Desde:</label>
                                    <input
                                        type="date"
                                        value={desde}
                                        onChange={(e) => setDesde(e.target.value)}
                                        className="rounded-xl border border-slate-700 bg-slate-950/80 px-3 py-1.5 text-xs text-white focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"
                                    />
                                </div>
                                <div className="flex items-center gap-2">
                                    <label className="text-xs text-slate-400">Hasta:</label>
                                    <input
                                        type="date"
                                        value={hasta}
                                        onChange={(e) => setHasta(e.target.value)}
                                        className="rounded-xl border border-slate-700 bg-slate-950/80 px-3 py-1.5 text-xs text-white focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"
                                    />
                                </div>
                                <button
                                    type="submit"
                                    className="flex items-center gap-1.5 rounded-xl bg-amber-500 px-3.5 py-1.5 text-xs font-semibold text-slate-950 shadow-md transition hover:bg-amber-400"
                                >
                                    <Filter size={13} />
                                    Filtrar
                                </button>
                                <button
                                    type="button"
                                    onClick={handleReset}
                                    title="Restablecer filtros"
                                    className="flex items-center gap-1 rounded-xl border border-slate-800 bg-slate-800/60 px-3 py-1.5 text-xs font-medium text-slate-400 transition hover:bg-slate-800 hover:text-white"
                                >
                                    <RotateCcw size={13} />
                                    Limpiar
                                </button>
                                <a
                                    href={getPdfUrl()}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title="Exportar informe oficial de ventas en PDF"
                                    className="flex items-center gap-1.5 rounded-xl border border-rose-500/40 bg-rose-500/10 px-3.5 py-1.5 text-xs font-semibold text-rose-300 shadow-md transition hover:border-rose-400 hover:bg-rose-500 hover:text-white"
                                >
                                    <FileDown size={13} />
                                    Exportar PDF
                                </a>
                            </form>
                        </div>
                    </section>

                    {/* Key Metrics Cards */}
                    <section
                        aria-label="Métricas Principales"
                        className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        {/* Total Ingresos */}
                        <article className="relative overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-lg backdrop-blur-md">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium tracking-wide text-slate-400 uppercase">
                                    Ingresos Totales
                                </span>
                                <span className="flex size-9 items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400">
                                    <DollarSign size={18} />
                                </span>
                            </div>
                            <p className="mt-3 text-2xl font-bold tracking-tight text-emerald-400">
                                {formatPrice(metricas.total_ingresos)}
                            </p>
                            <div className="mt-2 flex items-center justify-between text-xs text-slate-400">
                                <span>Rango seleccionado</span>
                                {metricas.total_descuentos > 0 && (
                                    <span className="text-amber-400/90">
                                        Desc: {formatPrice(metricas.total_descuentos)}
                                    </span>
                                )}
                            </div>
                            <div className="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400" />
                        </article>

                        {/* Total Transacciones */}
                        <article className="relative overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-lg backdrop-blur-md">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium tracking-wide text-slate-400 uppercase">
                                    Ventas / Transacciones
                                </span>
                                <span className="flex size-9 items-center justify-center rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-400">
                                    <ShoppingCart size={18} />
                                </span>
                            </div>
                            <p className="mt-3 text-2xl font-bold tracking-tight text-amber-300">
                                {metricas.total_transacciones.toLocaleString('es-CO')}
                            </p>
                            <p className="mt-2 text-xs text-slate-400">
                                {metricas.total_transacciones === 1
                                    ? '1 venta concretada'
                                    : `${metricas.total_transacciones} ventas concretadas`}
                            </p>
                            <div className="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-amber-500 to-orange-400" />
                        </article>

                        {/* Unidades Vendidas */}
                        <article className="relative overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-lg backdrop-blur-md">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium tracking-wide text-slate-400 uppercase">
                                    Unidades Vendidas
                                </span>
                                <span className="flex size-9 items-center justify-center rounded-xl border border-sky-500/30 bg-sky-500/10 text-sky-400">
                                    <Package size={18} />
                                </span>
                            </div>
                            <p className="mt-3 text-2xl font-bold tracking-tight text-sky-300">
                                {formatQuantity(metricas.total_productos_vendidos)}
                            </p>
                            <p className="mt-2 text-xs text-slate-400">
                                Artículos salientes del inventario
                            </p>
                            <div className="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-sky-500 to-blue-500" />
                        </article>

                        {/* Ticket Promedio */}
                        <article className="relative overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-lg backdrop-blur-md">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium tracking-wide text-slate-400 uppercase">
                                    Ticket Promedio
                                </span>
                                <span className="flex size-9 items-center justify-center rounded-xl border border-purple-500/30 bg-purple-500/10 text-purple-400">
                                    <BarChart3 size={18} />
                                </span>
                            </div>
                            <p className="mt-3 text-2xl font-bold tracking-tight text-purple-300">
                                {formatPrice(metricas.ticket_promedio)}
                            </p>
                            <p className="mt-2 text-xs text-slate-400">
                                Promedio facturado por compra
                            </p>
                            <div className="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-purple-500 to-indigo-500" />
                        </article>
                    </section>

                    {/* Charts & Payment Methods Section */}
                    <section className="mb-6 grid gap-6 lg:grid-cols-3">
                        {/* Daily Sales Chart */}
                        <div className="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-lg backdrop-blur-md lg:col-span-2">
                            <div className="mb-4 flex items-center justify-between">
                                <div>
                                    <h2 className="text-base font-semibold text-white">
                                        Evolución de Ventas en el Periodo
                                    </h2>
                                    <p className="text-xs text-slate-400">
                                        Comportamiento diario de la facturación
                                    </p>
                                </div>
                                <span className="rounded-lg border border-slate-800 bg-slate-950 px-2.5 py-1 text-xs text-slate-400">
                                    {ventas_por_dia.length} {ventas_por_dia.length === 1 ? 'día con actividad' : 'días con actividad'}
                                </span>
                            </div>

                            {ventas_por_dia.length === 0 ? (
                                <div className="flex h-56 flex-col items-center justify-center rounded-xl border border-dashed border-slate-800 p-6 text-center">
                                    <Calendar className="mb-2 text-slate-600" size={32} />
                                    <p className="text-sm font-medium text-slate-400">
                                        No hay registros de ventas en este rango de fechas.
                                    </p>
                                    <p className="mt-1 text-xs text-slate-500">
                                        Prueba seleccionando otro periodo o consulta el historial total.
                                    </p>
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {/* Responsive visual bar representation */}
                                    <div className="flex h-48 items-end gap-2 overflow-x-auto pt-6 pb-2">
                                        {ventas_por_dia.map((dia) => {
                                            const heightPct = Math.max(
                                                Math.round((dia.total / maxDailyTotal) * 100),
                                                12,
                                            );
                                            return (
                                                <div
                                                    key={dia.fecha}
                                                    className="group flex flex-1 min-w-[38px] flex-col items-center justify-end h-full"
                                                >
                                                    {/* Tooltip on hover */}
                                                    <div className="mb-1 hidden text-center group-hover:block">
                                                        <span className="rounded bg-slate-800 px-1.5 py-0.5 text-[10px] text-amber-300 font-semibold shadow">
                                                            {formatPrice(dia.total)}
                                                        </span>
                                                    </div>
                                                    <div
                                                        style={{ height: `${heightPct}%` }}
                                                        className="w-full rounded-t-lg bg-gradient-to-t from-amber-500/70 to-amber-400 transition-all duration-300 group-hover:from-amber-400 group-hover:to-amber-300 group-hover:brightness-110"
                                                    />
                                                    <span className="mt-2 text-[10px] font-medium text-slate-400 truncate max-w-full">
                                                        {dia.fecha.split('-').slice(1).join('/')}
                                                    </span>
                                                </div>
                                            );
                                        })}
                                    </div>
                                    <div className="flex items-center justify-between border-t border-slate-800/80 pt-3 text-xs text-slate-400">
                                        <span>Total del periodo consultado:</span>
                                        <span className="font-semibold text-white">
                                            {formatPrice(metricas.total_ingresos)}
                                        </span>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Payment Methods Breakdown */}
                        <div className="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-lg backdrop-blur-md">
                            <div className="mb-4">
                                <h2 className="text-base font-semibold text-white">
                                    Métodos de Pago
                                </h2>
                                <p className="text-xs text-slate-400">
                                    Distribución del dinero recaudado
                                </p>
                            </div>

                            {metodos_pago.length === 0 ? (
                                <div className="flex h-56 flex-col items-center justify-center rounded-xl border border-dashed border-slate-800 p-6 text-center">
                                    <CreditCard className="mb-2 text-slate-600" size={30} />
                                    <p className="text-xs text-slate-400">
                                        Sin datos de pagos para este filtro.
                                    </p>
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {metodos_pago.map((metodo) => (
                                        <div key={metodo.metodo} className="space-y-1.5">
                                            <div className="flex items-center justify-between text-xs">
                                                <span className="font-medium text-slate-200">
                                                    {metodo.metodo}
                                                </span>
                                                <div className="text-right">
                                                    <span className="font-semibold text-amber-300">
                                                        {formatPrice(metodo.monto)}
                                                    </span>
                                                    <span className="ml-1 text-[11px] text-slate-500">
                                                        ({metodo.porcentaje}%)
                                                    </span>
                                                </div>
                                            </div>
                                            <div className="h-2 w-full overflow-hidden rounded-full bg-slate-800">
                                                <div
                                                    className={`h-full rounded-full transition-all duration-500 ${
                                                        metodo.metodo === 'Efectivo'
                                                            ? 'bg-emerald-400'
                                                            : metodo.metodo === 'Tarjeta'
                                                              ? 'bg-sky-400'
                                                              : 'bg-purple-400'
                                                    }`}
                                                    style={{ width: `${Math.min(metodo.porcentaje, 100)}%` }}
                                                />
                                            </div>
                                            <p className="text-[11px] text-slate-400">
                                                {metodo.transacciones} {metodo.transacciones === 1 ? 'transacción' : 'transacciones'}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </section>

                    {/* Products Performance Section (Best Sellers vs Least Sellers) */}
                    <section className="mb-8 space-y-4">
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 className="text-xl font-bold text-white">
                                    Rotación de Productos
                                </h2>
                                <p className="text-xs text-slate-400">
                                    Compara los artículos con mayor demanda frente a los de baja salida.
                                </p>
                            </div>

                            {/* View Switcher Tabs */}
                            <div className="inline-flex rounded-xl border border-slate-800 bg-slate-900/90 p-1">
                                <button
                                    type="button"
                                    onClick={() => setActiveTab('ambos')}
                                    className={`rounded-lg px-3 py-1.5 text-xs font-medium transition ${
                                        activeTab === 'ambos'
                                            ? 'bg-amber-500 text-slate-950 shadow'
                                            : 'text-slate-400 hover:text-white'
                                    }`}
                                >
                                    Vista Comparativa
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setActiveTab('mas')}
                                    className={`flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition ${
                                        activeTab === 'mas'
                                            ? 'bg-emerald-500 text-slate-950 shadow'
                                            : 'text-slate-400 hover:text-white'
                                    }`}
                                >
                                    <TrendingUp size={13} />
                                    Más Vendidos
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setActiveTab('menos')}
                                    className={`flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition ${
                                        activeTab === 'menos'
                                            ? 'bg-rose-500 text-white shadow'
                                            : 'text-slate-400 hover:text-white'
                                    }`}
                                >
                                    <TrendingDown size={13} />
                                    Menos Vendidos
                                </button>
                            </div>
                        </div>

                        {/* Dual Column or Single Column Grid */}
                        <div
                            className={`grid gap-6 ${
                                activeTab === 'ambos' ? 'xl:grid-cols-2' : 'grid-cols-1'
                            }`}
                        >
                            {/* TOP MÁS VENDIDOS */}
                            {(activeTab === 'ambos' || activeTab === 'mas') && (
                                <article className="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-xl backdrop-blur-md">
                                    <div className="mb-4 flex items-center justify-between border-b border-slate-800/80 pb-3">
                                        <div className="flex items-center gap-2.5">
                                            <span className="flex size-9 items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400">
                                                <TrendingUp size={18} />
                                            </span>
                                            <div>
                                                <h3 className="text-base font-bold text-white">
                                                    Productos Más Vendidos
                                                </h3>
                                                <p className="text-xs text-slate-400">
                                                    Mayor rotación y volumen en el periodo
                                                </p>
                                            </div>
                                        </div>
                                        <span className="rounded-full bg-emerald-500/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-300 border border-emerald-500/20">
                                            Alta demanda
                                        </span>
                                    </div>

                                    {mas_vendidos.length === 0 ? (
                                        <div className="flex h-48 flex-col items-center justify-center rounded-xl border border-dashed border-slate-800 p-6 text-center">
                                            <PackageMinus className="mb-2 text-slate-600" size={30} />
                                            <p className="text-xs text-slate-400">
                                                No se registraron ventas de productos en este periodo.
                                            </p>
                                        </div>
                                    ) : (
                                        <div className="overflow-x-auto">
                                            <table className="w-full text-left text-xs">
                                                <thead>
                                                    <tr className="border-b border-slate-800 text-slate-400">
                                                        <th className="pb-2.5 font-medium">#</th>
                                                        <th className="pb-2.5 font-medium">Producto</th>
                                                        <th className="pb-2.5 text-right font-medium">Vendidos</th>
                                                        <th className="pb-2.5 text-right font-medium">Recaudado</th>
                                                        <th className="pb-2.5 text-right font-medium">Stock</th>
                                                    </tr>
                                                </thead>
                                                <tbody className="divide-y divide-slate-800/60">
                                                    {mas_vendidos.map((prod, index) => {
                                                        const imgSrc = getProductImage(prod);
                                                        return (
                                                            <tr
                                                                key={prod.id}
                                                                className="transition-colors hover:bg-slate-800/40"
                                                            >
                                                                <td className="py-3 font-semibold text-slate-400">
                                                                    <span
                                                                        className={`flex size-6 items-center justify-center rounded-full text-[11px] font-bold ${
                                                                            index === 0
                                                                                ? 'bg-amber-400 text-slate-950'
                                                                                : index === 1
                                                                                  ? 'bg-slate-300 text-slate-900'
                                                                                  : index === 2
                                                                                    ? 'bg-amber-700/80 text-white'
                                                                                    : 'bg-slate-800 text-slate-400'
                                                                        }`}
                                                                    >
                                                                        {index + 1}
                                                                    </span>
                                                                </td>
                                                                <td className="py-3">
                                                                    <div className="flex items-center gap-3">
                                                                        <div className="size-10 rounded-lg overflow-hidden border border-slate-700 bg-slate-950 shrink-0">
                                                                            <ProductImage
                                                                                product={prod}
                                                                                className="size-full"
                                                                                imgClassName="size-full object-cover"
                                                                                compact
                                                                            />
                                                                        </div>
                                                                        <div className="min-w-0 max-w-[180px] sm:max-w-[240px]">
                                                                            <p className="font-semibold text-slate-100 truncate" title={prod.nombre}>
                                                                                {prod.nombre}
                                                                            </p>
                                                                            <p className="text-[11px] text-slate-400 truncate">
                                                                                SKU: {prod.codigo_sku || '—'} · {prod.categoria}
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td className="py-3 text-right">
                                                                    <span className="rounded-lg bg-emerald-500/10 px-2 py-1 font-bold text-emerald-300">
                                                                        {formatQuantity(prod.total_vendido)} u.
                                                                    </span>
                                                                </td>
                                                                <td className="py-3 text-right font-medium text-slate-200">
                                                                    {formatPrice(prod.total_recaudado)}
                                                                </td>
                                                                <td className="py-3 text-right">
                                                                    <span
                                                                        className={`text-[11px] font-semibold ${
                                                                            prod.stock <= 5
                                                                                ? 'text-rose-400'
                                                                                : 'text-slate-400'
                                                                        }`}
                                                                    >
                                                                        {formatQuantity(prod.stock)} u.
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        );
                                                    })}
                                                </tbody>
                                            </table>
                                        </div>
                                    )}
                                </article>
                            )}

                            {/* TOP MENOS VENDIDOS / BAJA ROTACIÓN */}
                            {(activeTab === 'ambos' || activeTab === 'menos') && (
                                <article className="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-xl backdrop-blur-md">
                                    <div className="mb-4 flex items-center justify-between border-b border-slate-800/80 pb-3">
                                        <div className="flex items-center gap-2.5">
                                            <span className="flex size-9 items-center justify-center rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-400">
                                                <TrendingDown size={18} />
                                            </span>
                                            <div>
                                                <h3 className="text-base font-bold text-white">
                                                    Productos Menos Vendidos
                                                </h3>
                                                <p className="text-xs text-slate-400">
                                                    Baja salida y posible inventario estancado
                                                </p>
                                            </div>
                                        </div>
                                        <span className="rounded-full bg-rose-500/10 px-2.5 py-1 text-[11px] font-semibold text-rose-300 border border-rose-500/20">
                                            Baja rotación
                                        </span>
                                    </div>

                                    {menos_vendidos.length === 0 ? (
                                        <div className="flex h-48 flex-col items-center justify-center rounded-xl border border-dashed border-slate-800 p-6 text-center">
                                            <PackagePlus className="mb-2 text-slate-600" size={30} />
                                            <p className="text-xs text-slate-400">
                                                No hay artículos registrados en el catálogo.
                                            </p>
                                        </div>
                                    ) : (
                                        <div className="overflow-x-auto">
                                            <table className="w-full text-left text-xs">
                                                <thead>
                                                    <tr className="border-b border-slate-800 text-slate-400">
                                                        <th className="pb-2.5 font-medium">#</th>
                                                        <th className="pb-2.5 font-medium">Producto</th>
                                                        <th className="pb-2.5 text-right font-medium">Salidas</th>
                                                        <th className="pb-2.5 text-right font-medium">Precio Unit.</th>
                                                        <th className="pb-2.5 text-right font-medium">Stock en Bodega</th>
                                                    </tr>
                                                </thead>
                                                <tbody className="divide-y divide-slate-800/60">
                                                    {menos_vendidos.map((prod, index) => {
                                                        const imgSrc = getProductImage(prod);
                                                        const isZeroSales = Number(prod.total_vendido) === 0;

                                                        return (
                                                            <tr
                                                                key={prod.id}
                                                                className="transition-colors hover:bg-slate-800/40"
                                                            >
                                                                <td className="py-3 font-semibold text-slate-500">
                                                                    <span className="flex size-6 items-center justify-center rounded-full bg-slate-800 text-[11px] text-slate-400">
                                                                        {index + 1}
                                                                    </span>
                                                                </td>
                                                                <td className="py-3">
                                                                    <div className="flex items-center gap-3">
                                                                        <div className="size-10 rounded-lg overflow-hidden border border-slate-700 bg-slate-950 shrink-0">
                                                                            <ProductImage
                                                                                product={prod}
                                                                                className="size-full"
                                                                                imgClassName="size-full object-cover"
                                                                                compact
                                                                            />
                                                                        </div>
                                                                        <div className="min-w-0 max-w-[180px] sm:max-w-[240px]">
                                                                            <p className="font-semibold text-slate-100 truncate" title={prod.nombre}>
                                                                                {prod.nombre}
                                                                            </p>
                                                                            <p className="text-[11px] text-slate-400 truncate">
                                                                                SKU: {prod.codigo_sku || '—'} · {prod.categoria}
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td className="py-3 text-right">
                                                                    {isZeroSales ? (
                                                                        <span className="rounded-lg bg-rose-500/10 px-2 py-1 font-semibold text-rose-300">
                                                                            0 salidas
                                                                        </span>
                                                                    ) : (
                                                                        <span className="rounded-lg bg-amber-500/10 px-2 py-1 font-medium text-amber-300">
                                                                            {formatQuantity(prod.total_vendido)} u.
                                                                        </span>
                                                                    )}
                                                                </td>
                                                                <td className="py-3 text-right font-medium text-slate-300">
                                                                    {formatPrice(prod.precio_venta)}
                                                                </td>
                                                                <td className="py-3 text-right">
                                                                    <span
                                                                        className={`font-bold ${
                                                                            prod.stock > 20
                                                                                ? 'text-amber-400'
                                                                                : 'text-slate-300'
                                                                        }`}
                                                                        title={prod.stock > 20 ? 'Stock alto sin movimiento' : ''}
                                                                    >
                                                                        {formatQuantity(prod.stock)} u.
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        );
                                                    })}
                                                </tbody>
                                            </table>
                                        </div>
                                    )}
                                </article>
                            )}
                        </div>
                    </section>
                </div>
            </main>
        </div>
    );
}
