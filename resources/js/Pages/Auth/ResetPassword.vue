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
                    Restablecer Contraseña
                </h2>
                <p class="mt-2 text-center text-sm text-slate-600">
                    Ingresa tu nueva contraseña
                </p>
            </div>

            <!-- Formulario -->
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <input type="hidden" v-model="form.token" />
                <input type="hidden" v-model="form.email" />

                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">
                            Correo Electrónico
                        </label>
                        <input
                            id="email"
                            :value="email"
                            type="email"
                            disabled
                            class="appearance-none relative block w-full px-3 py-2 border border-slate-200 bg-slate-50 text-slate-500 rounded-lg cursor-not-allowed sm:text-sm"
                        />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">
                            Nueva Contraseña
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            class="appearance-none relative block w-full px-3 py-2 border border-slate-300 placeholder-slate-500 text-slate-900 rounded-lg focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                            placeholder="Mínimo 8 caracteres"
                        />
                        <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">
                            Confirmar Nueva Contraseña
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            class="appearance-none relative block w-full px-3 py-2 border border-slate-300 placeholder-slate-500 text-slate-900 rounded-lg focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                            placeholder="Confirma tu contraseña"
                        />
                        <div v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password_confirmation }}
                        </div>
                    </div>
                </div>

                <div v-if="form.errors.token" class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                    {{ form.errors.token }}
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Restableciendo...</span>
                        <span v-else>Restablecer Contraseña</span>
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

const props = defineProps({
    token: String,
    email: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("password.update"), {
        onSuccess: () => {
            // El redirect se maneja en el controlador
        },
    });
};
</script>

