import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    ArrowRight,
    CalendarDays,
    ClipboardList,
    CreditCard,
    Eye,
    FileDown,
    Search,
    X,
} from 'lucide-react';
import Sidebar from '../../Components/Sidebar';

const currency = new Intl.NumberFormat('es-CO', {
    maximumFractionDigits: 2,
});

function formatPrice(value) {
    return `$ ${currency.format(Number(value))} COP`;
}

export default function HistorialVentas({ ventas, totales, filtros, pagination }) {
    const [filters, setFilters] = useState(filtros);
    const [selectedSale, setSelectedSale] = useState(null);

    const applyFilters = (event) => {
        event.preventDefault();
        router.get('/ventas', filters, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 lg:flex">
            <Head title="Historial de ventas - Ferretería" />
            <Sidebar />

            <main className="min-w-0 flex-1 p-4 lg:ml-64 lg:p-8">
                <div className="mx-auto max-w-7xl">
                    <header className="mb-6">
                        <p className="text-sm font-medium text-amber-400">FERRETERÍA</p>
                        <h1 className="mt-1 text-2xl font-bold text-white">Historial de ventas</h1>
                        <p className="mt-1 text-sm text-slate-400">
                            Consulta ventas, pagos y productos registrados.
                        </p>
                    </header>

                    <section
                        aria-label="Resumen de ventas filtradas"
                        className="mb-6 grid gap-4 sm:grid-cols-2"
                    >
                        <article className="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-lg backdrop-blur-md">
                            <div className="flex items-center gap-3">
                                <span className="flex size-10 items-center justify-center rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-300">
                                    <ClipboardList size={19} aria-hidden="true" />
                                </span>
                                <div>
                                    <p className="text-sm text-slate-400">Total de ventas listadas</p>
                                    <p className="mt-1 text-xl font-bold text-amber-300">
                                        {formatPrice(totales.ventas)}
                                    </p>
                                </div>
                            </div>
                        </article>
                        <article className="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-lg backdrop-blur-md">
                            <div className="flex items-center gap-3">
                                <span className="flex size-10 items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-300">
                                    <CreditCard size={19} aria-hidden="true" />
                                </span>
                                <div>
                                    <p className="text-sm text-slate-400">Total recibido en efectivo</p>
                                    <p className="mt-1 text-xl font-bold text-emerald-300">
                                        {formatPrice(totales.efectivo)}
                                    </p>
                                </div>
                            </div>
                        </article>
                    </section>

                    <form
                        onSubmit={applyFilters}
                        className="mb-5 grid gap-3 rounded-2xl border border-slate-800 bg-slate-900/70 p-4 backdrop-blur-md md:grid-cols-2 xl:grid-cols-[minmax(220px,1.5fr)_repeat(3,minmax(140px,1fr))_auto_auto]"
                    >
                        <label className="relative block">
                            <span className="sr-only">Buscar por cliente o número de venta</span>
                            <Search
                                className="absolute top-1/2 left-3 -translate-y-1/2 text-slate-500"
                                size={17}
                                aria-hidden="true"
                            />
                            <input
                                type="search"
                                value={filters.q}
                                onChange={(event) =>
                                    setFilters((current) => ({ ...current, q: event.target.value }))
                                }
                                placeholder="Cliente o número de venta..."
                                className="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 pl-10 text-sm text-white outline-none placeholder:text-slate-500 focus:border-amber-500"
                            />
                        </label>

                        <label className="relative">
                            <span className="sr-only">Fecha desde</span>
                            <CalendarDays
                                className="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-slate-500"
                                size={16}
                                aria-hidden="true"
                            />
                            <input
                                type="date"
                                value={filters.desde}
                                onChange={(event) =>
                                    setFilters((current) => ({
                                        ...current,
                                        desde: event.target.value,
                                    }))
                                }
                                aria-label="Fecha desde"
                                className="w-full rounded-xl border border-slate-700 bg-slate-950 py-2.5 pr-3 pl-10 text-sm text-white outline-none focus:border-amber-500"
                            />
                        </label>

                        <label>
                            <span className="sr-only">Fecha hasta</span>
                            <input
                                type="date"
                                value={filters.hasta}
                                onChange={(event) =>
                                    setFilters((current) => ({
                                        ...current,
                                        hasta: event.target.value,
                                    }))
                                }
                                aria-label="Fecha hasta"
                                className="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500"
                            />
                        </label>

                        <label>
                            <span className="sr-only">Método de pago</span>
                            <select
                                value={filters.metodo_pago}
                                onChange={(event) =>
                                    setFilters((current) => ({
                                        ...current,
                                        metodo_pago: event.target.value,
                                    }))
                                }
                                aria-label="Método de pago"
                                className="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500"
                            >
                                <option value="">Todos los pagos</option>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta</option>
                                <option value="Transferencia">Transferencia</option>
                            </select>
                        </label>

                        <button
                            type="submit"
                            className="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400"
                        >
                            Filtrar
                        </button>
                        <Link
                            href="/ventas"
                            preserveScroll
                            className="rounded-xl border border-slate-700 px-4 py-2.5 text-center text-sm font-medium text-slate-300 transition hover:border-slate-500 hover:text-white"
                        >
                            Limpiar
                        </Link>
                    </form>

                    <div className="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 shadow-xl backdrop-blur-md">
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[900px] text-left text-sm">
                                <thead className="border-b border-slate-800 bg-slate-950/60 text-xs uppercase tracking-wide text-slate-400">
                                    <tr>
                                        {['Venta', 'Cliente', 'Usuario', 'Método de pago', 'Descuento', 'Total', 'Fecha', 'Acciones'].map(
                                            (column) => (
                                                <th key={column} scope="col" className="px-5 py-4">
                                                    {column}
                                                </th>
                                            ),
                                        )}
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-800">
                                    {ventas.map((venta) => (
                                        <tr key={venta.id} className="text-slate-200 transition hover:bg-slate-800/40">
                                            <td className="px-5 py-4 font-semibold text-amber-300">
                                                {venta.codigo_venta || `#${venta.id}`}
                                            </td>
                                            <td className="px-5 py-4">{venta.cliente}</td>
                                            <td className="px-5 py-4 text-slate-400">{venta.usuario}</td>
                                            <td className="px-5 py-4">{venta.metodo_pago}</td>
                                            <td className="px-5 py-4 font-medium text-emerald-300">
                                                {formatPrice(venta.descuento ?? 0)}
                                            </td>
                                            <td className="px-5 py-4 font-semibold text-white">
                                                {formatPrice(venta.total)}
                                            </td>
                                            <td className="px-5 py-4 text-slate-400">{venta.fecha}</td>
                                            <td className="px-5 py-4">
                                                <div className="flex items-center gap-2">
                                                    <button
                                                        type="button"
                                                        onClick={() => setSelectedSale(venta)}
                                                        aria-label={`Ver detalle de la venta ${venta.codigo_venta || venta.id}`}
                                                        className="inline-flex items-center gap-1.5 rounded-lg border border-slate-700 px-3 py-2 text-xs font-medium text-slate-300 transition hover:border-amber-500 hover:text-amber-300"
                                                    >
                                                        <Eye size={15} aria-hidden="true" />
                                                        Ver
                                                    </button>
                                                    <a
                                                        href={`/ventas/${venta.id}/factura`}
                                                        aria-label={`Descargar recibo PDF ${venta.codigo_venta || venta.id}`}
                                                        className="inline-flex items-center gap-1.5 rounded-lg border border-slate-700 px-3 py-2 text-xs font-medium text-slate-300 transition hover:border-emerald-500 hover:text-emerald-300"
                                                    >
                                                        <FileDown size={15} aria-hidden="true" />
                                                        PDF
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                    {ventas.length === 0 && (
                                        <tr>
                                            <td colSpan="8" className="px-5 py-12 text-center text-slate-500">
                                                No hay ventas que coincidan con los filtros.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {pagination && (
                            <div className="flex items-center justify-between border-t border-slate-800 px-5 py-4">
                                <span className="text-xs text-slate-500">
                                    Página {pagination.currentPage} de {pagination.lastPage}
                                </span>
                                <div className="flex gap-2">
                                    {pagination.previousPageUrl && (
                                        <Link
                                            href={pagination.previousPageUrl}
                                            preserveScroll
                                            aria-label="Página anterior"
                                            className="rounded-lg border border-slate-700 p-2 text-slate-300 transition hover:border-amber-500 hover:text-amber-300"
                                        >
                                            <ArrowLeft size={16} aria-hidden="true" />
                                        </Link>
                                    )}
                                    {pagination.nextPageUrl && (
                                        <Link
                                            href={pagination.nextPageUrl}
                                            preserveScroll
                                            aria-label="Página siguiente"
                                            className="rounded-lg border border-slate-700 p-2 text-slate-300 transition hover:border-amber-500 hover:text-amber-300"
                                        >
                                            <ArrowRight size={16} aria-hidden="true" />
                                        </Link>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </main>

            {selectedSale && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm"
                    role="presentation"
                    onMouseDown={(event) => {
                        if (event.target === event.currentTarget) {
                            setSelectedSale(null);
                        }
                    }}
                >
                    <section
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="sale-detail-title"
                        className="max-h-[85vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-700 bg-slate-900 p-5 shadow-2xl sm:p-6"
                    >
                        <header className="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <p className="text-sm font-medium text-amber-400">
                                    {selectedSale.cliente} · {selectedSale.fecha}
                                </p>
                                <h2 id="sale-detail-title" className="mt-1 text-xl font-bold text-white">
                                    Detalle de venta {selectedSale.codigo_venta || `#${selectedSale.id}`}
                                </h2>
                            </div>
                            <button
                                type="button"
                                onClick={() => setSelectedSale(null)}
                                aria-label="Cerrar detalle de venta"
                                className="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-800 hover:text-white"
                            >
                                <X size={19} aria-hidden="true" />
                            </button>
                        </header>

                        <div className="overflow-x-auto rounded-xl border border-slate-800">
                            <table className="w-full min-w-[460px] text-left text-sm">
                                <thead className="bg-slate-950/70 text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th scope="col" className="px-4 py-3">Producto</th>
                                        <th scope="col" className="px-4 py-3 text-center">Cantidad</th>
                                        <th scope="col" className="px-4 py-3 text-right">Precio unitario</th>
                                        <th scope="col" className="px-4 py-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-800">
                                    {selectedSale.productos.map((producto) => (
                                        <tr key={producto.id}>
                                            <td className="px-4 py-3">
                                                <p className="font-medium text-white">{producto.nombre}</p>
                                                {producto.codigo_sku && (
                                                    <p className="mt-1 text-xs text-slate-500">
                                                        SKU: {producto.codigo_sku}
                                                    </p>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-center text-slate-300">
                                                {producto.cantidad}
                                            </td>
                                            <td className="px-4 py-3 text-right text-slate-300">
                                                {formatPrice(producto.precio_unitario)}
                                            </td>
                                            <td className="px-4 py-3 text-right font-medium text-amber-300">
                                                {formatPrice(producto.subtotal)}
                                            </td>
                                        </tr>
                                    ))}
                                    {selectedSale.productos.length === 0 && (
                                        <tr>
                                            <td colSpan="4" className="px-4 py-8 text-center text-slate-500">
                                                Esta venta no tiene productos disponibles para mostrar.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                                <tfoot className="border-t border-slate-700 bg-slate-950/50">
                                    <tr>
                                        <th colSpan="3" className="px-4 py-3 text-right text-white">
                                            Subtotal
                                        </th>
                                        <td className="px-4 py-3 text-right font-medium text-slate-300">
                                            {formatPrice(selectedSale.subtotal)}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colSpan="3" className="px-4 py-3 text-right text-emerald-300">
                                            Descuento
                                        </th>
                                        <td className="px-4 py-3 text-right font-semibold text-emerald-300">
                                            - {formatPrice(selectedSale.descuento ?? 0)}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colSpan="3" className="px-4 py-3 text-right text-white">
                                            Total
                                        </th>
                                        <td className="px-4 py-3 text-right font-bold text-amber-300">
                                            {formatPrice(selectedSale.total)}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <footer className="mt-4 flex justify-end">
                            <button
                                type="button"
                                onClick={() => setSelectedSale(null)}
                                className="rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-slate-800"
                            >
                                Cerrar
                            </button>
                        </footer>
                    </section>
                </div>
            )}
        </div>
    );
}
