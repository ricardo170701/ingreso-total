<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-semibold text-slate-900 dark:text-slate-100"
                    >
                        Mi Perfil
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                        Gestiona tu información personal y configuración de
                        cuenta
                    </p>
                </div>
            </div>

            <!-- Mensaje de éxito (notificación temporal) -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 translate-x-full"
                enter-to-class="opacity-100 translate-x-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-x-0"
                leave-to-class="opacity-0 translate-x-full"
            >
                <div
                    v-if="showSuccessMessage"
                    class="fixed top-4 right-4 z-50 max-w-md"
                >
                    <div
                        class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4 shadow-lg flex items-center gap-3"
                    >
                        <div class="shrink-0">
                            <span class="text-2xl">✅</span>
                        </div>
                        <div class="flex-1">
                            <p
                                class="text-sm font-medium text-green-800 dark:text-green-200"
                            >
                                Perfil actualizado exitosamente
                            </p>
                            <p
                                class="text-xs text-green-700 dark:text-green-300 mt-1"
                            >
                                Los cambios se han aplicado a tu perfil
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="showSuccessMessage = false"
                            class="shrink-0 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 transition-colors"
                            aria-label="Cerrar"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </Transition>

            <div
                v-if="$page.props.flash?.error"
                class="p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200"
            >
                {{ $page.props.flash.error }}
            </div>

            <!-- Formulario de perfil -->
            <div
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 shadow-sm"
            >
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Foto de perfil -->
                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                        >
                            Foto de Perfil
                        </label>
                        <div class="flex items-start gap-6">
                            <!-- Foto actual -->
                            <div class="shrink-0">
                                <div
                                    class="w-32 h-32 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600"
                                >
                                    <img
                                        v-if="fotoPreview || user.foto_perfil"
                                        :src="
                                            fotoPreview ||
                                            storageUrl(user.foto_perfil)
                                        "
                                        alt="Foto de perfil"
                                        class="w-full h-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="w-full h-full flex items-center justify-center text-slate-400 dark:text-slate-500 text-4xl"
                                    >
                                        👤
                                    </div>
                                </div>
                            </div>
                            <!-- Input de archivo -->
                            <div class="flex-1">
                                <input
                                    type="file"
                                    ref="fotoInput"
                                    @change="handleFotoChange"
                                    accept="image/jpeg,image/png,image/jpg,image/gif"
                                    class="block w-full text-sm text-slate-700 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-50 dark:file:bg-slate-700 file:text-slate-700 dark:file:text-slate-200 hover:file:bg-slate-100 dark:hover:file:bg-slate-600"
                                />
                                <p
                                    class="mt-2 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Formatos permitidos: JPEG, PNG, JPG, GIF.
                                    Tamaño máximo: 2MB
                                </p>
                                <div
                                    v-if="form.errors.foto"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.foto }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos personales -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormField label="Nombre" :error="form.errors.nombre">
                            <input
                                v-model="form.nombre"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent"
                                placeholder="Tu nombre"
                            />
                        </FormField>

                        <FormField
                            label="Apellido"
                            :error="form.errors.apellido"
                        >
                            <input
                                v-model="form.apellido"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent"
                                placeholder="Tu apellido"
                            />
                        </FormField>
                    </div>

                    <!-- Email (solo lectura) -->
                    <FormField label="Email">
                        <input
                            :value="user.email"
                            type="email"
                            disabled
                            class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-500 dark:text-slate-400 cursor-not-allowed"
                        />
                        <p
                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                        >
                            El email no puede ser modificado desde aquí
                        </p>
                    </FormField>

                    <!-- Información adicional (solo lectura) -->
                    <div
                        class="pt-6 border-t border-slate-200 dark:border-slate-700"
                    >
                        <h3
                            class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-4"
                        >
                            Información de Cuenta
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400 mb-1"
                                >
                                    Tipo de vinculación
                                </p>
                                <p
                                    class="text-sm text-slate-900 dark:text-slate-100 font-medium"
                                >
                                    {{
                                        formatTipoVinculacion(
                                            user.role?.name
                                        ) || "-"
                                    }}
                                </p>
                            </div>
                            <div v-if="user.cargo">
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400 mb-1"
                                >
                                    Rol (permisos)
                                </p>
                                <p
                                    class="text-sm text-slate-900 dark:text-slate-100 font-medium"
                                >
                                    {{ user.cargo?.name || "-" }}
                                </p>
                            </div>
                            <div v-if="user.cargo_texto">
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400 mb-1"
                                >
                                    Cargo (registro)
                                </p>
                                <p
                                    class="text-sm text-slate-900 dark:text-slate-100 font-medium"
                                >
                                    {{ user.cargo_texto }}
                                </p>
                            </div>
                            <div v-if="user.gerencia">
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400 mb-1"
                                >
                                    Secretaría / Gerencia
                                </p>
                                <p
                                    class="text-sm text-slate-900 dark:text-slate-100 font-medium"
                                >
                                    <span class="font-medium">{{
                                        user.gerencia.secretaria?.nombre || "-"
                                    }}</span>
                                    <span
                                        v-if="user.gerencia.secretaria?.piso"
                                        class="text-slate-500 dark:text-slate-400"
                                    >
                                        ·
                                        {{
                                            user.gerencia.secretaria.piso.nombre
                                        }}
                                    </span>
                                </p>
                                <p
                                    class="text-xs text-slate-600 dark:text-slate-400 mt-1"
                                >
                                    Gerencia: {{ user.gerencia.nombre }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración de apariencia -->
                    <div
                        class="pt-6 border-t border-slate-200 dark:border-slate-700"
                    >
                        <h3
                            class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-4"
                        >
                            Configuración de Apariencia
                        </h3>
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <p
                                    class="text-sm font-medium text-slate-900 dark:text-slate-100"
                                >
                                    Modo Oscuro
                                </p>
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400 mt-1"
                                >
                                    Cambia entre tema claro y oscuro
                                </p>
                            </div>
                            <button
                                @click="toggleDarkMode"
                                type="button"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800"
                                :class="
                                    isDark ? 'bg-green-600' : 'bg-slate-200'
                                "
                            >
                                <span
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200"
                                    :class="
                                        isDark
                                            ? 'translate-x-6'
                                            : 'translate-x-1'
                                    "
                                />
                            </button>
                        </div>
                    </div>

                    <!-- Cambio de contraseña -->
                    <div
                        class="pt-6 border-t border-slate-200 dark:border-slate-700"
                    >
                        <h3
                            class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-4"
                        >
                            Cambiar Contraseña
                        </h3>
                        <p
                            class="text-xs text-slate-500 dark:text-slate-400 mb-4"
                        >
                            Deja estos campos vacíos si no deseas cambiar tu
                            contraseña
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <FormField
                                label="Nueva Contraseña"
                                :error="form.errors.password"
                            >
                                <input
                                    v-model="form.password"
                                    type="password"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent"
                                    placeholder="••••••••"
                                />
                            </FormField>

                            <FormField
                                label="Confirmar Nueva Contraseña"
                                :error="form.errors.password_confirmation"
                            >
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent"
                                    placeholder="••••••••"
                                />
                            </FormField>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div
                        class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-700"
                    >
                        <Link
                            :href="route('dashboard')"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium transition-colors duration-200"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                        >
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else>Guardar Cambios</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch, Transition } from "vue";
import { useForm, Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FormField from "@/Components/FormField.vue";
import { submitUploadForm } from "@/Support/inertiaUploads";
import { useDarkMode } from "@/composables/useDarkMode";

const { isDark, toggleDarkMode } = useDarkMode();
const page = usePage();

// Mensaje de éxito
const showSuccessMessage = ref(false);

// Mostrar mensaje de éxito si hay flash success
watch(
    () => page.props.flash?.success,
    (message) => {
        if (message) {
            showSuccessMessage.value = true;
            // Ocultar el mensaje después de 5 segundos
            setTimeout(() => {
                showSuccessMessage.value = false;
            }, 5000);
        }
    },
    { immediate: true }
);

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const formatTipoVinculacion = (name) => {
    const map = {
        visitante: "Visitante",
        servidor_publico: "Servidor público",
        proveedor: "Proveedor",
        contratista: "Proveedor", // Compatibilidad
        // compatibilidad histórica
        funcionario: "Servidor público",
    };
    return map[name] || name || null;
};

// Helper para URLs de storage
const storageUrl = (path) => {
    if (!path) return null;
    return `/storage/${path}`;
};

// Formulario
const form = useForm({
    nombre: props.user.nombre || "",
    apellido: props.user.apellido || "",
    foto: null,
    password: "",
    password_confirmation: "",
});

// Preview de foto
const fotoPreview = ref(null);
const fotoInput = ref(null);

const handleFotoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validar tamaño (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert("La imagen no puede ser mayor a 2MB");
            event.target.value = "";
            return;
        }

        // Validar tipo
        const validTypes = [
            "image/jpeg",
            "image/png",
            "image/jpg",
            "image/gif",
        ];
        if (!validTypes.includes(file.type)) {
            alert("El archivo debe ser una imagen (JPEG, PNG, JPG o GIF)");
            event.target.value = "";
            return;
        }

        form.foto = file;

        // Crear preview
        const reader = new FileReader();
        reader.onload = (e) => {
            fotoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    submitUploadForm(form, route("profile.update"), "put", {
        preserveScroll: true,
        onSuccess: () => {
            // Limpiar preview y resetear input
            fotoPreview.value = null;
            if (fotoInput.value) {
                fotoInput.value.value = "";
            }
            // Limpiar contraseñas
            form.password = "";
            form.password_confirmation = "";
        },
    });
};
</script>
