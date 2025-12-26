<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Editar Mantenimiento #{{ mantenimiento.id }}
                    </h1>
                    <p class="text-sm text-slate-600">
                        Actualiza los datos del mantenimiento.
                    </p>
                </div>
                <Link
                    :href="route('mantenimientos.index')"
                    class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700"
                >
                    Volver
                </Link>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            label="Puerta"
                            :error="form.errors.puerta_id"
                        >
                            <select
                                v-model="form.puerta_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                required
                            >
                                <option :value="null">Seleccione una puerta</option>
                                <option
                                    v-for="p in puertas"
                                    :key="p.id"
                                    :value="p.id"
                                >
                                    {{ p.nombre }} - {{ p.piso?.nombre || "-" }}
                                </option>
                            </select>
                        </FormField>
                        <FormField
                            label="Fecha de Mantenimiento"
                            :error="form.errors.fecha_mantenimiento"
                        >
                            <input
                                v-model="form.fecha_mantenimiento"
                                type="date"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                required
                            />
                        </FormField>
                        <FormField
                            label="Tipo de Mantenimiento"
                            :error="form.errors.tipo"
                        >
                            <select
                                v-model="form.tipo"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                required
                            >
                                <option value="realizado">Realizado</option>
                                <option value="programado">Programado</option>
                            </select>
                        </FormField>
                        <FormField
                            v-if="form.tipo === 'programado'"
                            label="Fecha de Fin Programada"
                            :error="form.errors.fecha_fin_programada"
                        >
                            <input
                                v-model="form.fecha_fin_programada"
                                type="date"
                                :min="form.fecha_mantenimiento"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                            <p class="mt-1 text-xs text-slate-500">
                                Indique hasta cuándo estará la puerta en mantenimiento
                            </p>
                        </FormField>
                    </div>

                    <FormField
                        label="Estado de los Defectos"
                        :error="form.errors.defectos"
                    >
                        <div class="space-y-3">
                            <div
                                v-for="defecto in defectos"
                                :key="defecto.id"
                                class="flex items-center gap-4 p-3 bg-slate-50 rounded-lg"
                            >
                                <label
                                    :for="`defecto-${defecto.id}`"
                                    class="flex-1 text-sm font-medium text-slate-700 min-w-[200px]"
                                >
                                    {{ defecto.nombre }}
                                </label>
                                <select
                                    :id="`defecto-${defecto.id}`"
                                    v-model="form.defectos[defecto.id]"
                                    class="flex-1 px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                                    required
                                >
                                    <option value="0">Sin defecto</option>
                                    <option value="1">Defecto ligero</option>
                                    <option value="2">Defecto grave</option>
                                    <option value="3">Defecto muy grave</option>
                                </select>
                                <span
                                    :class="[
                                        'px-2 py-1 rounded text-xs font-medium',
                                        form.defectos[defecto.id] == 0
                                            ? 'bg-green-100 text-green-700'
                                            : form.defectos[defecto.id] == 1
                                            ? 'bg-yellow-100 text-yellow-700'
                                            : form.defectos[defecto.id] == 2
                                            ? 'bg-orange-100 text-orange-700'
                                            : 'bg-red-100 text-red-700',
                                    ]"
                                >
                                    {{
                                        form.defectos[defecto.id] == 0
                                            ? "Sin defecto"
                                            : form.defectos[defecto.id] == 1
                                            ? "Ligero"
                                            : form.defectos[defecto.id] == 2
                                            ? "Grave"
                                            : "Muy grave"
                                    }}
                                </span>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">
                            Seleccione el nivel de gravedad para cada defecto
                        </p>
                    </FormField>

                    <FormField
                        label="Otros Defectos"
                        :error="form.errors.otros_defectos"
                    >
                        <textarea
                            v-model="form.otros_defectos"
                            rows="3"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Describa otros defectos encontrados..."
                        />
                    </FormField>

                    <FormField
                        label="Observaciones"
                        :error="form.errors.observaciones"
                    >
                        <textarea
                            v-model="form.observaciones"
                            rows="3"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Observaciones adicionales sobre el mantenimiento..."
                        />
                    </FormField>

                    <!-- Imágenes existentes -->
                    <div v-if="mantenimiento.imagenes?.length > 0">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Imágenes Actuales
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
                            <div
                                v-for="imagen in mantenimiento.imagenes"
                                :key="imagen.id"
                                class="relative"
                            >
                                <img
                                    :src="`/storage/${imagen.ruta_imagen}`"
                                    :alt="imagen.descripcion || 'Imagen'"
                                    class="w-full h-24 object-cover rounded border border-slate-200"
                                />
                                <button
                                    type="button"
                                    @click="eliminarImagen(imagen.id)"
                                    class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Agregar nuevas imágenes -->
                    <FormField
                        label="Agregar Nuevas Imágenes"
                        :error="form.errors.imagenes"
                    >
                        <input
                            @input="handleImages"
                            type="file"
                            multiple
                            accept="image/jpeg,image/jpg,image/png,image/gif"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        />
                        <p class="mt-1 text-xs text-slate-500">
                            Puede agregar hasta {{ 10 - (mantenimiento.imagenes?.length || 0) }} imágenes más. Formatos: JPEG, JPG, PNG, GIF (máx. 2MB cada una)
                        </p>
                        <div
                            v-if="imagenesPreview.length > 0"
                            class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-2"
                        >
                            <div
                                v-for="(preview, index) in imagenesPreview"
                                :key="index"
                                class="relative"
                            >
                                <img
                                    :src="preview"
                                    :alt="`Preview ${index + 1}`"
                                    class="w-full h-24 object-cover rounded border border-slate-200"
                                />
                                <button
                                    type="button"
                                    @click="removeImage(index)"
                                    class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                    </FormField>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="destroy"
                            class="px-4 py-2 rounded-lg border border-red-200 text-red-700 hover:bg-red-50"
                        >
                            Eliminar
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-50"
                        >
                            {{ form.processing ? "Guardando..." : "Guardar Cambios" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import FormField from "@/Components/FormField.vue";
import { Link, router, useForm } from "@inertiajs/vue3";

const props = defineProps({
    mantenimiento: Object,
    puertas: Array,
    defectos: Array,
});

const imagenesPreview = ref([]);
const imagenesFiles = ref([]);

// Inicializar defectos con los niveles de gravedad del mantenimiento
const defectosIniciales = {};
props.defectos.forEach((defecto) => {
    // Buscar si este defecto está en el mantenimiento
    const defectoMantenimiento = props.mantenimiento.defectos?.find(
        (d) => d.id === defecto.id
    );
    defectosIniciales[defecto.id] = defectoMantenimiento?.pivot?.nivel_gravedad ?? 0;
});

const form = useForm({
    puerta_id: props.mantenimiento.puerta_id,
    fecha_mantenimiento: props.mantenimiento.fecha_mantenimiento
        ? new Date(props.mantenimiento.fecha_mantenimiento).toISOString().split("T")[0]
        : new Date().toISOString().split("T")[0],
    tipo: props.mantenimiento.tipo || "realizado",
    fecha_fin_programada: props.mantenimiento.fecha_fin_programada
        ? new Date(props.mantenimiento.fecha_fin_programada).toISOString().split("T")[0]
        : null,
    defectos: defectosIniciales, // Objeto con defecto_id: nivel_gravedad
    otros_defectos: props.mantenimiento.otros_defectos || "",
    observaciones: props.mantenimiento.observaciones || "",
    imagenes: [],
});

const handleImages = (event) => {
    const files = Array.from(event.target.files);
    const imagenesActuales = props.mantenimiento.imagenes?.length || 0;
    const maxNuevas = 10 - imagenesActuales;

    if (files.length > maxNuevas) {
        alert(`Solo se pueden agregar hasta ${maxNuevas} imágenes más`);
        return;
    }

    imagenesFiles.value = files;
    imagenesPreview.value = [];

    files.forEach((file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagenesPreview.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });

    form.imagenes = files;
};

const removeImage = (index) => {
    imagenesPreview.value.splice(index, 1);
    imagenesFiles.value.splice(index, 1);
    form.imagenes = imagenesFiles.value;
};

const eliminarImagen = (imagenId) => {
    if (!confirm("¿Eliminar esta imagen?")) return;
    router.delete(route("mantenimientos.imagenes.destroy", { imagen: imagenId }));
};

const submit = () => {
    // Convertir el objeto de defectos a array con id y nivel_gravedad
    const defectosArray = Object.keys(form.defectos).map((defectoId) => ({
        id: parseInt(defectoId),
        nivel_gravedad: parseInt(form.defectos[defectoId]),
    }));

    const submitData = {
        ...form.data(),
        defectos: defectosArray,
    };

    if (form.imagenes.length > 0) {
        form.transform(() => ({
            ...submitData,
            _method: "PUT",
        })).post(route("mantenimientos.update", { mantenimiento: props.mantenimiento.id }), {
            forceFormData: true,
        });
    } else {
        form.transform(() => submitData).put(route("mantenimientos.update", { mantenimiento: props.mantenimiento.id }));
    }
};

const destroy = () => {
    if (!confirm("¿Eliminar este mantenimiento?")) return;
    router.delete(route("mantenimientos.destroy", { mantenimiento: props.mantenimiento.id }));
};
</script>

