import React from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import {
    BarChart3,
    Boxes,
    ClipboardList,
    House,
    LogIn,
    LogOut,
    Package,
    ShoppingCart,
    Tags,
    Truck,
    Users,
    Wrench,
} from 'lucide-react';

const navigation = [
    { label: 'Inicio', href: '/', icon: House },
    { label: 'Inventario', href: '/inventario', icon: Package, roles: ['administrador', 'bodeguero'] },
    { label: 'Punto de Venta', href: '/pos', icon: ShoppingCart, roles: ['administrador', 'cajero'] },
    { label: 'Categorías', href: '/categorias', icon: Tags, roles: ['administrador', 'bodeguero'] },
    { label: 'Proveedores', href: '/proveedores', icon: Truck, roles: ['administrador', 'bodeguero'] },
    { label: 'Clientes', href: '/clientes', icon: Users, roles: ['administrador', 'cajero'] },
    { label: 'Historial de Ventas', href: '/ventas', icon: ClipboardList, roles: ['administrador', 'cajero'] },
    { label: 'Reportes', href: '/reportes', icon: BarChart3, roles: ['administrador', 'cajero'] },
    { label: 'Usuarios', href: '/usuarios', icon: Users, roles: ['administrador'] },
];

export default function Sidebar() {
    const { url, props } = usePage();
    const user = props.auth?.user;
    const flash = props.flash ?? {};
    const currentPath = url.split('?')[0];

    // Si no hay usuario logueado (p. ej. en la pantalla de inicio en Render),
    // mostramos los menús principales para acceso y conocimiento del sistema.
    // Si el usuario está autenticado, filtramos según su rol.
    const visibleNavigation = navigation.filter(({ roles }) => {
        if (!user) {
            return true;
        }
        if (user.rol === 'administrador') {
            return true;
        }
        return !roles || roles.includes(user.rol);
    });

    return (
        <aside className="z-40 w-full shrink-0 border-b border-slate-700/70 bg-slate-900/95 backdrop-blur-md lg:fixed lg:inset-y-0 lg:left-0 lg:flex lg:w-64 lg:flex-col lg:border-r lg:border-b-0">
            <div className="flex items-center gap-3 px-5 py-5">
                <div className="flex size-10 items-center justify-center rounded-xl border border-amber-400/30 bg-amber-500/10 text-amber-400">
                    <Wrench size={20} aria-hidden="true" />
                </div>
                <div className="min-w-0">
                    <p className="font-semibold tracking-wide text-white truncate">Ferretería El Constructor</p>
                    <p className="text-xs text-slate-400">Panel de administración</p>
                </div>
            </div>

            <nav
                aria-label="Navegación principal"
                className="flex gap-2 overflow-x-auto px-3 pb-3 lg:flex-1 lg:flex-col lg:overflow-y-auto lg:px-3 lg:py-4"
            >
                {visibleNavigation.map(({ label, href, icon: Icon }) => {
                    const isActive =
                        currentPath === href ||
                        (href !== '/' && currentPath.startsWith(`${href}/`));

                    return (
                        <Link
                            key={href}
                            href={href}
                            aria-current={isActive ? 'page' : undefined}
                            className={`flex shrink-0 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition ${
                                isActive
                                    ? 'bg-amber-500 text-slate-950 shadow-lg shadow-amber-950/30'
                                    : 'text-slate-300 hover:bg-slate-800 hover:text-white'
                            }`}
                        >
                            <Icon size={18} aria-hidden="true" />
                            <span>{label}</span>
                        </Link>
                    );
                })}
            </nav>

            <div className="border-t border-slate-800 px-4 py-4">
                {flash.error && (
                    <p role="alert" className="mb-3 rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-xs text-rose-300">
                        {flash.error}
                    </p>
                )}
                {flash.success && (
                    <p role="status" className="mb-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-xs text-emerald-300">
                        {flash.success}
                    </p>
                )}
                {user ? (
                    <>
                        <div className="mb-3 flex items-center gap-2 text-xs text-slate-400">
                            <Boxes size={15} aria-hidden="true" />
                            <span className="min-w-0 truncate">{user.name} · {user.rol}</span>
                        </div>
                        <button
                            type="button"
                            onClick={() => router.post('/logout')}
                            className="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white"
                        >
                            <LogOut size={16} aria-hidden="true" />
                            Cerrar sesión
                        </button>
                    </>
                ) : (
                    <Link
                        href="/login"
                        className="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-3 py-2.5 text-center text-sm font-semibold text-slate-950 shadow-lg shadow-amber-950/30 transition hover:bg-amber-400"
                    >
                        <LogIn size={16} aria-hidden="true" />
                        <span>Iniciar sesión</span>
                    </Link>
                )}
            </div>
        </aside>
    );
}
