import React, { useMemo, useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import {
    AlertTriangle,
    Boxes,
    Building2,
    CheckCircle2,
    DollarSign,
    Mail,
    MapPin,
    PackagePlus,
    Pencil,
    Phone,
    Plus,
    Receipt,
    Search,
    Trash2,
    Truck,
    X,
} from 'lucide-react';
import Sidebar from '../../Components/Sidebar';

const emptySupplier = {
    nombre: '',
    nit: '',
    telefono: '',
    correo: '',
    direccion: '',
};

export default function Proveedores({ proveedores = [], productos = [] }) {
    const [search, setSearch] = useState('');
    const [modalOpen, setModalOpen] = useState(false);
    const [editingSupplier, setEditingSupplier] = useState(null);
    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm(emptySupplier);

    // Reabastecimiento modal state
    const [restockModalOpen, setRestockModalOpen] = useState(false);
    const [restockSupplier, setRestockSupplier] = useState(null);

    const restockForm = useForm({
        items: [{ producto_id: '', cantidad: '', costo_unitario: '' }],
        notas: '',
    });

    const filteredSuppliers = useMemo(() => {
        const query = search.trim().toLocaleLowerCase();

        if (!query) {
            return proveedores;
        }

        return proveedores.filter((supplier) =>
            [supplier.nombre, supplier.nit]
                .filter(Boolean)
                .some((value) => String(value).toLocaleLowerCase().includes(query)),
        );
    }, [proveedores, search]);

    // Group or prioritize products for the selected restock supplier
    const supplierProducts = useMemo(() => {
        if (!restockSupplier) return { associated: [], others: [] };

        const associated = [];
        const others = [];

        productos.forEach((p) => {
            if (p.proveedor_id === restockSupplier.id) {
                associated.push(p);
            } else {
                others.push(p);
            }
        });

        return { associated, others };
    }, [productos, restockSupplier]);

    const closeModal = () => {
        setModalOpen(false);
        setEditingSupplier(null);
        reset();
        clearErrors();
    };

    const openCreateModal = () => {
        reset();
        clearErrors();
        setEditingSupplier(null);
        setModalOpen(true);
    };

    const openEditModal = (supplier) => {
        clearErrors();
        setEditingSupplier(supplier);
        setData({
            nombre: supplier.nombre ?? '',
            nit: supplier.nit ?? '',
            telefono: supplier.telefono ?? '',
            correo: supplier.correo ?? '',
            direccion: supplier.direccion ?? '',
        });
        setModalOpen(true);
    };

    const submit = (event) => {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: closeModal,
        };

        if (editingSupplier) {
            put(`/proveedores/${editingSupplier.id}`, options);
            return;
        }

        post('/proveedores', options);
    };

    const deleteSupplier = (supplier) => {
        if (!window.confirm(`¿Eliminar el proveedor "${supplier.nombre}"?`)) {
            return;
        }

        router.delete(`/proveedores/${supplier.id}`, {
            preserveScroll: true,
        });
    };

    // Open Restock Modal
    const openRestockModal = (supplier) => {
        setRestockSupplier(supplier);
        restockForm.clearErrors();

        // If supplier already has associated products, we can preselect the first one or start clean
        const firstAssociated = productos.find((p) => p.proveedor_id === supplier.id);
        restockForm.setData({
            items: [
                {
                    producto_id: firstAssociated ? String(firstAssociated.id) : '',
                    cantidad: '',
                    costo_unitario: firstAssociated ? (firstAssociated.precio_compra ?? '') : '',
                },
            ],
            notas: '',
        });
        setRestockModalOpen(true);
    };

    const closeRestockModal = () => {
        setRestockModalOpen(false);
        setRestockSupplier(null);
        restockForm.reset();
        restockForm.clearErrors();
    };

    const addRestockItemRow = () => {
        restockForm.setData('items', [
            ...restockForm.data.items,
            { producto_id: '', cantidad: '', costo_unitario: '' },
        ]);
    };

    const removeRestockItemRow = (index) => {
        if (restockForm.data.items.length <= 1) return;
        const newItems = restockForm.data.items.filter((_, i) => i !== index);
        restockForm.setData('items', newItems);
    };

    const updateRestockItemRow = (index, field, value) => {
        const newItems = [...restockForm.data.items];
        newItems[index] = { ...newItems[index], [field]: value };

        // If user selects a product, auto-suggest the product's current purchase price if empty
        if (field === 'producto_id') {
            const selectedProduct = productos.find((p) => String(p.id) === String(value));
            if (selectedProduct && (!newItems[index].costo_unitario || newItems[index].costo_unitario === '')) {
                newItems[index].costo_unitario = selectedProduct.precio_compra ?? '';
            }
        }

        restockForm.setData('items', newItems);
    };

    const submitRestock = (event) => {
        event.preventDefault();
        if (!restockSupplier) return;

        restockForm.post(`/proveedores/${restockSupplier.id}/reabastecer`, {
            preserveScroll: true,
            onSuccess: () => {
                closeRestockModal();
            },
        });
    };

    // Live calculations for restock summary
    const restockSummary = useMemo(() => {
        let totalUnits = 0;
        let totalCost = 0;
        let validItemsCount = 0;

        restockForm.data.items.forEach((item) => {
            const qty = parseFloat(item.cantidad) || 0;
            const cost = parseFloat(item.costo_unitario) || 0;

            if (item.producto_id && qty > 0) {
                totalUnits += qty;
                totalCost += qty * cost;
                validItemsCount += 1;
            }
        });

        return { totalUnits, totalCost, validItemsCount };
    }, [restockForm.data.items]);

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 lg:flex">
            <Head title="Proveedores - Ferretería" />
            <Sidebar />

            <main className="min-w-0 flex-1 p-4 lg:ml-64 lg:p-8">
                <div className="mx-auto max-w-7xl">
                    <header className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-sm font-medium text-amber-400">FERRETERÍA</p>
                            <h1 className="mt-1 text-2xl font-bold text-white">Proveedores</h1>
                            <p className="mt-1 text-sm text-slate-400">
                                Administra proveedores y registra entradas directas de mercancía al inventario.
                            </p>
                        </div>
                        <button
                            type="button"
                            onClick={openCreateModal}
                            className="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-2 focus:ring-offset-slate-950"
                        >
                            <Plus size={17} aria-hidden="true" />
                            Nuevo Proveedor
                        </button>
                    </header>

                    <section className="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 shadow-xl backdrop-blur-md">
                        <div className="flex flex-col gap-3 border-b border-slate-800 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 className="font-semibold text-white">Directorio de proveedores</h2>
                                <p className="mt-1 text-xs text-slate-500">
                                    {filteredSuppliers.length} de {proveedores.length} proveedores
                                </p>
                            </div>
                            <label className="relative block w-full sm:max-w-sm">
                                <span className="sr-only">Buscar proveedor por nombre o NIT</span>
                                <Search
                                    className="absolute top-1/2 left-3 -translate-y-1/2 text-slate-500"
                                    size={17}
                                    aria-hidden="true"
                                />
                                <input
                                    type="search"
                                    value={search}
                                    onChange={(event) => setSearch(event.target.value)}
                                    placeholder="Buscar por nombre o NIT..."
                                    className="w-full rounded-xl border border-slate-700 bg-slate-950 py-2.5 pr-3 pl-10 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
                                />
                            </label>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[950px] text-left text-sm">
                                <thead className="bg-slate-950/60 text-xs uppercase tracking-wide text-slate-400">
                                    <tr>
                                        <th scope="col" className="px-5 py-4">Proveedor</th>
                                        <th scope="col" className="px-5 py-4">NIT</th>
                                        <th scope="col" className="px-5 py-4">Teléfono</th>
                                        <th scope="col" className="px-5 py-4">Correo</th>
                                        <th scope="col" className="px-5 py-4">Dirección</th>
                                        <th scope="col" className="px-5 py-4 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-800">
                                    {filteredSuppliers.map((supplier) => (
                                        <tr key={supplier.id} className="text-slate-200 transition hover:bg-slate-800/40">
                                            <td className="px-5 py-4">
                                                <span className="flex items-center gap-2.5 font-medium text-white">
                                                    <Building2 size={17} className="shrink-0 text-amber-400" aria-hidden="true" />
                                                    {supplier.nombre}
                                                </span>
                                            </td>
                                            <td className="px-5 py-4 text-slate-300">{supplier.nit || '—'}</td>
                                            <td className="px-5 py-4 text-slate-300">{supplier.telefono || '—'}</td>
                                            <td className="px-5 py-4 text-slate-300">{supplier.correo || '—'}</td>
                                            <td className="max-w-xs truncate px-5 py-4 text-slate-400" title={supplier.direccion || ''}>
                                                {supplier.direccion || '—'}
                                            </td>
                                            <td className="px-5 py-4">
                                                <div className="flex items-center justify-end gap-2">
                                                    {/* Botón Registrar Compra / Surtir */}
                                                    <button
                                                        type="button"
                                                        onClick={() => openRestockModal(supplier)}
                                                        title="Registrar Compra / Reabastecer Inventario"
                                                        aria-label={`Reabastecer con ${supplier.nombre}`}
                                                        className="inline-flex items-center gap-1.5 rounded-lg border border-amber-500/40 bg-amber-500/10 px-2.5 py-1.5 text-xs font-semibold text-amber-300 transition hover:bg-amber-500 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-amber-400/40"
                                                    >
                                                        <PackagePlus size={15} aria-hidden="true" />
                                                        <span>+ Surtir</span>
                                                    </button>

                                                    <button
                                                        type="button"
                                                        onClick={() => openEditModal(supplier)}
                                                        aria-label={`Editar ${supplier.nombre}`}
                                                        className="rounded-lg border border-slate-700 p-2 text-slate-300 transition hover:border-amber-500 hover:text-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                                                    >
                                                        <Pencil size={15} aria-hidden="true" />
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => deleteSupplier(supplier)}
                                                        aria-label={`Eliminar ${supplier.nombre}`}
                                                        className="rounded-lg border border-slate-700 p-2 text-slate-300 transition hover:border-rose-500 hover:text-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-500/20"
                                                    >
                                                        <Trash2 size={15} aria-hidden="true" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                    {filteredSuppliers.length === 0 && (
                                        <tr>
                                            <td colSpan="6" className="px-5 py-14 text-center">
                                                <Truck size={26} className="mx-auto mb-2 text-slate-600" aria-hidden="true" />
                                                <p className="font-medium text-slate-300">
                                                    {proveedores.length === 0
                                                        ? 'Todavía no hay proveedores registrados.'
                                                        : 'No hay proveedores que coincidan con la búsqueda.'}
                                                </p>
                                                {proveedores.length === 0 && (
                                                    <p className="mt-1 text-sm text-slate-500">
                                                        Usa “Nuevo Proveedor” para agregar el primero.
                                                    </p>
                                                )}
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </main>

            {/* MODAL 1: Crear / Editar Proveedor */}
            {modalOpen && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm"
                    role="presentation"
                    onMouseDown={(event) => {
                        if (event.target === event.currentTarget && !processing) {
                            closeModal();
                        }
                    }}
                >
                    <section
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="provider-modal-title"
                        className="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl border border-slate-700 bg-slate-900 p-5 shadow-2xl sm:p-6"
                    >
                        <header className="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <span className="mb-3 flex size-10 items-center justify-center rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-300">
                                    {editingSupplier ? <Pencil size={18} aria-hidden="true" /> : <Truck size={19} aria-hidden="true" />}
                                </span>
                                <h2 id="provider-modal-title" className="text-lg font-semibold text-white">
                                    {editingSupplier ? 'Editar proveedor' : 'Nuevo proveedor'}
                                </h2>
                                <p className="mt-1 text-sm text-slate-400">
                                    Completa los datos de contacto del proveedor.
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={closeModal}
                                disabled={processing}
                                aria-label="Cerrar formulario de proveedor"
                                className="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-800 hover:text-white disabled:opacity-50"
                            >
                                <X size={19} aria-hidden="true" />
                            </button>
                        </header>

                        <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
                            <FormField
                                label="Nombre"
                                name="nombre"
                                value={data.nombre}
                                onChange={(value) => setData('nombre', value)}
                                error={errors.nombre}
                                required
                            />
                            <FormField
                                label="NIT (opcional)"
                                name="nit"
                                value={data.nit}
                                onChange={(value) => setData('nit', value)}
                                error={errors.nit}
                            />
                            <FormField
                                label="Teléfono"
                                name="telefono"
                                icon={Phone}
                                value={data.telefono}
                                onChange={(value) => setData('telefono', value)}
                                error={errors.telefono}
                            />
                            <FormField
                                label="Correo electrónico"
                                name="correo"
                                type="email"
                                icon={Mail}
                                value={data.correo}
                                onChange={(value) => setData('correo', value)}
                                error={errors.correo}
                            />
                            <div className="sm:col-span-2">
                                <FormField
                                    label="Dirección"
                                    name="direccion"
                                    icon={MapPin}
                                    value={data.direccion}
                                    onChange={(value) => setData('direccion', value)}
                                    error={errors.direccion}
                                />
                            </div>

                            <footer className="flex justify-end gap-2 pt-2 sm:col-span-2">
                                <button
                                    type="button"
                                    onClick={closeModal}
                                    disabled={processing}
                                    className="rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-slate-800 disabled:opacity-50"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 disabled:cursor-wait disabled:opacity-60"
                                >
                                    {processing
                                        ? 'Guardando...'
                                        : editingSupplier
                                            ? 'Guardar cambios'
                                            : 'Guardar proveedor'}
                                </button>
                            </footer>
                        </form>
                    </section>
                </div>
            )}

            {/* MODAL 2: Registrar Compra / Reabastecimiento de Inventario */}
            {restockModalOpen && restockSupplier && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/85 p-3 sm:p-4 backdrop-blur-md"
                    role="presentation"
                    onMouseDown={(event) => {
                        if (event.target === event.currentTarget && !restockForm.processing) {
                            closeRestockModal();
                        }
                    }}
                >
                    <section
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="restock-modal-title"
                        className="flex max-h-[92vh] w-full max-w-3xl flex-col rounded-3xl border border-slate-700/80 bg-slate-900 shadow-2xl shadow-black/70"
                    >
                        {/* Header del Modal */}
                        <header className="flex items-start justify-between border-b border-slate-800 p-5 sm:p-6">
                            <div className="flex items-center gap-3.5">
                                <span className="flex size-11 items-center justify-center rounded-2xl border border-amber-500/30 bg-amber-500/10 text-amber-400">
                                    <PackagePlus size={22} aria-hidden="true" />
                                </span>
                                <div>
                                    <h2 id="restock-modal-title" className="text-xl font-bold text-white">
                                        Registrar Compra / Reabastecer
                                    </h2>
                                    <p className="mt-0.5 text-xs text-slate-400">
                                        Proveedor:{' '}
                                        <span className="font-semibold text-amber-400">
                                            {restockSupplier.nombre}
                                        </span>
                                        {restockSupplier.nit && (
                                            <span className="text-slate-500"> · NIT: {restockSupplier.nit}</span>
                                        )}
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={closeRestockModal}
                                disabled={restockForm.processing}
                                aria-label="Cerrar ventana de reabastecimiento"
                                className="rounded-xl p-2 text-slate-400 transition hover:bg-slate-800 hover:text-white disabled:opacity-50"
                            >
                                <X size={20} aria-hidden="true" />
                            </button>
                        </header>

                        {/* Formulario Scrolleable */}
                        <form onSubmit={submitRestock} className="flex flex-1 flex-col overflow-hidden">
                            <div className="flex-1 space-y-5 overflow-y-auto p-5 sm:p-6">
                                {/* Alertas de validación general */}
                                {Object.keys(restockForm.errors).length > 0 && (
                                    <div
                                        role="alert"
                                        className="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-xs text-rose-300"
                                    >
                                        <p className="font-semibold">Revisa los siguientes errores:</p>
                                        <ul className="mt-1.5 list-inside list-disc space-y-1">
                                            {Object.entries(restockForm.errors).map(([key, msg]) => (
                                                <li key={key}>{msg}</li>
                                            ))}
                                        </ul>
                                    </div>
                                )}

                                {/* Lista de productos a reabastecer */}
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <label className="text-sm font-semibold text-slate-200">
                                            Productos a ingresar al inventario
                                        </label>
                                        <span className="text-xs text-slate-400">
                                            {restockForm.data.items.length}{' '}
                                            {restockForm.data.items.length === 1 ? 'producto' : 'productos'}
                                        </span>
                                    </div>

                                    {restockForm.data.items.map((item, index) => {
                                        const selectedProduct = productos.find(
                                            (p) => String(p.id) === String(item.producto_id),
                                        );
                                        const currentStock = selectedProduct ? Number(selectedProduct.stock) : null;
                                        const isLowStock = currentStock !== null && currentStock <= 5;
                                        const isOutOfStock = currentStock !== null && currentStock <= 0;
                                        const qtyNum = parseFloat(item.cantidad) || 0;
                                        const costNum = parseFloat(item.costo_unitario) || 0;
                                        const lineSubtotal = qtyNum * costNum;

                                        return (
                                            <div
                                                key={index}
                                                className="relative rounded-2xl border border-slate-800 bg-slate-950/60 p-4 transition focus-within:border-amber-500/50"
                                            >
                                                <div className="mb-3 flex items-center justify-between">
                                                    <span className="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-amber-400">
                                                        <Boxes size={14} aria-hidden="true" />
                                                        Ítem #{index + 1}
                                                    </span>
                                                    {restockForm.data.items.length > 1 && (
                                                        <button
                                                            type="button"
                                                            onClick={() => removeRestockItemRow(index)}
                                                            className="flex items-center gap-1 text-xs text-rose-400 transition hover:text-rose-300"
                                                        >
                                                            <Trash2 size={13} aria-hidden="true" />
                                                            Quitar
                                                        </button>
                                                    )}
                                                </div>

                                                <div className="grid gap-4 sm:grid-cols-12">
                                                    {/* Selector de producto */}
                                                    <div className="sm:col-span-6">
                                                        <label className="mb-1 block text-xs font-medium text-slate-300">
                                                            Producto del inventario <span className="text-amber-400">*</span>
                                                        </label>
                                                        <select
                                                            value={item.producto_id}
                                                            onChange={(e) =>
                                                                updateRestockItemRow(index, 'producto_id', e.target.value)
                                                            }
                                                            required
                                                            className="w-full rounded-xl border border-slate-700 bg-slate-900 px-3 py-2.5 text-xs text-white outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                                        >
                                                            <option value="">-- Seleccionar producto --</option>
                                                            {supplierProducts.associated.length > 0 && (
                                                                <optgroup label="⭐ Productos habituales de este proveedor">
                                                                    {supplierProducts.associated.map((p) => (
                                                                        <option key={p.id} value={p.id}>
                                                                            {p.codigo_sku ? `[${p.codigo_sku}] ` : ''}
                                                                            {p.nombre} (Stock: {Number(p.stock)})
                                                                            {Number(p.stock) <= 0
                                                                                ? ' ⚠️ AGOTADO'
                                                                                : Number(p.stock) <= 5
                                                                                    ? ' ⚠️ BAJO'
                                                                                    : ''}
                                                                        </option>
                                                                    ))}
                                                                </optgroup>
                                                            )}
                                                            <optgroup label="📦 Otros productos del inventario">
                                                                {supplierProducts.others.map((p) => (
                                                                    <option key={p.id} value={p.id}>
                                                                        {p.codigo_sku ? `[${p.codigo_sku}] ` : ''}
                                                                        {p.nombre} (Stock: {Number(p.stock)})
                                                                        {Number(p.stock) <= 0
                                                                            ? ' ⚠️ AGOTADO'
                                                                            : Number(p.stock) <= 5
                                                                                ? ' ⚠️ BAJO'
                                                                                : ''}
                                                                    </option>
                                                                ))}
                                                            </optgroup>
                                                        </select>

                                                        {/* Badge de Stock Actual */}
                                                        {selectedProduct && (
                                                            <div className="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                                                <span
                                                                    className={`inline-flex items-center gap-1 rounded-md px-2 py-0.5 font-medium ${
                                                                        isOutOfStock
                                                                            ? 'border border-rose-500/30 bg-rose-500/15 text-rose-300'
                                                                            : isLowStock
                                                                                ? 'border border-amber-500/30 bg-amber-500/15 text-amber-300'
                                                                                : 'border border-emerald-500/30 bg-emerald-500/15 text-emerald-300'
                                                                    }`}
                                                                >
                                                                    {isOutOfStock || isLowStock ? (
                                                                        <AlertTriangle size={12} aria-hidden="true" />
                                                                    ) : (
                                                                        <CheckCircle2 size={12} aria-hidden="true" />
                                                                    )}
                                                                    Stock actual: {currentStock}{' '}
                                                                    {isOutOfStock
                                                                        ? '(Agotado)'
                                                                        : isLowStock
                                                                            ? '(Bajo)'
                                                                            : 'disponibles'}
                                                                </span>
                                                                {selectedProduct.categoria && (
                                                                    <span className="text-slate-400">
                                                                        {selectedProduct.categoria.nombre}
                                                                    </span>
                                                                )}
                                                            </div>
                                                        )}
                                                    </div>

                                                    {/* Cantidad recibida */}
                                                    <div className="sm:col-span-3">
                                                        <label className="mb-1 block text-xs font-medium text-slate-300">
                                                            Cantidad recibida <span className="text-amber-400">*</span>
                                                        </label>
                                                        <input
                                                            type="number"
                                                            step="any"
                                                            min="0.001"
                                                            max="9999999.999"
                                                            value={item.cantidad}
                                                            onChange={(e) =>
                                                                updateRestockItemRow(index, 'cantidad', e.target.value)
                                                            }
                                                            placeholder="Ej. 10"
                                                            required
                                                            className="w-full rounded-xl border border-slate-700 bg-slate-900 px-3 py-2 text-xs text-white outline-none transition placeholder:text-slate-600 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                                        />
                                                        {selectedProduct && qtyNum > 0 && (
                                                            <p className="mt-1 text-[11px] text-emerald-400">
                                                                Nuevo stock: {(currentStock + qtyNum).toLocaleString('es-CO', {
                                                                    maximumFractionDigits: 3,
                                                                })}
                                                            </p>
                                                        )}
                                                    </div>

                                                    {/* Costo unitario de compra */}
                                                    <div className="sm:col-span-3">
                                                        <label className="mb-1 block text-xs font-medium text-slate-300">
                                                            Costo unitario ($) <span className="text-slate-500">(opc.)</span>
                                                        </label>
                                                        <input
                                                            type="number"
                                                            step="0.01"
                                                            min="0"
                                                            value={item.costo_unitario}
                                                            onChange={(e) =>
                                                                updateRestockItemRow(index, 'costo_unitario', e.target.value)
                                                            }
                                                            placeholder="Ej. 15000"
                                                            className="w-full rounded-xl border border-slate-700 bg-slate-900 px-3 py-2 text-xs text-white outline-none transition placeholder:text-slate-600 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                                        />
                                                        {costNum > 0 && qtyNum > 0 && (
                                                            <p className="mt-1 text-[11px] text-slate-400">
                                                                Subtotal: ${' '}
                                                                {lineSubtotal.toLocaleString('es-CO', {
                                                                    minimumFractionDigits: 0,
                                                                    maximumFractionDigits: 2,
                                                                })}
                                                            </p>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}

                                    <button
                                        type="button"
                                        onClick={addRestockItemRow}
                                        className="inline-flex items-center gap-1.5 rounded-xl border border-dashed border-amber-500/40 bg-amber-500/5 px-3 py-2 text-xs font-semibold text-amber-300 transition hover:border-amber-500 hover:bg-amber-500/10 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
                                    >
                                        <Plus size={14} aria-hidden="true" />
                                        <span>+ Agregar otro producto a esta compra</span>
                                    </button>
                                </div>

                                {/* Observaciones / Notas */}
                                <div>
                                    <label className="mb-1.5 flex items-center gap-1.5 text-xs font-medium text-slate-300">
                                        <Receipt size={14} className="text-slate-500" aria-hidden="true" />
                                        <span>Número de Factura / Remisión u Observaciones (opcional)</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={restockForm.data.notas}
                                        onChange={(e) => restockForm.setData('notas', e.target.value)}
                                        placeholder="Ej. Factura #F-45892 - Entrega semanal de bodega"
                                        maxLength={1000}
                                        className="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-xs text-white outline-none transition placeholder:text-slate-600 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                    />
                                </div>

                                {/* Resumen del Reabastecimiento */}
                                <div className="grid grid-cols-2 gap-3 rounded-2xl border border-slate-800 bg-slate-950/80 p-4 sm:grid-cols-3">
                                    <div>
                                        <p className="text-[11px] font-medium uppercase tracking-wider text-slate-500">
                                            Productos
                                        </p>
                                        <p className="mt-1 text-base font-bold text-white">
                                            {restockSummary.validItemsCount}{' '}
                                            <span className="text-xs font-normal text-slate-400">
                                                {restockSummary.validItemsCount === 1 ? 'ítem' : 'ítems'}
                                            </span>
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-[11px] font-medium uppercase tracking-wider text-slate-500">
                                            Total Unidades
                                        </p>
                                        <p className="mt-1 text-base font-bold text-amber-400">
                                            +{restockSummary.totalUnits.toLocaleString('es-CO', {
                                                maximumFractionDigits: 3,
                                            })}{' '}
                                            <span className="text-xs font-normal text-amber-400/70">unidades</span>
                                        </p>
                                    </div>
                                    <div className="col-span-2 sm:col-span-1">
                                        <p className="text-[11px] font-medium uppercase tracking-wider text-slate-500">
                                            Costo Estimado
                                        </p>
                                        <p className="mt-1 text-base font-bold text-white">
                                            {restockSummary.totalCost > 0
                                                ? `$ ${restockSummary.totalCost.toLocaleString('es-CO', {
                                                      minimumFractionDigits: 0,
                                                      maximumFractionDigits: 2,
                                                  })}`
                                                : '—'}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Footer de Acciones del Modal */}
                            <footer className="flex items-center justify-end gap-3 border-t border-slate-800 bg-slate-900/90 p-4 sm:p-5">
                                <button
                                    type="button"
                                    onClick={closeRestockModal}
                                    disabled={restockForm.processing}
                                    className="rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-slate-800 disabled:opacity-50"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={
                                        restockForm.processing ||
                                        restockSummary.validItemsCount === 0 ||
                                        restockSummary.totalUnits <= 0
                                    }
                                    className="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-amber-950/30 transition hover:from-amber-400 hover:to-orange-400 focus:outline-none focus:ring-2 focus:ring-amber-400 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <PackagePlus size={18} aria-hidden="true" />
                                    <span>
                                        {restockForm.processing
                                            ? 'Ingresando mercancía...'
                                            : 'Confirmar Entrada al Inventario'}
                                    </span>
                                </button>
                            </footer>
                        </form>
                    </section>
                </div>
            )}
        </div>
    );
}

function FormField({ label, name, type = 'text', icon: Icon, value, onChange, error, required = false }) {
    return (
        <label className="block text-sm font-medium text-slate-200">
            {label}
            <span className="relative mt-1.5 block">
                {Icon && (
                    <Icon
                        size={16}
                        className="absolute top-1/2 left-3 -translate-y-1/2 text-slate-500"
                        aria-hidden="true"
                    />
                )}
                <input
                    type={type}
                    name={name}
                    value={value}
                    onChange={(event) => onChange(event.target.value)}
                    maxLength={name === 'direccion' ? 2000 : 255}
                    required={required}
                    autoFocus={name === 'nombre'}
                    className={`w-full rounded-xl border border-slate-700 bg-slate-950 py-2.5 pr-3 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 ${Icon ? 'pl-9' : 'pl-3'}`}
                />
            </span>
            {error && <span className="mt-1 block text-xs text-rose-300">{error}</span>}
        </label>
    );
}
