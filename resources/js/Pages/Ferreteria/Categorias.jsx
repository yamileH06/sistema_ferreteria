import React, { useState } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import {
    ChevronDown,
    ChevronUp,
    Edit2,
    FolderOpen,
    Package,
    Plus,
    Search,
    Trash2,
    X,
} from 'lucide-react';
import Sidebar from '../../Components/Sidebar';

const currency = new Intl.NumberFormat('es-CO', {
    maximumFractionDigits: 0,
});

function formatPrice(value) {
    return `$ ${currency.format(Number(value))} COP`;
}

export default function Categorias({ categorias }) {
    const [expandedCategory, setExpandedCategory] = useState(null);
    const [searches, setSearches] = useState({});
    const [modalOpen, setModalOpen] = useState(false);
    const [editingCategory, setEditingCategory] = useState(null);
    const [productModalOpen, setProductModalOpen] = useState(false);
    const [productCategory, setProductCategory] = useState(null);
    const [editingProduct, setEditingProduct] = useState(null);
    const [successMessage, setSuccessMessage] = useState('');
    const { errors: pageErrors = {} } = usePage().props;
    const { data, setData, post, put, processing, errors, setError, clearErrors, reset } = useForm({
        nombre: '',
    });
    const {
        data: productData,
        setData: setProductData,
        post: postProduct,
        put: putProduct,
        processing: productProcessing,
        errors: productErrors,
        clearErrors: clearProductErrors,
        reset: resetProductForm,
    } = useForm({
        nombre: '',
        codigo_sku: '',
        stock: '',
        precio_compra: '',
        precio_venta: '',
    });

    const openCreateModal = () => {
        setEditingCategory(null);
        reset();
        clearErrors();
        setModalOpen(true);
    };

    const openEditModal = (category) => {
        setEditingCategory(category);
        setData('nombre', category.nombre);
        clearErrors();
        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        setEditingCategory(null);
        reset();
        clearErrors();
    };

    const saveCategory = (event) => {
        event.preventDefault();
        setSuccessMessage('');

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                setSuccessMessage(
                    editingCategory ? 'Categoría actualizada correctamente.' : 'Categoría creada correctamente.',
                );
                closeModal();
            },
        };

        if (editingCategory) {
            put(`/categorias/${editingCategory.id}`, options);
        } else {
            post('/categorias', options);
        }
    };

    const deleteCategory = (category) => {
        if (category.productos_count > 0) {
            setError(
                'categoria',
                'No se puede eliminar esta categoría porque tiene productos asociados.',
            );
            return;
        }

        if (!window.confirm(`¿Eliminar la categoría "${category.nombre}"?`)) {
            return;
        }

        setSuccessMessage('');
        clearErrors('categoria');
        router.delete(`/categorias/${category.id}`, {
            preserveScroll: true,
            onSuccess: () => setSuccessMessage('Categoría eliminada correctamente.'),
        });
    };

    const openCreateProductModal = (category) => {
        setProductCategory(category);
        setEditingProduct(null);
        resetProductForm();
        clearProductErrors();
        setProductModalOpen(true);
    };

    const openEditProductModal = (category, product) => {
        setProductCategory(category);
        setEditingProduct(product);
        setProductData({
            nombre: product.nombre,
            codigo_sku: product.codigo_sku,
            stock: product.stock,
            precio_compra: product.precio_compra,
            precio_venta: product.precio_venta,
        });
        clearProductErrors();
        setProductModalOpen(true);
    };

    const closeProductModal = () => {
        setProductModalOpen(false);
        setProductCategory(null);
        setEditingProduct(null);
        resetProductForm();
        clearProductErrors();
    };

    const saveProduct = (event) => {
        event.preventDefault();
        setSuccessMessage('');

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                setExpandedCategory(productCategory.id);
                setSuccessMessage(
                    editingProduct ? 'Producto actualizado correctamente.' : 'Producto agregado correctamente.',
                );
                closeProductModal();
            },
        };

        if (editingProduct) {
            putProduct(`/productos/${editingProduct.id}`, options);
        } else {
            postProduct(`/categorias/${productCategory.id}/productos`, options);
        }
    };

    const deleteProduct = (product) => {
        if (!window.confirm(`¿Eliminar el producto "${product.nombre}"?`)) {
            return;
        }

        setSuccessMessage('');
        router.delete(`/productos/${product.id}`, {
            preserveScroll: true,
            onSuccess: () => setSuccessMessage('Producto eliminado correctamente.'),
        });
    };

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 lg:flex">
            <Head title="Categorías - Ferretería" />
            <Sidebar />

            <main className="min-w-0 flex-1 p-4 lg:ml-64 lg:p-8">
                <div className="mx-auto max-w-6xl">
                    <header className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-sm font-medium text-amber-400">FERRETERÍA</p>
                            <h1 className="mt-1 text-2xl font-bold text-white">Categorías</h1>
                            <p className="mt-1 text-sm text-slate-400">
                                Organiza los productos y consulta su inventario por categoría.
                            </p>
                        </div>
                        <button
                            type="button"
                            onClick={openCreateModal}
                            className="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400"
                        >
                            <Plus size={18} aria-hidden="true" />
                            Nueva Categoría
                        </button>
                    </header>

                    {(successMessage || errors.categoria || pageErrors.categoria || pageErrors.producto) && (
                        <div
                            role="status"
                            className={`mb-5 rounded-xl border px-4 py-3 text-sm ${
                                successMessage
                                    ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300'
                                    : 'border-rose-500/30 bg-rose-500/10 text-rose-300'
                            }`}
                        >
                            {successMessage || errors.categoria || pageErrors.categoria || pageErrors.producto}
                        </div>
                    )}

                    <section className="space-y-3" aria-label="Listado de categorías">
                        {categorias.map((category) => {
                            const isExpanded = expandedCategory === category.id;
                            const normalizedSearch = (searches[category.id] || '')
                                .trim()
                                .toLocaleLowerCase();
                            const filteredProducts = category.productos.filter(
                                (product) =>
                                    product.nombre.toLocaleLowerCase().includes(normalizedSearch) ||
                                    product.codigo_sku.toLocaleLowerCase().includes(normalizedSearch),
                            );

                            return (
                                <article
                                    key={category.id}
                                    className="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 shadow-xl backdrop-blur-md"
                                >
                                    <div className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                                        <button
                                            type="button"
                                            aria-expanded={isExpanded}
                                            onClick={() =>
                                                setExpandedCategory(isExpanded ? null : category.id)
                                            }
                                            className="flex min-w-0 flex-1 items-center gap-3 text-left"
                                        >
                                            <span className="flex size-11 shrink-0 items-center justify-center rounded-xl border border-amber-400/20 bg-amber-500/10 text-amber-400">
                                                <FolderOpen size={20} aria-hidden="true" />
                                            </span>
                                            <span className="min-w-0 flex-1">
                                                <span className="block truncate font-semibold text-white">
                                                    {category.nombre}
                                                </span>
                                                <span className="mt-1 flex flex-wrap gap-2 text-xs text-slate-400">
                                                    <span className="rounded-full border border-slate-700 bg-slate-950/70 px-2.5 py-1">
                                                        {category.productos_count}{' '}
                                                        {category.productos_count === 1
                                                            ? 'producto'
                                                            : 'productos'}
                                                    </span>
                                                    <span className="rounded-full border border-slate-700 bg-slate-950/70 px-2.5 py-1">
                                                        {category.stock_disponible} unidades en bodega
                                                    </span>
                                                </span>
                                            </span>
                                            {isExpanded ? (
                                                <ChevronUp
                                                    className="shrink-0 text-amber-400"
                                                    size={20}
                                                    aria-hidden="true"
                                                />
                                            ) : (
                                                <ChevronDown
                                                    className="shrink-0 text-slate-400"
                                                    size={20}
                                                    aria-hidden="true"
                                                />
                                            )}
                                        </button>

                                        <div className="flex items-center gap-2 pl-14 sm:pl-0">
                                            {category.productos_count > 0 && (
                                                <span className="mr-1 text-xs text-slate-500">
                                                    Reasigna sus productos para eliminarla.
                                                </span>
                                            )}
                                            <button
                                                type="button"
                                                onClick={() => openEditModal(category)}
                                                aria-label={`Editar ${category.nombre}`}
                                                className="rounded-lg border border-slate-700 p-2 text-slate-300 transition hover:border-amber-500 hover:text-amber-300"
                                            >
                                                <Edit2 size={16} aria-hidden="true" />
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => deleteCategory(category)}
                                                disabled={category.productos_count > 0}
                                                aria-label={`Eliminar ${category.nombre}`}
                                                title={
                                                    category.productos_count > 0
                                                        ? 'Reasigna o elimina sus productos antes de borrar la categoría.'
                                                        : 'Eliminar categoría'
                                                }
                                                className="rounded-lg border border-slate-700 p-2 text-slate-300 transition hover:border-rose-500 hover:text-rose-300 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-700 disabled:hover:text-slate-300"
                                            >
                                                <Trash2 size={16} aria-hidden="true" />
                                            </button>
                                        </div>
                                    </div>

                                    {isExpanded && (
                                        <div className="border-t border-slate-800 bg-slate-950/40 p-4 sm:p-5">
                                            <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <label className="flex w-full max-w-xl items-center gap-2 rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 focus-within:border-amber-500">
                                                    <Search
                                                        size={17}
                                                        className="shrink-0 text-slate-500"
                                                        aria-hidden="true"
                                                    />
                                                    <input
                                                        type="search"
                                                        value={searches[category.id] || ''}
                                                        onChange={(event) =>
                                                            setSearches((current) => ({
                                                                ...current,
                                                                [category.id]: event.target.value,
                                                            }))
                                                        }
                                                        placeholder="Buscar por nombre o SKU..."
                                                        aria-label={`Buscar productos en ${category.nombre}`}
                                                        className="w-full border-0 bg-transparent text-sm text-white outline-none placeholder:text-slate-500 focus:ring-0"
                                                    />
                                                </label>
                                                <button
                                                    type="button"
                                                    onClick={() => openCreateProductModal(category)}
                                                    className="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400"
                                                >
                                                    <Plus size={17} aria-hidden="true" />
                                                    Agregar Producto
                                                </button>
                                            </div>

                                            <div className="overflow-x-auto">
                                                <table className="w-full min-w-[760px] text-left text-sm">
                                                    <thead className="text-xs uppercase tracking-wide text-slate-500">
                                                        <tr>
                                                            <th scope="col" className="px-3 py-3">
                                                                Producto
                                                            </th>
                                                            <th scope="col" className="px-3 py-3">
                                                                SKU
                                                            </th>
                                                            <th scope="col" className="px-3 py-3">
                                                                Stock
                                                            </th>
                                                            <th scope="col" className="px-3 py-3 text-right">
                                                                Precio de venta
                                                            </th>
                                                            <th scope="col" className="px-3 py-3 text-right">
                                                                Acciones
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody className="divide-y divide-slate-800">
                                                        {filteredProducts.map((product) => (
                                                            <tr key={product.id} className="text-slate-200">
                                                                <td className="px-3 py-3 font-medium">
                                                                    <span className="flex items-center gap-2">
                                                                        <Package
                                                                            size={16}
                                                                            className="text-amber-400"
                                                                            aria-hidden="true"
                                                                        />
                                                                        {product.nombre}
                                                                    </span>
                                                                </td>
                                                                <td className="px-3 py-3 text-slate-400">
                                                                    {product.codigo_sku}
                                                                </td>
                                                                <td className="px-3 py-3">
                                                                    {product.stock} unidades
                                                                </td>
                                                                <td className="px-3 py-3 text-right font-medium text-amber-300">
                                                                    {formatPrice(product.precio_venta)}
                                                                </td>
                                                                <td className="px-3 py-3">
                                                                    <div className="flex justify-end gap-2">
                                                                        <button
                                                                            type="button"
                                                                            onClick={() =>
                                                                                openEditProductModal(category, product)
                                                                            }
                                                                            aria-label={`Editar ${product.nombre}`}
                                                                            className="rounded-lg border border-slate-700 p-2 text-slate-300 transition hover:border-amber-500 hover:text-amber-300"
                                                                        >
                                                                            <Edit2 size={15} aria-hidden="true" />
                                                                        </button>
                                                                        <button
                                                                            type="button"
                                                                            onClick={() => deleteProduct(product)}
                                                                            aria-label={`Eliminar ${product.nombre}`}
                                                                            className="rounded-lg border border-slate-700 p-2 text-slate-300 transition hover:border-rose-500 hover:text-rose-300"
                                                                        >
                                                                            <Trash2 size={15} aria-hidden="true" />
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        ))}
                                                        {filteredProducts.length === 0 && (
                                                            <tr>
                                                                <td
                                                                    colSpan="5"
                                                                    className="px-3 py-8 text-center text-slate-500"
                                                                >
                                                                    {category.productos.length === 0
                                                                        ? 'Esta categoría todavía no tiene productos.'
                                                                        : 'No hay productos que coincidan con la búsqueda.'}
                                                                </td>
                                                            </tr>
                                                        )}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    )}
                                </article>
                            );
                        })}

                        {categorias.length === 0 && (
                            <div className="rounded-2xl border border-dashed border-slate-700 bg-slate-900/60 px-5 py-12 text-center">
                                <FolderOpen
                                    size={30}
                                    className="mx-auto text-slate-500"
                                    aria-hidden="true"
                                />
                                <p className="mt-3 font-medium text-white">Aún no hay categorías</p>
                                <p className="mt-1 text-sm text-slate-400">
                                    Crea una categoría para comenzar a organizar el inventario.
                                </p>
                            </div>
                        )}
                    </section>
                </div>
            </main>

            {modalOpen && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm"
                    role="presentation"
                    onMouseDown={(event) => {
                        if (event.target === event.currentTarget) closeModal();
                    }}
                >
                    <section
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="category-modal-title"
                        className="w-full max-w-md rounded-2xl border border-slate-700 bg-slate-900 p-5 shadow-2xl"
                    >
                        <header className="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <h2 id="category-modal-title" className="text-lg font-semibold text-white">
                                    {editingCategory ? 'Editar categoría' : 'Nueva categoría'}
                                </h2>
                                <p className="mt-1 text-sm text-slate-400">
                                    Escribe un nombre claro para identificar el grupo de productos.
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={closeModal}
                                aria-label="Cerrar formulario"
                                className="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-800 hover:text-white"
                            >
                                <X size={19} aria-hidden="true" />
                            </button>
                        </header>

                        <form onSubmit={saveCategory} className="space-y-4">
                            <div>
                                <label
                                    htmlFor="category-name"
                                    className="mb-1.5 block text-sm font-medium text-slate-200"
                                >
                                    Nombre
                                </label>
                                <input
                                    id="category-name"
                                    type="text"
                                    value={data.nombre}
                                    onChange={(event) => setData('nombre', event.target.value)}
                                    maxLength="255"
                                    autoFocus
                                    required
                                    className="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-white outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
                                    placeholder="Ej. Herramientas manuales"
                                />
                                {errors.nombre && (
                                    <p className="mt-1.5 text-sm text-rose-300">{errors.nombre}</p>
                                )}
                            </div>

                            <footer className="flex justify-end gap-2 pt-2">
                                <button
                                    type="button"
                                    onClick={closeModal}
                                    className="rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-slate-800"
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
                                        : editingCategory
                                          ? 'Guardar cambios'
                                          : 'Crear categoría'}
                                </button>
                            </footer>
                        </form>
                    </section>
                </div>
            )}

            {productModalOpen && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm"
                    role="presentation"
                    onMouseDown={(event) => {
                        if (event.target === event.currentTarget) closeProductModal();
                    }}
                >
                    <section
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="product-modal-title"
                        className="w-full max-w-lg rounded-2xl border border-slate-700 bg-slate-900 p-5 shadow-2xl"
                    >
                        <header className="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <h2 id="product-modal-title" className="text-lg font-semibold text-white">
                                    {editingProduct ? 'Editar producto' : 'Agregar producto'}
                                </h2>
                                <p className="mt-1 text-sm text-slate-400">
                                    Categoría: {productCategory?.nombre}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={closeProductModal}
                                aria-label="Cerrar formulario de producto"
                                className="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-800 hover:text-white"
                            >
                                <X size={19} aria-hidden="true" />
                            </button>
                        </header>

                        <form onSubmit={saveProduct} className="space-y-4">
                            {[
                                { key: 'nombre', label: 'Nombre del producto', type: 'text', step: undefined },
                                { key: 'codigo_sku', label: 'Código SKU', type: 'text', step: undefined },
                                { key: 'stock', label: 'Stock disponible', type: 'number', step: '0.001' },
                                { key: 'precio_compra', label: 'Precio de compra (COP)', type: 'number', step: '0.01' },
                                { key: 'precio_venta', label: 'Precio de venta (COP)', type: 'number', step: '0.01' },
                            ].map((field) => (
                                <div key={field.key}>
                                    <label
                                        htmlFor={`product-${field.key}`}
                                        className="mb-1.5 block text-sm font-medium text-slate-200"
                                    >
                                        {field.label}
                                    </label>
                                    <input
                                        id={`product-${field.key}`}
                                        type={field.type}
                                        step={field.step}
                                        min={field.type === 'number' ? '0' : undefined}
                                        value={productData[field.key]}
                                        onChange={(event) => setProductData(field.key, event.target.value)}
                                        maxLength={field.type === 'text' ? 255 : undefined}
                                        required
                                        autoFocus={field.key === 'nombre'}
                                        className="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-white outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
                                    />
                                    {productErrors[field.key] && (
                                        <p className="mt-1.5 text-sm text-rose-300">
                                            {productErrors[field.key]}
                                        </p>
                                    )}
                                </div>
                            ))}

                            <footer className="flex justify-end gap-2 pt-2">
                                <button
                                    type="button"
                                    onClick={closeProductModal}
                                    className="rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-slate-800"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={productProcessing}
                                    className="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 disabled:cursor-wait disabled:opacity-60"
                                >
                                    {productProcessing
                                        ? 'Guardando...'
                                        : editingProduct
                                          ? 'Guardar cambios'
                                          : 'Agregar producto'}
                                </button>
                            </footer>
                        </form>
                    </section>
                </div>
            )}
        </div>
    );
}
