<template>
    <div
        class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center px-4"
    >
        <div class="max-w-md w-full">
            <!-- Logo/Title -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">
                    Escaner Total
                </h1>
                <p class="text-slate-400">Sistema de Control de Accesos</p>
            </div>

            <!-- Card -->
            <div
                class="bg-slate-800 border border-slate-700 rounded-xl p-8 shadow-2xl"
            >
                <h2 class="text-2xl font-semibold text-white mb-6 text-center">
                    Iniciar Sesión
                </h2>

                <form @submit.prevent="submit">
                    <!-- Email -->
                    <div class="mb-4">
                        <label
                            for="email"
                            class="block text-sm font-medium text-slate-300 mb-2"
                        >
                            Correo Electrónico
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autofocus
                            autocomplete="email"
                            class="w-full px-4 py-3 rounded-lg border border-slate-600 bg-slate-900 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                            placeholder="tu@email.com"
                        />
                        <div
                            v-if="form.errors.email"
                            class="mt-2 text-sm text-red-400"
                        >
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-6">
                        <label
                            for="password"
                            class="block text-sm font-medium text-slate-300 mb-2"
                        >
                            Contraseña
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="w-full px-4 py-3 rounded-lg border border-slate-600 bg-slate-900 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                            placeholder="••••••••"
                        />
                        <div
                            v-if="form.errors.password"
                            class="mt-2 text-sm text-red-400"
                        >
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-6 flex items-center">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="w-4 h-4 rounded border-slate-600 bg-slate-900 text-green-600 focus:ring-green-500 focus:ring-2"
                        />
                        <label
                            for="remember"
                            class="ml-2 text-sm text-slate-300"
                        >
                            Recordarme
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg transition-colors shadow-lg shadow-green-500/20"
                    >
                        {{
                            form.processing
                                ? "Iniciando sesión..."
                                : "Iniciar Sesión"
                        }}
                    </button>
                </form>

                <!-- Demo Credentials Hint -->
                <div class="mt-6 pt-6 border-t border-slate-700">
                    <p class="text-xs text-slate-400 text-center">
                        Credenciales de prueba:
                        <br />
                        <code class="text-green-400">admin@local.test</code> /
                        <code class="text-green-400">demo12345</code>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <p class="mt-6 text-center text-sm text-slate-500">
                © 2025 Escaner Total. Todos los derechos reservados.
            </p>
        </div>
    </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";

defineOptions({
    layout: null, // Sin layout para login
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>
