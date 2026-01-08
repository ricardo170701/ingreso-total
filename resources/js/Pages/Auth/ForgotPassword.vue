<template>
    <div class="min-h-screen bg-slate-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo y título -->
            <div>
                <div class="flex justify-center">
                    <img
                        src="/images/logo-gobernacion-meta.png"
                        alt="Gobernación del Meta"
                        class="h-20 w-auto"
                        onerror="this.style.display='none'"
                    />
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-slate-900">
                    Recuperar Contraseña
                </h2>
                <p class="mt-2 text-center text-sm text-slate-600">
                    Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña
                </p>
            </div>

            <!-- Mensajes -->
            <div
                v-if="$page.props.flash?.success"
                class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"
            >
                {{ $page.props.flash.success }}
            </div>

            <!-- Formulario -->
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">
                        Correo Electrónico
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        class="appearance-none relative block w-full px-3 py-2 border border-slate-300 placeholder-slate-500 text-slate-900 rounded-lg focus:outline-none focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm"
                        placeholder="tu@correo.com"
                    />
                    <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Enviando...</span>
                        <span v-else>Enviar Enlace de Recuperación</span>
                    </button>
                </div>

                <div class="text-center">
                    <Link
                        :href="route('login')"
                        class="text-sm text-green-600 hover:text-green-500"
                    >
                        ← Volver al inicio de sesión
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from "@inertiajs/vue3";

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

