import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';

export default function PuntoVenta({ productos, clientes }) {
    const [carrito, setCarrito] = useState([]);

    const { data, setData, post, processing, reset, transform } = useForm({
        cliente_id: '',
        metodo_pago: 'Efectivo',
        items: [],
    });

    const agregarAlCarrito = (producto) => {
        const existe = carrito.find((item) => item.producto_id === producto.id);

        if (existe) {
            setCarrito(
                carrito.map((item) =>
                    item.producto_id === producto.id
                        ? { ...item, cantidad: item.cantidad + 1 }
                        : item,
                ),
            );
        } else {
            setCarrito([
                ...carrito,
                {
                    producto_id: producto.id,
                    nombre: producto.nombre,
                    precio: producto.precio_venta,
                    cantidad: 1,
                },
            ]);
        }
    };

    const registrarVenta = (event) => {
        event.preventDefault();

        transform((formData) => ({
            ...formData,
            items: carrito.map((item) => ({
                producto_id: item.producto_id,
                cantidad: item.cantidad,
            })),
        }));

        post('/pos', {
            onSuccess: () => {
                setCarrito([]);
                reset();
                alert('¡Venta realizada con éxito!');
            },
        });
    };

    const totalGeneral = carrito.reduce(
        (total, item) => total + item.precio * item.cantidad,
        0,
    );

    return (
        <div className="min-h-screen bg-slate-950 p-6 text-slate-100">
            <Head title="Punto de Venta - Ferretería" />
            <div className="mx-auto grid max-w-7xl grid-cols-1 gap-8 md:grid-cols-3">
                <div className="md:col-span-2">
                    <h1 className="mb-4 text-2xl font-bold text-amber-500">
                        🛒 POS - Selección de Artículos
                    </h1>
                    <div className="grid grid-cols-2 gap-4 lg:grid-cols-3">
                        {productos.map((producto) => (
                            <button
                                key={producto.id}
                                onClick={() => agregarAlCarrito(producto)}
                                className="rounded-xl border border-slate-800 bg-slate-900 p-4 text-left shadow transition hover:border-amber-500"
                            >
                                <h3 className="font-bold text-white">{producto.nombre}</h3>
                                <p className="text-xs text-slate-400">Stock: {producto.stock}</p>
                                <p className="mt-2 font-semibold text-amber-400">
                                    $ {Number(producto.precio_venta).toLocaleString()} COP
                                </p>
                            </button>
                        ))}
                    </div>
                </div>

                <div className="flex flex-col justify-between rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl">
                    <div>
                        <h2 className="mb-4 text-xl font-bold text-amber-400">Factura Actual</h2>
                        <div className="mb-6 max-h-60 space-y-3 overflow-y-auto">
                            {carrito.map((item) => (
                                <div
                                    key={item.producto_id}
                                    className="flex items-center justify-between border-b border-slate-800 pb-2 text-sm"
                                >
                                    <div>
                                        <p className="font-bold">{item.nombre}</p>
                                        <p className="text-xs text-slate-400">
                                            {item.cantidad} x ${' '}
                                            {Number(item.precio).toLocaleString()}
                                        </p>
                                    </div>
                                    <span className="font-semibold text-amber-400">
                                        ${' '}
                                        {Number(item.precio * item.cantidad).toLocaleString()}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>

                    <form onSubmit={registrarVenta}>
                        <div className="mb-4">
                            <label className="text-xs text-slate-400">Cliente</label>
                            <select
                                value={data.cliente_id}
                                onChange={(event) => setData('cliente_id', event.target.value)}
                                className="w-full rounded border border-slate-800 bg-slate-950 p-2 text-white"
                            >
                                <option value="">Cliente General</option>
                                {clientes.map((cliente) => (
                                    <option key={cliente.id} value={cliente.id}>
                                        {cliente.nombre}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="mb-6 flex items-center justify-between border-t border-slate-800 pt-4 text-lg font-bold">
                            <span>Total a Pagar:</span>
                            <span className="text-amber-400">
                                $ {Number(totalGeneral).toLocaleString()} COP
                            </span>
                        </div>

                        <button
                            type="submit"
                            disabled={carrito.length === 0 || processing}
                            className="w-full rounded-xl bg-amber-500 py-3 font-bold text-slate-950 transition hover:bg-amber-400 disabled:opacity-50"
                        >
                            Cobrar y Finalizar Venta
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
}
