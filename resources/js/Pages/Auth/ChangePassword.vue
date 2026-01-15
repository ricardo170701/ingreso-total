<template>
    <div
        class="min-h-screen bg-gradient-to-br from-[#008c3a] via-[#006a2d] to-[#008c3a] flex items-center justify-center px-4 py-4"
    >
        <div class="max-w-md w-full">
            <!-- Header con Logo y Textos -->
            <div class="text-center mb-5">
                <!-- Logo Gobernación del Meta con contenedor elegante -->
                <div class="mb-4 flex justify-center">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 shadow-xl border border-white/20">
                        <img
                            src="/images/logo-gobernacion-meta.png"
                            alt="Gobernación del Meta"
                            class="h-20 w-auto object-contain drop-shadow-lg"
                            onerror="this.style.display='none'"
                        />
                    </div>
                </div>

                <!-- Título principal -->
                <h1 class="text-3xl font-bold text-white mb-2 tracking-tight">
                    Gobernación del Meta
                </h1>

                <!-- Separador decorativo -->
                <div class="flex items-center justify-center mb-2">
                    <div class="h-px w-12 bg-white/30"></div>
                    <div class="mx-2 w-1.5 h-1.5 rounded-full bg-white/40"></div>
                    <div class="h-px w-12 bg-white/30"></div>
                </div>

                <!-- Subtítulo del sistema -->
                <p class="text-white/95 text-lg font-medium mb-1">
                    Sistema de Control de Accesos
                </p>
            </div>

            <!-- Card -->
            <div
                class="bg-white/98 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-2xl"
            >
                <!-- Título del formulario con separador -->
                <div class="mb-5">
                    <h2 class="text-xl font-semibold text-[#008c3a] mb-2 text-center">
                        Cambio de Contraseña Obligatorio
                    </h2>
                    <div class="h-0.5 w-16 bg-gradient-to-r from-transparent via-[#008c3a] to-transparent mx-auto rounded-full"></div>
                    <p class="text-sm text-gray-600 mt-3 text-center">
                        Por seguridad, debes cambiar tu contraseña antes de continuar.
                    </p>
                </div>

                <!-- Mensaje de error -->
                <div
                    v-if="form.errors.password"
                    class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm"
                >
                    {{ form.errors.password }}
                </div>

                <form @submit.prevent="submit">
                    <!-- Nueva Contraseña -->
                    <div class="mb-3">
                        <label
                            for="password"
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                        >
                            Nueva Contraseña
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autofocus
                            autocomplete="new-password"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#008c3a] focus:border-transparent transition-all text-sm"
                            placeholder="Mínimo 8 caracteres"
                        />
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div class="mb-4">
                        <label
                            for="password_confirmation"
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                        >
                            Confirmar Nueva Contraseña
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autocomplete="new-password"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#008c3a] focus:border-transparent transition-all text-sm"
                            placeholder="Repite la contraseña"
                        />
                        <div
                            v-if="form.errors.password_confirmation"
                            class="mt-1.5 text-xs text-red-400"
                        >
                            {{ form.errors.password_confirmation }}
                        </div>
                    </div>

                    <!-- Ver Contraseña -->
                    <div class="mb-4 flex items-center">
                        <input
                            id="showPassword"
                            v-model="showPassword"
                            type="checkbox"
                            class="w-4 h-4 rounded border-gray-300 bg-white text-[#008c3a] focus:ring-[#008c3a] focus:ring-2"
                        />
                        <label
                            for="showPassword"
                            class="ml-2 text-sm text-gray-700"
                        >
                            Ver contraseña
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-2.5 px-4 bg-[#008c3a] hover:bg-[#006a2d] disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg transition-colors shadow-lg shadow-[#008c3a]/20 text-sm"
                    >
                        {{
                            form.processing
                                ? "Cambiando contraseña..."
                                : "Cambiar Contraseña"
                        }}
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="mt-4 pt-3 border-t border-white/20">
                <p class="text-center text-xs text-white/90 font-light">
                    © 2025 Gobernación del Meta
                </p>
                <p class="text-center text-xs text-white/70 mt-0.5">
                    Todos los derechos reservados
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";

defineOptions({
    layout: null, // Sin layout para cambio de contraseña
});

const showPassword = ref(false);

const form = useForm({
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("password.change"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>
