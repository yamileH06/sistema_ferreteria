import React, { useEffect, useMemo, useState } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import {
    Minus,
    FileDown,
    Package,
    PackageOpen,
    Plus,
    Search,
    ShoppingCart,
    Trash2,
    UserRoundPlus,
    X,
} from 'lucide-react';
import Sidebar from '../../Components/Sidebar';
import ProductImage from '../../Components/ProductImage';
import { getProductImage } from '../../Utils/productImage';

const currency = new Intl.NumberFormat('es-CO', {
    maximumFractionDigits: 2,
});

const PORCENTAJE_DESCUENTO = 0.05;
const MONTO_MINIMO_DESCUENTO = 2_000_000;

function formatPrice(value) {
    return `$ ${currency.format(Number(value))} COP`;
}

function lineTotal(price, quantity) {
    const priceInCents = Math.round(Number(price) * 100);
    const quantityInMilli = Math.round(Number(quantity) * 1000);

    return Math.floor((priceInCents * quantityInMilli + 500) / 1000) / 100;
}

export default function Pos({ productos, categorias, clientes, clienteCreadoId = null }) {
    const [search, setSearch] = useState('');
    const [activeCategory, setActiveCategory] = useState('all');
    const [cart, setCart] = useState([]);
    const [clientId, setClientId] = useState('');
    const [paymentMethod, setPaymentMethod] = useState('Efectivo');
    const [cashReceived, setCashReceived] = useState('');
    const [processing, setProcessing] = useState(false);
    const [successMessage, setSuccessMessage] = useState('');
    const [newClientModalOpen, setNewClientModalOpen] = useState(false);
    const { errors = {}, ventaPdfUrl } = usePage().props;
    const {
        data: clientData,
        setData: setClientData,
        post: postClient,
        processing: clientProcessing,
        errors: clientErrors,
        clearErrors: clearClientErrors,
        reset: resetClientForm,
    } = useForm({
        nombre: '',
        cedula_nit: '',
        telefono: '',
    });

    useEffect(() => {
        if (clienteCreadoId) {
            setClientId(String(clienteCreadoId));
        }
    }, [clienteCreadoId]);

    const filteredProducts = useMemo(() => {
        const normalizedSearch = search.trim().toLocaleLowerCase();

        return productos.filter((product) => {
            const matchesCategory =
                activeCategory === 'all' ||
                String(product.categoria_id) === String(activeCategory);
            const matchesSearch =
                !normalizedSearch ||
                product.nombre.toLocaleLowerCase().includes(normalizedSearch) ||
                product.codigo_sku.toLocaleLowerCase().includes(normalizedSearch);

            return matchesCategory && matchesSearch;
        });
    }, [activeCategory, productos, search]);

    const subtotalInCents = cart.reduce(
        (total, item) => total + Math.round(lineTotal(item.precio_venta, item.cantidad) * 100),
        0,
    );
    const subtotal = subtotalInCents / 100;
    const descuentoInCents = subtotalInCents > MONTO_MINIMO_DESCUENTO * 100
        ? Math.round(subtotalInCents * PORCENTAJE_DESCUENTO)
        : 0;
    const descuento = descuentoInCents / 100;
    const total = (subtotalInCents - descuentoInCents) / 100;
    const cartCanBeCheckedOut = cart.length > 0 && cart.every((item) => {
        const quantity = Number(item.cantidad);
        const hasAtMostThreeDecimals = Number.isFinite(quantity)
            && Math.abs(quantity * 1000 - Math.round(quantity * 1000)) < 0.000001;

        return hasAtMostThreeDecimals && quantity > 0 && quantity <= Number(item.stock);
    });
    const cashDifference = cashReceived === '' ? null : Number(cashReceived) - total;

    const closeNewClientModal = () => {
        setNewClientModalOpen(false);
        resetClientForm();
        clearClientErrors();
    };

    const createClient = (event) => {
        event.preventDefault();
        setSuccessMessage('');
        postClient('/pos/clientes', {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                closeNewClientModal();
                setSuccessMessage('Cliente registrado y seleccionado en la orden.');
            },
        });
    };

    const addToCart = (product) => {
        setSuccessMessage('');
        setCart((currentCart) => {
            const existingItem = currentCart.find((item) => item.id === product.id);

            if (existingItem) {
                if (Number(existingItem.cantidad) >= Number(product.stock)) {
                    return currentCart;
                }

                return currentCart.map((item) =>
                    item.id === product.id
                        ? {
                            ...item,
                            cantidad: Math.min(
                                Number(product.stock),
                                Number(item.cantidad) + 1,
                            ),
                        }
                        : item,
                );
            }

            return [
                ...currentCart,
                { ...product, cantidad: Math.min(1, Number(product.stock)) },
            ];
        });
    };

    const changeQuantity = (productId, amount) => {
        setCart((currentCart) =>
            currentCart.map((item) => {
                if (item.id !== productId) {
                    return item;
                }

                const stockMilli = Math.floor(Number(item.stock) * 1000 + 0.000001);
                const currentMilli = Math.round(Number(item.cantidad) * 1000);
                const nextMilli = Math.min(stockMilli, Math.max(1, currentMilli + amount * 1000));

                return {
                    ...item,
                    cantidad: nextMilli / 1000,
                };
            }),
        );
    };

    const setCartQuantity = (productId, quantity) => {
        setCart((currentCart) =>
            currentCart.map((item) =>
                item.id === productId ? { ...item, cantidad: quantity } : item,
            ),
        );
    };

    const removeFromCart = (productId) => {
        setCart((currentCart) => currentCart.filter((item) => item.id !== productId));
    };

    const checkout = (event) => {
        event.preventDefault();
        setSuccessMessage('');
        setProcessing(true);

        router.post(
            '/pos/cobrar',
            {
                cliente_id: clientId || null,
                metodo_pago: paymentMethod,
                items: cart.map((item) => ({
                    producto_id: item.id,
                    cantidad: item.cantidad,
                })),
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setCart([]);
                    setClientId('');
                    setPaymentMethod('Efectivo');
                    setCashReceived('');
                    setSuccessMessage('La venta se registró correctamente.');
                },
                onFinish: () => setProcessing(false),
            },
        );
    };

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 lg:flex">
            <Head title="Punto de Venta - Ferretería" />
            <Sidebar />

            <main className="min-w-0 flex-1 p-4 lg:ml-64 lg:p-6">
                <div className="mx-auto grid max-w-[1600px] grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                    <section className="min-w-0">
                        <header className="mb-4">
                            <p className="text-xs font-semibold uppercase tracking-wider text-amber-400">
                                Ferretería El Constructor
                            </p>
                            <h1 className="mt-1 text-2xl font-bold text-white">
                                Punto de Venta (POS)
                            </h1>
                            <p className="mt-1 text-xs sm:text-sm text-slate-400">
                                Selecciona productos para agregarlos a la orden.
                            </p>
                        </header>

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
                                {categorias.map((category) => {
                                    const categoryCount = productos.filter(
                                        (product) =>
                                            String(product.categoria_id) === String(category.id),
                                    ).length;

                                    return (
                                        <button
                                            key={category.id}
                                            type="button"
                                            role="tab"
                                            aria-selected={
                                                String(activeCategory) === String(category.id)
                                            }
                                            onClick={() => setActiveCategory(category.id)}
                                            className={`shrink-0 rounded-full px-4 py-2 text-xs sm:text-sm font-semibold transition ${
                                                String(activeCategory) === String(category.id)
                                                    ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20'
                                                    : 'border border-slate-700 bg-slate-900/90 text-slate-300 hover:border-slate-500 hover:text-white'
                                            }`}
                                        >
                                            {category.nombre} ({categoryCount})
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
                                    value={search}
                                    onChange={(event) => setSearch(event.target.value)}
                                    placeholder="Buscar por nombre o SKU..."
                                    aria-label="Buscar productos por nombre o SKU"
                                    className="w-full rounded-xl border border-slate-700 bg-slate-900/90 py-2 pr-3 pl-9 text-xs sm:text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                />
                            </label>
                        </div>

                        {filteredProducts.length > 0 ? (
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                {filteredProducts.map((product) => {
                                    const cartItem = cart.find((item) => item.id === product.id);
                                    const isOutOfStock = product.stock <= 0;
                                    const reachedStock =
                                        cartItem && cartItem.cantidad >= product.stock;
                                    const categoryName =
                                        product.categoria?.nombre ?? 'Sin categoría';

                                    return (
                                        <article
                                            key={product.id}
                                            className="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 p-3.5 shadow-xl shadow-black/20 backdrop-blur-md transition hover:border-amber-500/50 hover:shadow-amber-500/10"
                                        >
                                            <div>
                                                {/* Contenedor superior para imagen con altura fija h-32 y esquinas redondeadas */}
                                                <div className="relative mb-3 h-32 w-full overflow-hidden rounded-xl border border-slate-800 bg-slate-950/80">
                                                    <ProductImage
                                                        product={product}
                                                        className="size-full"
                                                        imgClassName="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                    />

                                                    {/* Badge de Stock Flotante en la esquina superior izquierda */}
                                                    <span
                                                        className={`absolute top-2 left-2 z-10 rounded-md border px-2 py-0.5 text-[11px] font-bold backdrop-blur-md shadow-md ${
                                                            isOutOfStock
                                                                ? 'border-red-500/40 bg-red-950/80 text-red-300'
                                                                : product.stock <= 5
                                                                ? 'border-amber-500/40 bg-amber-950/80 text-amber-300'
                                                                : 'border-emerald-500/40 bg-slate-950/85 text-emerald-300'
                                                        }`}
                                                    >
                                                        Stock: {Number(product.stock).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 3 })}
                                                    </span>

                                                    {/* Badge de SKU en esquina superior derecha */}
                                                    <span className="absolute top-2 right-2 z-10 rounded-md border border-slate-700/80 bg-slate-950/85 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-amber-400/90 backdrop-blur-md shadow-sm">
                                                        {product.codigo_sku}
                                                    </span>
                                                </div>

                                                {/* Información central */}
                                                <p className="text-[11px] font-medium text-amber-400 truncate">
                                                    {categoryName}
                                                </p>
                                                <h2
                                                    className="mt-1 line-clamp-2 min-h-10 text-sm font-bold text-white transition-colors group-hover:text-amber-300 leading-snug"
                                                    title={product.nombre}
                                                >
                                                    {product.nombre}
                                                </h2>
                                            </div>

                                            {/* Pie de tarjeta: Precio en COP en color ámbar y botón Agregar */}
                                            <div className="mt-3 flex items-center justify-between gap-2 border-t border-slate-800/80 pt-3">
                                                <p className="font-bold text-sm sm:text-base text-amber-400 whitespace-nowrap">
                                                    {formatPrice(product.precio_venta)}
                                                </p>
                                                <button
                                                    type="button"
                                                    disabled={isOutOfStock || reachedStock}
                                                    onClick={() => addToCart(product)}
                                                    className="flex items-center gap-1.5 rounded-xl bg-amber-500 px-3.5 py-1.5 text-xs font-bold text-slate-950 transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:bg-slate-800 disabled:text-slate-500 shadow-md"
                                                >
                                                    <Plus size={15} aria-hidden="true" />
                                                    <span>{isOutOfStock ? 'Agotado' : reachedStock ? 'Límite' : 'Agregar'}</span>
                                                </button>
                                            </div>
                                        </article>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="rounded-2xl border border-dashed border-slate-700 bg-slate-900/50 px-6 py-14 text-center text-slate-400">
                                No hay productos que coincidan con la búsqueda.
                            </div>
                        )}
                    </section>

                    <aside className="h-fit rounded-2xl border border-slate-800 bg-slate-900/85 p-5 shadow-xl shadow-black/20 backdrop-blur-md xl:sticky xl:top-6">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                <ShoppingCart className="text-amber-400" size={20} aria-hidden="true" />
                                <h2 className="text-lg font-bold text-white">Orden Actual</h2>
                            </div>
                            <span className="rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-300">
                                {cart.reduce((count, item) => count + Number(item.cantidad || 0), 0)} artículos
                            </span>
                        </div>

                        <div className="mt-5 grid gap-4 sm:grid-cols-2 2xl:grid-cols-1">
                            <div>
                                <div className="mb-2 flex items-center justify-between gap-2">
                                    <label
                                        htmlFor="pos-client"
                                        className="text-xs font-medium text-slate-400"
                                    >
                                        Cliente
                                    </label>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            clearClientErrors();
                                            setNewClientModalOpen(true);
                                        }}
                                        aria-label="Registrar nuevo cliente"
                                        className="inline-flex items-center gap-1 rounded-lg border border-amber-500/30 bg-amber-500/10 px-2 py-1 text-xs font-semibold text-amber-300 transition hover:border-amber-400 hover:bg-amber-500/20"
                                    >
                                        <Plus size={14} aria-hidden="true" />
                                        Nuevo Cliente
                                    </button>
                                </div>
                                <select
                                    id="pos-client"
                                    value={clientId}
                                    onChange={(event) => setClientId(event.target.value)}
                                    className="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500"
                                >
                                    <option value="">Cliente General</option>
                                    {clientes.map((client) => (
                                        <option key={client.id} value={client.id}>
                                            {client.nombre}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <label className="block">
                                <span className="mb-2 block text-xs font-medium text-slate-400">
                                    Método de pago
                                </span>
                                <select
                                    value={paymentMethod}
                                    onChange={(event) => setPaymentMethod(event.target.value)}
                                    className="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none focus:border-amber-500"
                                >
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Tarjeta">Tarjeta</option>
                                    <option value="Transferencia">Transferencia</option>
                                </select>
                            </label>
                        </div>

                        {paymentMethod === 'Efectivo' && (
                            <div className="mt-4 rounded-xl border border-slate-800 bg-slate-950/60 p-3">
                                <label
                                    htmlFor="cash-received"
                                    className="mb-2 block text-xs font-medium text-slate-400"
                                >
                                    Efectivo recibido ($ COP)
                                </label>
                                <input
                                    id="cash-received"
                                    type="number"
                                    min="0"
                                    step="1"
                                    value={cashReceived}
                                    onChange={(event) => setCashReceived(event.target.value)}
                                    placeholder="Ej. 50000"
                                    className="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2.5 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-amber-500"
                                />
                                {cashDifference !== null && (
                                    <p
                                        role="status"
                                        className={`mt-2 text-sm font-semibold ${
                                            cashDifference >= 0 ? 'text-emerald-300' : 'text-rose-300'
                                        }`}
                                    >
                                        {cashDifference >= 0
                                            ? `Cambio a devolver: ${formatPrice(cashDifference)}`
                                            : `Falta por recibir: ${formatPrice(Math.abs(cashDifference))}`}
                                    </p>
                                )}
                            </div>
                        )}

                        <div className="mt-5 max-h-[34vh] space-y-3 overflow-y-auto pr-1">
                            {cart.length === 0 ? (
                                <div className="flex min-h-32 flex-col items-center justify-center rounded-xl border border-dashed border-slate-800 text-center">
                                    <ShoppingCart
                                        size={25}
                                        className="mb-2 text-slate-600"
                                        aria-hidden="true"
                                    />
                                    <p className="text-sm font-medium text-slate-400">
                                        El carrito está vacío
                                    </p>
                                    <p className="mt-1 text-xs text-slate-600">
                                        Agrega productos del catálogo.
                                    </p>
                                </div>
                            ) : (
                                cart.map((item) => (
                                    <div
                                        key={item.id}
                                        className="rounded-xl border border-slate-800 bg-slate-950/70 p-3"
                                    >
                                        <div className="flex items-start justify-between gap-3">
                                            <div className="min-w-0">
                                                <p className="truncate text-sm font-semibold text-white">
                                                    {item.nombre}
                                                </p>
                                                <p className="mt-1 text-xs text-slate-500">
                                                    {formatPrice(item.precio_venta)}
                                                </p>
                                            </div>
                                            <button
                                                type="button"
                                                onClick={() => removeFromCart(item.id)}
                                                aria-label={`Eliminar ${item.nombre} de la orden`}
                                                className="rounded-md p-1 text-slate-500 transition hover:bg-red-500/10 hover:text-red-400"
                                            >
                                                <Trash2 size={16} aria-hidden="true" />
                                            </button>
                                        </div>
                                        <div className="mt-3 flex items-center justify-between">
                                            <div className="flex items-center gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() => changeQuantity(item.id, -1)}
                                                    disabled={Number(item.cantidad) <= 0.001}
                                                    aria-label={`Disminuir cantidad de ${item.nombre}`}
                                                    className="rounded-md border border-slate-700 p-1 text-slate-300 transition hover:border-amber-500 disabled:cursor-not-allowed disabled:opacity-40"
                                                >
                                                    <Minus size={14} aria-hidden="true" />
                                                </button>
                                                <input
                                                    type="number"
                                                    min="0.001"
                                                    max={item.stock}
                                                    step="0.001"
                                                    value={item.cantidad}
                                                    onChange={(event) =>
                                                        setCartQuantity(item.id, event.target.value)
                                                    }
                                                    aria-label={`Cantidad de ${item.nombre}`}
                                                    className="w-24 rounded-md border border-slate-700 bg-slate-950 px-2 py-1 text-center text-sm font-semibold text-white outline-none focus:border-amber-500"
                                                />
                                                <button
                                                    type="button"
                                                    onClick={() => changeQuantity(item.id, 1)}
                                                    disabled={Number(item.cantidad) >= Number(item.stock)}
                                                    aria-label={`Aumentar cantidad de ${item.nombre}`}
                                                    className="rounded-md border border-slate-700 p-1 text-slate-300 transition hover:border-amber-500 disabled:cursor-not-allowed disabled:opacity-40"
                                                >
                                                    <Plus size={14} aria-hidden="true" />
                                                </button>
                                            </div>
                                            <span className="text-sm font-bold text-amber-300">
                                                {formatPrice(
                                                    lineTotal(item.precio_venta, item.cantidad),
                                                )}
                                            </span>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>

                        <div className="mt-5 space-y-3 border-t border-slate-800 pt-4">
                            {errors.items && (
                                <p role="alert" className="text-sm text-red-300">
                                    {errors.items}
                                </p>
                            )}
                            {errors.venta && (
                                <p role="alert" className="text-sm text-red-300">
                                    {errors.venta}
                                </p>
                            )}
                            {successMessage && (
                                <p role="status" className="text-sm text-emerald-300">
                                    {successMessage}
                                </p>
                            )}
                            {ventaPdfUrl && (
                                <a
                                    href={ventaPdfUrl}
                                    className="inline-flex items-center gap-2 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-sm font-medium text-emerald-300 transition hover:bg-emerald-500/20"
                                >
                                    <FileDown size={16} aria-hidden="true" />
                                    Descargar recibo PDF
                                </a>
                            )}
                            <div className="flex justify-between text-sm text-slate-400">
                                <span>Subtotal</span>
                                <span>{formatPrice(subtotal)}</span>
                            </div>
                            <div className={`flex justify-between text-sm ${descuento > 0 ? 'font-semibold text-emerald-300' : 'text-slate-500'}`}>
                                <span>Descuento (5% por compra &gt; $ 2.000.000)</span>
                                <span>- {formatPrice(descuento)}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="font-semibold text-white">TOTAL A PAGAR</span>
                                <span className="text-xl font-bold text-amber-400">
                                    {formatPrice(total)}
                                </span>
                            </div>
                        </div>

                        <button
                            type="button"
                            onClick={checkout}
                            disabled={!cartCanBeCheckedOut || processing}
                            className="mt-5 w-full rounded-xl bg-amber-500 px-4 py-3 font-bold text-slate-950 shadow-lg shadow-amber-950/20 transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:bg-slate-800 disabled:text-slate-500 disabled:shadow-none"
                        >
                            {processing ? 'Procesando venta...' : 'Cobrar y Finalizar Venta'}
                        </button>
                    </aside>
                </div>
            </main>

            {newClientModalOpen && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm"
                    role="presentation"
                    onMouseDown={(event) => {
                        if (event.target === event.currentTarget && !clientProcessing) {
                            closeNewClientModal();
                        }
                    }}
                >
                    <section
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="pos-client-modal-title"
                        className="w-full max-w-md rounded-2xl border border-slate-700 bg-slate-900 p-5 shadow-2xl"
                    >
                        <header className="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <div className="mb-3 flex size-10 items-center justify-center rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-300">
                                    <UserRoundPlus size={19} aria-hidden="true" />
                                </div>
                                <h2
                                    id="pos-client-modal-title"
                                    className="text-lg font-semibold text-white"
                                >
                                    Nuevo cliente
                                </h2>
                                <p className="mt-1 text-sm text-slate-400">
                                    El cliente quedará seleccionado sin vaciar la orden actual.
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={closeNewClientModal}
                                disabled={clientProcessing}
                                aria-label="Cerrar formulario de cliente"
                                className="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-800 hover:text-white disabled:opacity-50"
                            >
                                <X size={19} aria-hidden="true" />
                            </button>
                        </header>

                        <form onSubmit={createClient} className="space-y-4">
                            {[
                                { key: 'nombre', label: 'Nombre', required: true },
                                { key: 'cedula_nit', label: 'Cédula / NIT', required: true },
                                { key: 'telefono', label: 'Teléfono (opcional)', required: false },
                            ].map((field) => (
                                <div key={field.key}>
                                    <label
                                        htmlFor={`pos-client-${field.key}`}
                                        className="mb-1.5 block text-sm font-medium text-slate-200"
                                    >
                                        {field.label}
                                    </label>
                                    <input
                                        id={`pos-client-${field.key}`}
                                        type="text"
                                        value={clientData[field.key]}
                                        onChange={(event) =>
                                            setClientData(field.key, event.target.value)
                                        }
                                        maxLength={255}
                                        required={field.required}
                                        autoFocus={field.key === 'nombre'}
                                        className="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
                                    />
                                    {clientErrors[field.key] && (
                                        <p className="mt-1.5 text-sm text-rose-300">
                                            {clientErrors[field.key]}
                                        </p>
                                    )}
                                </div>
                            ))}
                            <p className="text-xs text-slate-500">
                                La base de datos requiere una cédula o NIT único para registrar clientes.
                            </p>

                            <footer className="flex justify-end gap-2 pt-2">
                                <button
                                    type="button"
                                    onClick={closeNewClientModal}
                                    disabled={clientProcessing}
                                    className="rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-slate-800 disabled:opacity-50"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={clientProcessing}
                                    className="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 disabled:cursor-wait disabled:opacity-60"
                                >
                                    {clientProcessing ? 'Guardando...' : 'Guardar cliente'}
                                </button>
                            </footer>
                        </form>
                    </section>
                </div>
            )}
        </div>
    );
}
