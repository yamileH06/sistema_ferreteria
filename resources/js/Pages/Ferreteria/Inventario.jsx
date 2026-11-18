import React, { useMemo, useRef, useState } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import {
    Image as ImageIcon,
    Package,
    Pencil,
    Search,
    Trash2,
    Upload,
    Wrench,
    X,
} from 'lucide-react';
import { getProductImage } from '../../Utils/productImage';
import ProductImage from '../../Components/ProductImage';

export default function Inventario({ productos, categorias, proveedores }) {
    const [busqueda, setBusqueda] = useState('');
    const [activeCategory, setActiveCategory] = useState('all');
    const [editingProduct, setEditingProduct] = useState(null);
    const [createPreview, setCreatePreview] = useState(null);
    const [editPreview, setEditPreview] = useState(null);
    const createFileInputRef = useRef(null);
    const editFileInputRef = useRef(null);

    const { errors: pageErrors = {} } = usePage().props;

    const {
        data,
        setData,
        post,
        processing,
        errors: createErrors,
        reset,
    } = useForm({
        nombre: '',
        codigo_sku: '',
        categoria_id: '',
        proveedor_id: '',
        stock: '',
        precio_compra: '',
        precio_venta: '',
        imagen: null,
    });

    const {
        data: editData,
        setData: setEditData,
        processing: editProcessing,
        errors: editErrors,
        clearErrors: clearEditErrors,
        reset: resetEditForm,
    } = useForm({
        nombre: '',
        codigo_sku: '',
        stock: '',
        precio_compra: '',
        precio_venta: '',
        categoria_id: '',
        proveedor_id: '',
        imagen: null,
        imagen_url: '',
        quitar_imagen: false,
    });

    const handleCreateImageChange = (event) => {
        const file = event.target.files?.[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('La imagen no debe superar los 2MB.');
                if (createFileInputRef.current) createFileInputRef.current.value = '';
                return;
            }
            setData('imagen', file);
            if (createPreview) URL.revokeObjectURL(createPreview);
            setCreatePreview(URL.createObjectURL(file));
        }
    };

    const clearCreateImage = () => {
        setData('imagen', null);
        if (createPreview) {
            URL.revokeObjectURL(createPreview);
            setCreatePreview(null);
        }
        if (createFileInputRef.current) {
            createFileInputRef.current.value = '';
        }
    };

    const handleEditImageChange = (event) => {
        const file = event.target.files?.[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('La imagen no debe superar los 2MB.');
                if (editFileInputRef.current) editFileInputRef.current.value = '';
                return;
            }
            setEditData('imagen', file);
            if (editPreview) URL.revokeObjectURL(editPreview);
            setEditPreview(URL.createObjectURL(file));
        }
    };

    const clearEditImage = () => {
        setEditData('imagen', null);
        if (editPreview) {
            URL.revokeObjectURL(editPreview);
            setEditPreview(null);
        }
        if (editFileInputRef.current) {
            editFileInputRef.current.value = '';
        }
    };

    const submit = (event) => {
        event.preventDefault();
        post('/inventario', {
            forceFormData: true,
            onSuccess: () => {
                reset();
                clearCreateImage();
            },
        });
    };

    const productosFiltrados = useMemo(() => {
        const normalizedSearch = busqueda.trim().toLowerCase();

        return productos.filter((producto) => {
            const matchesCategory =
                activeCategory === 'all' ||
                String(producto.categoria_id) === String(activeCategory);

            const matchesSearch =
                !normalizedSearch ||
                producto.nombre.toLowerCase().includes(normalizedSearch) ||
                producto.codigo_sku.toLowerCase().includes(normalizedSearch);

            return matchesCategory && matchesSearch;
        });
    }, [activeCategory, busqueda, productos]);

    const openEditModal = (producto) => {
        setEditingProduct(producto);
        setEditData({
            nombre: producto.nombre,
            codigo_sku: producto.codigo_sku,
            stock: producto.stock,
            precio_compra: producto.precio_compra,
            precio_venta: producto.precio_venta,
            categoria_id: producto.categoria_id || '',
            proveedor_id: producto.proveedor_id || '',
            imagen: null,
            imagen_url: (producto.imagen && producto.imagen.startsWith('http')) ? producto.imagen : '',
            quitar_imagen: false,
        });
        clearEditErrors();
        if (editPreview) {
            URL.revokeObjectURL(editPreview);
            setEditPreview(null);
        }
    };

    const closeEditModal = () => {
        setEditingProduct(null);
        resetEditForm();
        clearEditErrors();
        clearEditImage();
    };

    const submitEdit = (event) => {
        event.preventDefault();

        // Enviar con _method: 'PUT' usando router.post para soportar carga de archivos en Laravel
        router.post(`/productos/${editingProduct.id}`, {
            ...editData,
            _method: 'PUT',
        }, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: closeEditModal,
        });
    };

    const deleteProduct = (producto) => {
        if (!window.confirm(`¿Eliminar el producto "${producto.nombre}"?`)) {
            return;
        }

        router.delete(`/productos/${producto.id}`, {
            preserveScroll: true,
        });
    };

    return (
        <div className="min-h-screen bg-slate-950 p-6 text-slate-100">
            <Head title="Inventario - Ferretería El Constructor" />

            <div className="mx-auto max-w-7xl">
                <header className="mb-8 flex flex-col gap-4 border-b border-slate-800 pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-wider text-amber-500">
                            🛠️ Ferretería El Constructor - Inventario
                        </h1>
                        <p className="text-sm text-slate-400">
                            Control general de catálogo, existencias, precios en COP e imágenes
                        </p>
                    </div>
                    <a
                        href="/pos"
                        className="self-start rounded-xl bg-amber-500 px-4 py-2.5 font-bold text-slate-950 transition hover:bg-amber-400 sm:self-auto"
                    >
                        Ir al Punto de Venta (POS) ➡️
                    </a>
                </header>

                {/* Formulario para registrar nuevo producto */}
                <div className="mb-10 rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow-xl backdrop-blur-md">
                    <h2 className="mb-4 text-xl font-semibold text-amber-400">
                        Registrar Nuevo Producto
                    </h2>
                    <form onSubmit={submit} className="space-y-4">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div>
                                <input
                                    type="text"
                                    placeholder="Nombre del producto"
                                    value={data.nombre}
                                    onChange={(event) => setData('nombre', event.target.value)}
                                    className="w-full rounded-lg border border-slate-800 bg-slate-950 p-2.5 text-white outline-none focus:border-amber-500"
                                    required
                                />
                                {createErrors.nombre && (
                                    <p className="mt-1 text-xs text-rose-400">{createErrors.nombre}</p>
                                )}
                            </div>

                            <div>
                                <input
                                    type="text"
                                    placeholder="Código SKU"
                                    value={data.codigo_sku}
                                    onChange={(event) => setData('codigo_sku', event.target.value)}
                                    className="w-full rounded-lg border border-slate-800 bg-slate-950 p-2.5 text-white outline-none focus:border-amber-500"
                                    required
                                />
                                {createErrors.codigo_sku && (
                                    <p className="mt-1 text-xs text-rose-400">{createErrors.codigo_sku}</p>
                                )}
                            </div>

                            <div>
                                <select
                                    value={data.categoria_id}
                                    onChange={(event) => setData('categoria_id', event.target.value)}
                                    className="w-full rounded-lg border border-slate-800 bg-slate-950 p-2.5 text-white outline-none focus:border-amber-500"
                                    required
                                >
                                    <option value="">Seleccione Categoría</option>
                                    {categorias.map((categoria) => (
                                        <option key={categoria.id} value={categoria.id}>
                                            {categoria.nombre}
                                        </option>
                                    ))}
                                </select>
                                {createErrors.categoria_id && (
                                    <p className="mt-1 text-xs text-rose-400">{createErrors.categoria_id}</p>
                                )}
                            </div>

                            <div>
                                <select
                                    value={data.proveedor_id}
                                    onChange={(event) => setData('proveedor_id', event.target.value)}
                                    className="w-full rounded-lg border border-slate-800 bg-slate-950 p-2.5 text-white outline-none focus:border-amber-500"
                                >
                                    <option value="">Seleccione Proveedor</option>
                                    {proveedores.map((proveedor) => (
                                        <option key={proveedor.id} value={proveedor.id}>
                                            {proveedor.nombre}
                                        </option>
                                    ))}
                                </select>
                                {createErrors.proveedor_id && (
                                    <p className="mt-1 text-xs text-rose-400">{createErrors.proveedor_id}</p>
                                )}
                            </div>

                            <div>
                                <input
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    placeholder="Stock inicial"
                                    value={data.stock}
                                    onChange={(event) => setData('stock', event.target.value)}
                                    className="w-full rounded-lg border border-slate-800 bg-slate-950 p-2.5 text-white outline-none focus:border-amber-500"
                                    required
                                />
                                {createErrors.stock && (
                                    <p className="mt-1 text-xs text-rose-400">{createErrors.stock}</p>
                                )}
                            </div>

                            <div>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="Precio Compra ($ COP)"
                                    value={data.precio_compra}
                                    onChange={(event) => setData('precio_compra', event.target.value)}
                                    className="w-full rounded-lg border border-slate-800 bg-slate-950 p-2.5 text-white outline-none focus:border-amber-500"
                                    required
                                />
                                {createErrors.precio_compra && (
                                    <p className="mt-1 text-xs text-rose-400">{createErrors.precio_compra}</p>
                                )}
                            </div>

                            <div>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="Precio Venta ($ COP)"
                                    value={data.precio_venta}
                                    onChange={(event) => setData('precio_venta', event.target.value)}
                                    className="w-full rounded-lg border border-slate-800 bg-slate-950 p-2.5 text-white outline-none focus:border-amber-500"
                                    required
                                />
                                {createErrors.precio_venta && (
                                    <p className="mt-1 text-xs text-rose-400">{createErrors.precio_venta}</p>
                                )}
                            </div>

                            {/* Campo de imagen para nuevo producto */}
                            <div className="flex items-center gap-2">
                                <label className="relative flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg border border-dashed border-slate-700 bg-slate-950 p-2.5 text-xs text-slate-300 transition hover:border-amber-500 hover:text-amber-300">
                                    <Upload size={16} aria-hidden="true" />
                                    <span className="truncate">
                                        {data.imagen ? data.imagen.name : 'Subir Imagen (2MB)'}
                                    </span>
                                    <input
                                        ref={createFileInputRef}
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp,image/jpg"
                                        onChange={handleCreateImageChange}
                                        className="sr-only"
                                    />
                                </label>
                                {createPreview && (
                                    <div className="relative size-10 shrink-0 overflow-hidden rounded-lg border border-slate-700">
                                        <img
                                            src={createPreview}
                                            alt="Previsualización"
                                            className="size-full object-cover"
                                        />
                                        <button
                                            type="button"
                                            onClick={clearCreateImage}
                                            className="absolute top-0 right-0 rounded-bl bg-rose-600 p-0.5 text-white hover:bg-rose-500"
                                            title="Quitar imagen"
                                        >
                                            <X size={10} />
                                        </button>
                                    </div>
                                )}
                            </div>
                        </div>

                        {createErrors.imagen && (
                            <p className="text-xs text-rose-400">{createErrors.imagen}</p>
                        )}

                        <div className="flex justify-end pt-2">
                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-xl bg-amber-500 px-6 py-2.5 font-bold text-slate-950 transition hover:bg-amber-400 disabled:cursor-wait disabled:opacity-60"
                            >
                                {processing ? 'Guardando...' : 'Guardar Producto'}
                            </button>
                        </div>
                    </form>
                </div>

                {/* Filtros de categorías superiores en píldoras horizontales y buscador rápido alineado a la derecha */}
                <div className="mb-6 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div
                        role="tablist"
                        aria-label="Filtrar por categoría"
                        className="flex min-w-0 flex-1 gap-2 overflow-x-auto pb-1"
                    >
                        <button
                            type="button"
                            role="tab"
                            aria-selected={activeCategory === 'all'}
                            onClick={() => setActiveCategory('all')}
                            className={`shrink-0 rounded-full px-4 py-2 text-xs sm:text-sm font-semibold transition ${
                                activeCategory === 'all'
                                    ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20'
                                    : 'border border-slate-700 bg-slate-900/90 text-slate-300 hover:border-slate-500 hover:text-white'
                            }`}
                        >
                            Todos ({productos.length})
                        </button>
                        {categorias.map((categoria) => {
                            const categoryCount = productos.filter(
                                (producto) =>
                                    String(producto.categoria_id) === String(categoria.id),
                            ).length;

                            return (
                                <button
                                    key={categoria.id}
                                    type="button"
                                    role="tab"
                                    aria-selected={
                                        String(activeCategory) === String(categoria.id)
                                    }
                                    onClick={() => setActiveCategory(categoria.id)}
                                    className={`shrink-0 rounded-full px-4 py-2 text-xs sm:text-sm font-semibold transition ${
                                        String(activeCategory) === String(categoria.id)
                                            ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20'
                                            : 'border border-slate-700 bg-slate-900/90 text-slate-300 hover:border-slate-500 hover:text-white'
                                    }`}
                                >
                                    {categoria.nombre} ({categoryCount})
                                </button>
                            );
                        })}
                    </div>

                    <label className="relative block w-full shrink-0 lg:w-72">
                        <Search
                            className="absolute top-1/2 left-3 -translate-y-1/2 text-slate-500"
                            size={17}
                            aria-hidden="true"
                        />
                        <input
                            type="search"
                            value={busqueda}
                            onChange={(event) => setBusqueda(event.target.value)}
                            placeholder="Buscar por nombre o SKU..."
                            aria-label="Buscar productos por nombre o SKU"
                            className="w-full rounded-xl border border-slate-700 bg-slate-900/90 py-2 pr-3 pl-9 text-xs sm:text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                        />
                    </label>
                </div>

                {pageErrors.producto && (
                    <p
                        role="alert"
                        className="mb-6 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-300"
                    >
                        {pageErrors.producto}
                    </p>
                )}

                {/* Grilla moderna de tarjetas de productos */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {productosFiltrados.map((producto) => (
                        <article
                            key={producto.id}
                            className="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 p-3.5 shadow-xl shadow-black/20 backdrop-blur-md transition hover:border-amber-500/50 hover:shadow-amber-500/10"
                        >
                            <div>
                                {/* Contenedor superior para imagen con altura fija h-32 y esquinas redondeadas */}
                                <div className="relative mb-3 h-32 w-full overflow-hidden rounded-xl border border-slate-800 bg-slate-950/80">
                                    <ProductImage
                                        product={producto}
                                        className="size-full"
                                        imgClassName="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    />

                                    {/* Badge de Stock Flotante en la esquina superior izquierda */}
                                    <span
                                        className={`absolute top-2 left-2 z-10 rounded-md border px-2 py-0.5 text-[11px] font-bold backdrop-blur-md shadow-md ${
                                            producto.stock <= 0
                                                ? 'border-red-500/40 bg-red-950/80 text-red-300'
                                                : producto.stock <= 5
                                                ? 'border-amber-500/40 bg-amber-950/80 text-amber-300'
                                                : 'border-emerald-500/40 bg-slate-950/85 text-emerald-300'
                                        }`}
                                    >
                                        Stock: {Number(producto.stock).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 3 })}
                                    </span>

                                    {/* Badge de SKU en esquina superior derecha */}
                                    <span className="absolute top-2 right-2 z-10 rounded-md border border-slate-700/80 bg-slate-950/85 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-amber-400/90 backdrop-blur-md shadow-sm">
                                        {producto.codigo_sku}
                                    </span>
                                </div>

                                {/* Información central */}
                                <div className="flex items-center gap-1.5 text-[11px] font-medium text-amber-400 truncate">
                                    <span>{producto.categoria?.nombre ?? 'Sin categoría'}</span>
                                    {producto.proveedor?.nombre && (
                                        <>
                                            <span className="text-slate-600">•</span>
                                            <span className="text-slate-400 truncate">
                                                {producto.proveedor.nombre}
                                            </span>
                                        </>
                                    )}
                                </div>
                                <h3
                                    className="mt-1 line-clamp-2 min-h-10 text-sm font-bold text-white transition-colors group-hover:text-amber-300 leading-snug"
                                    title={producto.nombre}
                                >
                                    {producto.nombre}
                                </h3>
                            </div>

                            {/* Pie de tarjeta: Precio en COP en color ámbar y botones Editar / Eliminar */}
                            <div className="mt-3 flex items-center justify-between gap-2 border-t border-slate-800/80 pt-3">
                                <p className="font-bold text-sm sm:text-base text-amber-400 whitespace-nowrap">
                                    $ {Number(producto.precio_venta).toLocaleString('es-CO')} COP
                                </p>
                                <div className="flex items-center gap-1.5">
                                    <button
                                        type="button"
                                        onClick={() => openEditModal(producto)}
                                        aria-label={`Editar ${producto.nombre}`}
                                        className="inline-flex items-center gap-1 rounded-lg border border-slate-700 bg-slate-800/80 px-2.5 py-1.5 text-xs font-medium text-slate-200 transition hover:border-amber-500 hover:text-amber-300"
                                    >
                                        <Pencil size={13} aria-hidden="true" />
                                        <span>Editar</span>
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => deleteProduct(producto)}
                                        aria-label={`Eliminar ${producto.nombre}`}
                                        className="inline-flex items-center gap-1 rounded-lg border border-slate-700 bg-slate-800/80 px-2.5 py-1.5 text-xs font-medium text-slate-300 transition hover:border-rose-500 hover:text-rose-300"
                                    >
                                        <Trash2 size={13} aria-hidden="true" />
                                        <span>Eliminar</span>
                                    </button>
                                </div>
                            </div>
                        </article>
                    ))}
                </div>

                {productosFiltrados.length === 0 && (
                    <div className="rounded-2xl border border-dashed border-slate-800 bg-slate-900/40 p-12 text-center text-slate-500">
                        <Wrench size={36} className="mx-auto mb-2 text-slate-600" />
                        <p className="text-sm font-medium text-slate-400">No se encontraron productos</p>
                        <p className="mt-1 text-xs">Intenta con otro término de búsqueda o agrega un nuevo producto.</p>
                    </div>
                )}
            </div>

            {/* Modal para Editar Producto */}
            {editingProduct && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm"
                    role="presentation"
                    onMouseDown={(event) => {
                        if (event.target === event.currentTarget) closeEditModal();
                    }}
                >
                    <section
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="inventory-edit-title"
                        className="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-700 bg-slate-900 p-6 shadow-2xl"
                    >
                        <header className="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <h2 id="inventory-edit-title" className="text-xl font-semibold text-white">
                                    Editar producto
                                </h2>
                                <p className="mt-1 text-sm text-slate-400">
                                    Actualiza los datos, la foto, el stock o los precios.
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={closeEditModal}
                                aria-label="Cerrar formulario de edición"
                                className="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-800 hover:text-white"
                            >
                                <X size={19} aria-hidden="true" />
                            </button>
                        </header>

                        <form onSubmit={submitEdit} className="space-y-4">
                            {/* Previsualización y carga de imagen en edición */}
                            <div className="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
                                <div className="mb-2.5 flex items-center justify-between">
                                    <label className="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                                        Fotografía del Producto
                                    </label>
                                    {(editingProduct.imagen || editData.imagen_url || editPreview) && !editData.quitar_imagen && (
                                        <button
                                            type="button"
                                            onClick={() => {
                                                setEditData('quitar_imagen', true);
                                                setEditData('imagen', null);
                                                setEditData('imagen_url', '');
                                                if (editPreview) {
                                                    URL.revokeObjectURL(editPreview);
                                                    setEditPreview(null);
                                                }
                                                if (editFileInputRef.current) editFileInputRef.current.value = '';
                                            }}
                                            className="text-xs font-medium text-rose-400 transition hover:text-rose-300 underline"
                                        >
                                            Quitar fotografía (usar icono técnico)
                                        </button>
                                    )}
                                    {editData.quitar_imagen && (
                                        <button
                                            type="button"
                                            onClick={() => setEditData('quitar_imagen', false)}
                                            className="text-xs font-medium text-amber-400 hover:text-amber-300 underline"
                                        >
                                            Restaurar fotografía anterior
                                        </button>
                                    )}
                                </div>

                                <div className="flex items-start gap-4">
                                    <div className="relative size-20 shrink-0 overflow-hidden rounded-2xl border border-slate-700 bg-slate-900 shadow-inner">
                                        {editPreview ? (
                                            <img
                                                src={editPreview}
                                                alt="Preview"
                                                className="size-full object-cover"
                                            />
                                        ) : !editData.quitar_imagen && editData.imagen_url && editData.imagen_url.trim() !== '' ? (
                                            <img
                                                src={editData.imagen_url}
                                                alt="Preview URL"
                                                className="size-full object-cover"
                                                onError={(e) => { e.currentTarget.style.display = 'none'; }}
                                            />
                                        ) : !editData.quitar_imagen && editingProduct.imagen ? (
                                            <ProductImage
                                                product={editingProduct}
                                                className="size-full"
                                                imgClassName="size-full object-cover"
                                                compact
                                            />
                                        ) : (
                                            <ProductImage
                                                product={{
                                                    ...editingProduct,
                                                    categoria: categorias.find((c) => String(c.id) === String(editData.categoria_id))?.nombre || editingProduct.categoria,
                                                }}
                                                className="size-full"
                                                compact
                                            />
                                        )}
                                    </div>

                                    <div className="flex-1 space-y-2">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <label className="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-medium text-slate-200 transition hover:border-amber-500 hover:text-amber-300">
                                                <Upload size={14} />
                                                <span>{editPreview ? 'Cambiar archivo' : 'Subir archivo (2MB)'}</span>
                                                <input
                                                    ref={editFileInputRef}
                                                    type="file"
                                                    accept="image/jpeg,image/png,image/webp,image/jpg"
                                                    onChange={handleEditImageChange}
                                                    className="sr-only"
                                                />
                                            </label>

                                            {editPreview && (
                                                <button
                                                    type="button"
                                                    onClick={clearEditImage}
                                                    className="text-xs text-rose-400 underline hover:text-rose-300"
                                                >
                                                    Descartar archivo
                                                </button>
                                            )}
                                        </div>

                                        <div>
                                            <input
                                                type="url"
                                                value={editData.imagen_url || ''}
                                                onChange={(e) => {
                                                    setEditData('imagen_url', e.target.value);
                                                    if (editData.quitar_imagen) setEditData('quitar_imagen', false);
                                                }}
                                                placeholder="O pega una URL directa de imagen (https://...)"
                                                className="w-full rounded-xl border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs text-white placeholder:text-slate-500 focus:border-amber-500 focus:outline-none"
                                            />
                                        </div>

                                        <p className="text-[11px] text-slate-500">
                                            {editData.quitar_imagen
                                                ? 'Se guardará sin foto y se mostrará el icono técnico representativo.'
                                                : 'Sube un archivo, pega un enlace directo o déjalo vacío para usar el icono técnico oficial.'}
                                        </p>
                                    </div>
                                </div>

                                {editErrors.imagen && (
                                    <p className="mt-1.5 text-xs text-rose-400">{editErrors.imagen}</p>
                                )}
                            </div>

                            {[
                                { key: 'nombre', label: 'Nombre del producto', type: 'text', step: undefined },
                                { key: 'codigo_sku', label: 'Código SKU', type: 'text', step: undefined },
                                { key: 'stock', label: 'Stock disponible', type: 'number', step: '0.001' },
                                { key: 'precio_compra', label: 'Precio de compra (COP)', type: 'number', step: '0.01' },
                                { key: 'precio_venta', label: 'Precio de venta (COP)', type: 'number', step: '0.01' },
                            ].map((field) => (
                                <div key={field.key}>
                                    <label
                                        htmlFor={`inventory-edit-${field.key}`}
                                        className="mb-1.5 block text-sm font-medium text-slate-200"
                                    >
                                        {field.label}
                                    </label>
                                    <input
                                        id={`inventory-edit-${field.key}`}
                                        type={field.type}
                                        step={field.step}
                                        min={field.type === 'number' ? '0' : undefined}
                                        maxLength={field.type === 'text' ? 255 : undefined}
                                        required
                                        value={editData[field.key]}
                                        onChange={(event) => setEditData(field.key, event.target.value)}
                                        className="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-white outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
                                    />
                                    {editErrors[field.key] && (
                                        <p className="mt-1.5 text-sm text-rose-300">
                                            {editErrors[field.key]}
                                        </p>
                                    )}
                                </div>
                            ))}

                            <footer className="flex justify-end gap-2 pt-2">
                                <button
                                    type="button"
                                    onClick={closeEditModal}
                                    className="rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-slate-800"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={editProcessing}
                                    className="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 disabled:cursor-wait disabled:opacity-60"
                                >
                                    {editProcessing ? 'Guardando...' : 'Guardar cambios'}
                                </button>
                            </footer>
                        </form>
                    </section>
                </div>
            )}
        </div>
    );
}
