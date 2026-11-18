import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, ArrowRight } from 'lucide-react';
import Sidebar from '../../Components/Sidebar';

export default function Consulta({ title, description, columns, rows, pagination }) {
    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 lg:flex">
            <Head title={`${title} - Ferretería`} />
            <Sidebar />

            <main className="min-w-0 flex-1 p-4 lg:ml-64 lg:p-8">
                <div className="mx-auto max-w-6xl">
                    <header className="mb-6">
                        <p className="text-sm font-medium text-amber-400">FERRETERÍA</p>
                        <h1 className="mt-1 text-2xl font-bold text-white">{title}</h1>
                        <p className="mt-1 text-sm text-slate-400">{description}</p>
                    </header>

                    <div className="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 shadow-xl backdrop-blur-md">
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[560px] text-left text-sm">
                                <thead className="border-b border-slate-800 bg-slate-950/60 text-xs uppercase tracking-wide text-slate-400">
                                    <tr>
                                        {columns.map((column) => (
                                            <th key={column} scope="col" className="px-5 py-4">
                                                {column}
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-800">
                                    {rows.map((row) => (
                                        <tr key={row.id} className="text-slate-200">
                                            {row.values.map((value, index) => (
                                                <td
                                                    key={`${row.id}-${index}`}
                                                    className="px-5 py-4"
                                                >
                                                    {value || '—'}
                                                </td>
                                            ))}
                                        </tr>
                                    ))}
                                    {rows.length === 0 && (
                                        <tr>
                                            <td
                                                colSpan={columns.length}
                                                className="px-5 py-12 text-center text-slate-500"
                                            >
                                                Todavía no hay registros para mostrar.
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
        </div>
    );
}
