import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Hammer, LockKeyhole, Mail, ShieldCheck } from 'lucide-react';

export default function Login() {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
    });

    const submit = (event) => {
        event.preventDefault();
        post('/login', {
            onFinish: () => reset('password'),
        });
    };

    return (
        <main className="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-950 px-4 py-10 text-slate-100 sm:px-6">
            <Head title="Iniciar sesión - ControlPro" />

            <div
                aria-hidden="true"
                className="pointer-events-none absolute -top-32 left-1/2 size-[28rem] -translate-x-1/2 rounded-full bg-amber-500/10 blur-3xl"
            />
            <div
                aria-hidden="true"
                className="pointer-events-none absolute -bottom-40 -right-32 size-96 rounded-full bg-orange-600/10 blur-3xl"
            />

            <section className="relative w-full max-w-md rounded-3xl border border-slate-700/80 bg-slate-900/80 p-6 shadow-2xl shadow-black/40 backdrop-blur-xl sm:p-8">
                <header className="mb-8 text-center">
                    <div className="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl border border-amber-400/30 bg-amber-500/10 text-amber-400">
                        <Hammer size={27} strokeWidth={1.8} aria-hidden="true" />
                    </div>
                    <p className="text-xs font-semibold uppercase tracking-[0.24em] text-amber-400">
                        Sistema de ferretería
                    </p>
                    <h1 className="mt-2 text-3xl font-bold tracking-tight text-white">ControlPro</h1>
                    <p className="mt-2 text-sm text-slate-400">
                        Ferretería El Constructor · Ipiales, Nariño
                    </p>
                </header>

                <form onSubmit={submit} className="space-y-5">
                    <div>
                        <label
                            htmlFor="login-email"
                            className="mb-2 block text-sm font-medium text-slate-200"
                        >
                            Usuario (correo electrónico)
                        </label>
                        <div className="flex items-center gap-3 rounded-xl border border-slate-700 bg-slate-950/80 px-3.5 transition focus-within:border-amber-500 focus-within:ring-2 focus-within:ring-amber-500/20">
                            <Mail size={17} className="shrink-0 text-slate-500" aria-hidden="true" />
                            <input
                                id="login-email"
                                type="email"
                                name="email"
                                autoComplete="username"
                                autoFocus
                                required
                                value={data.email}
                                onChange={(event) => setData('email', event.target.value)}
                                placeholder="nombre@correo.com"
                                aria-invalid={Boolean(errors.email)}
                                aria-describedby={errors.email ? 'login-email-error' : undefined}
                                className="w-full border-0 bg-transparent py-3 text-sm text-white outline-none placeholder:text-slate-600 focus:ring-0"
                            />
                        </div>
                    </div>

                    <div>
                        <label
                            htmlFor="login-password"
                            className="mb-2 block text-sm font-medium text-slate-200"
                        >
                            Contraseña
                        </label>
                        <div className="flex items-center gap-3 rounded-xl border border-slate-700 bg-slate-950/80 px-3.5 transition focus-within:border-amber-500 focus-within:ring-2 focus-within:ring-amber-500/20">
                            <LockKeyhole
                                size={17}
                                className="shrink-0 text-slate-500"
                                aria-hidden="true"
                            />
                            <input
                                id="login-password"
                                type="password"
                                name="password"
                                autoComplete="current-password"
                                required
                                value={data.password}
                                onChange={(event) => setData('password', event.target.value)}
                                placeholder="Ingresa tu contraseña"
                                aria-invalid={Boolean(errors.password)}
                                aria-describedby={errors.password ? 'login-password-error' : undefined}
                                className="w-full border-0 bg-transparent py-3 text-sm text-white outline-none placeholder:text-slate-600 focus:ring-0"
                            />
                        </div>
                    </div>

                    {(errors.email || errors.password) && (
                        <p
                            id={errors.email ? 'login-email-error' : 'login-password-error'}
                            role="alert"
                            className="rounded-xl border border-rose-500/30 bg-rose-500/10 px-3.5 py-3 text-sm text-rose-300"
                        >
                            {errors.email || errors.password}
                        </p>
                    )}

                    <button
                        type="submit"
                        disabled={processing}
                        className="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-amber-950/20 transition hover:from-amber-400 hover:to-orange-400 focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-2 focus:ring-offset-slate-900 disabled:cursor-wait disabled:opacity-60"
                    >
                        <ShieldCheck size={18} aria-hidden="true" />
                        {processing ? 'Verificando...' : 'Iniciar sesión'}
                    </button>
                </form>

                <footer className="mt-7 border-t border-slate-800 pt-5 text-center">
                    <p className="text-xs text-slate-500">
                        Acceso seguro para el equipo de El Constructor
                    </p>
                </footer>
            </section>
        </main>
    );
}
