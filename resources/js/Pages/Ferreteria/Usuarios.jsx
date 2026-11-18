import React from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { UserPlus } from 'lucide-react';
import Sidebar from '../../Components/Sidebar';

const roles = [
    { value: 'administrador', label: 'Administrador' },
    { value: 'cajero', label: 'Cajero' },
    { value: 'bodeguero', label: 'Bodeguero' },
];

export default function Usuarios({ usuarios }) {
    const { props } = usePage();
    const currentUserId = props.auth?.user?.id;
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        rol: 'cajero',
    });

    const submit = (event) => {
        event.preventDefault();
        post('/usuarios', {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    const updateRole = (user, rol) => {
        if (rol === user.rol) {
            return;
        }

        router.put(`/usuarios/${user.id}/rol`, { rol }, { preserveScroll: true });
    };

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 lg:flex">
            <Head title="Gestión de usuarios - ControlPro" />
            <Sidebar />

            <main className="min-w-0 flex-1 p-4 lg:ml-64 lg:p-8">
                <div className="mx-auto max-w-6xl space-y-6">
                    <header>
                        <p className="text-sm font-medium text-amber-400">ADMINISTRACIÓN</p>
                        <h1 className="mt-1 text-2xl font-bold text-white">Gestión de usuarios</h1>
                        <p className="mt-1 text-sm text-slate-400">
                            Registra empleados y administra sus permisos por rol.
                        </p>
                    </header>

                    <section className="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-xl backdrop-blur-md sm:p-6">
                        <div className="mb-5 flex items-center gap-3">
                            <span className="flex size-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                                <UserPlus size={19} aria-hidden="true" />
                            </span>
                            <div>
                                <h2 className="font-semibold text-white">Registrar empleado</h2>
                                <p className="text-xs text-slate-400">Asigna credenciales y un rol inicial.</p>
                            </div>
                        </div>

                        <form onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
                            <Field
                                label="Nombre completo"
                                value={data.name}
                                onChange={(value) => setData('name', value)}
                                error={errors.name}
                                autoComplete="name"
                            />
                            <Field
                                label="Correo electrónico"
                                type="email"
                                value={data.email}
                                onChange={(value) => setData('email', value)}
                                error={errors.email}
                                autoComplete="email"
                            />
                            <Field
                                label="Contraseña (mínimo 8 caracteres)"
                                type="password"
                                value={data.password}
                                onChange={(value) => setData('password', value)}
                                error={errors.password}
                                autoComplete="new-password"
                            />
                            <Field
                                label="Confirmar contraseña"
                                type="password"
                                value={data.password_confirmation}
                                onChange={(value) => setData('password_confirmation', value)}
                                error={errors.password_confirmation}
                                autoComplete="new-password"
                            />
                            <label className="block text-sm font-medium text-slate-300">
                                Rol inicial
                                <select
                                    value={data.rol}
                                    onChange={(event) => setData('rol', event.target.value)}
                                    className="mt-2 w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-3 text-sm text-white outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
                                >
                                    {roles.map((role) => (
                                        <option key={role.value} value={role.value}>{role.label}</option>
                                    ))}
                                </select>
                                {errors.rol && <span className="mt-1 block text-xs text-rose-300">{errors.rol}</span>}
                            </label>

                            <div className="flex items-end">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full rounded-xl bg-amber-500 px-4 py-3 text-sm font-bold text-slate-950 transition hover:bg-amber-400 disabled:cursor-wait disabled:opacity-60"
                                >
                                    {processing ? 'Guardando...' : 'Crear usuario'}
                                </button>
                            </div>
                        </form>
                    </section>

                    <section className="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 shadow-xl backdrop-blur-md">
                        <div className="border-b border-slate-800 px-5 py-4">
                            <h2 className="font-semibold text-white">Empleados registrados</h2>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[620px] text-left text-sm">
                                <thead className="bg-slate-950/60 text-xs uppercase tracking-wide text-slate-400">
                                    <tr>
                                        <th className="px-5 py-3">Nombre</th>
                                        <th className="px-5 py-3">Correo</th>
                                        <th className="px-5 py-3">Rol</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-800">
                                    {usuarios.map((user) => (
                                        <tr key={user.id}>
                                            <td className="px-5 py-4 font-medium text-slate-100">
                                                {user.name}{user.id === currentUserId && (
                                                    <span className="ml-2 text-xs text-slate-500">(Tu cuenta)</span>
                                                )}
                                            </td>
                                            <td className="px-5 py-4 text-slate-300">{user.email}</td>
                                            <td className="px-5 py-4">
                                                <select
                                                    aria-label={`Rol de ${user.name}`}
                                                    value={user.rol}
                                                    disabled={user.id === currentUserId}
                                                    onChange={(event) => updateRole(user, event.target.value)}
                                                    className="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-200 outline-none focus:border-amber-500 disabled:cursor-not-allowed disabled:opacity-50"
                                                >
                                                    {roles.map((role) => (
                                                        <option key={role.value} value={role.value}>{role.label}</option>
                                                    ))}
                                                </select>
                                            </td>
                                        </tr>
                                    ))}
                                    {usuarios.length === 0 && (
                                        <tr>
                                            <td colSpan="3" className="px-5 py-10 text-center text-slate-500">
                                                No hay usuarios registrados.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    );
}

function Field({ label, type = 'text', value, onChange, error, autoComplete }) {
    return (
        <label className="block text-sm font-medium text-slate-300">
            {label}
            <input
                type={type}
                value={value}
                onChange={(event) => onChange(event.target.value)}
                autoComplete={autoComplete}
                className="mt-2 w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-3 text-sm text-white outline-none placeholder:text-slate-600 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
            />
            {error && <span className="mt-1 block text-xs text-rose-300">{error}</span>}
        </label>
    );
}
