<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Mi Perfil</h1>
                    <p class="text-sm text-slate-600 mt-1">
                        Gestiona tu información personal y configuración de cuenta
                    </p>
                </div>
            </div>

            <!-- Mensajes de éxito/error -->
            <div
                v-if="$page.props.flash?.success"
                class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"
            >
                {{ $page.props.flash.success }}
            </div>
            <div
                v-if="$page.props.flash?.error"
                class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800"
            >
                {{ $page.props.flash.error }}
            </div>

            <!-- Formulario de perfil -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Foto de perfil -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Foto de Perfil
                        </label>
                        <div class="flex items-start gap-6">
                            <!-- Foto actual -->
                            <div class="flex-shrink-0">
                                <div class="w-32 h-32 rounded-full overflow-hidden bg-slate-100 border-2 border-slate-200">
                                    <img
                                        v-if="fotoPreview || user.foto_perfil"
                                        :src="fotoPreview || storageUrl(user.foto_perfil)"
                                        alt="Foto de perfil"
                                        class="w-full h-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="w-full h-full flex items-center justify-center text-slate-400 text-4xl"
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
                                    class="block w-full text-sm text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100"
                                />
                                <p class="mt-2 text-xs text-slate-500">
                                    Formatos permitidos: JPEG, PNG, JPG, GIF. Tamaño máximo: 2MB
                                </p>
                                <div v-if="form.errors.foto" class="mt-1 text-sm text-red-600">
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
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Tu nombre"
                            />
                        </FormField>

                        <FormField label="Apellido" :error="form.errors.apellido">
                            <input
                                v-model="form.apellido"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                            class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed"
                        />
                        <p class="mt-1 text-xs text-slate-500">
                            El email no puede ser modificado desde aquí
                        </p>
                    </FormField>

                    <!-- Información adicional (solo lectura) -->
                    <div class="pt-6 border-t border-slate-200">
                        <h3 class="text-sm font-semibold text-slate-900 mb-4">
                            Información de Cuenta
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Rol</p>
                                <p class="text-sm text-slate-900 font-medium">
                                    {{ user.role?.name || "-" }}
                                </p>
                            </div>
                            <div v-if="user.cargo">
                                <p class="text-xs text-slate-500 mb-1">Cargo</p>
                                <p class="text-sm text-slate-900 font-medium">
                                    {{ user.cargo?.name || "-" }}
                                </p>
                            </div>
                            <div v-if="user.departamento">
                                <p class="text-xs text-slate-500 mb-1">Departamento</p>
                                <p class="text-sm text-slate-900 font-medium">
                                    {{ user.departamento?.nombre || "-" }}
                                    <span v-if="user.departamento?.piso" class="text-slate-500">
                                        · {{ user.departamento.piso.nombre }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Cambio de contraseña -->
                    <div class="pt-6 border-t border-slate-200">
                        <h3 class="text-sm font-semibold text-slate-900 mb-4">
                            Cambiar Contraseña
                        </h3>
                        <p class="text-xs text-slate-500 mb-4">
                            Deja estos campos vacíos si no deseas cambiar tu contraseña
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <FormField
                                label="Nueva Contraseña"
                                :error="form.errors.password"
                            >
                                <input
                                    v-model="form.password"
                                    type="password"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="••••••••"
                                />
                            </FormField>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200">
                        <Link
                            :href="route('dashboard')"
                            class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium disabled:opacity-50 disabled:cursor-not-allowed"
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
import { ref, computed } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FormField from "@/Components/FormField.vue";
import { submitUploadForm } from "@/Support/inertiaUploads";

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

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
        const validTypes = ["image/jpeg", "image/png", "image/jpg", "image/gif"];
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

