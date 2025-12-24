<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Gestionar Permisos: {{ cargo.name }}
                    </h1>
                    <p class="text-sm text-slate-600">
                        Configura los permisos de acceso a puertas para este
                        cargo.
                    </p>
                </div>
                <Link
                    :href="route('cargos.index')"
                    class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700"
                >
                    Volver
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"
            >
                {{ $page.props.flash.message }}
            </div>

            <!-- Formulario básico del cargo -->
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
                    Información del Cargo
                </h2>
                <form
                    @submit.prevent="submitCargo"
                    class="grid grid-cols-1 gap-4"
                >
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            label="Nombre"
                            :error="formCargo.errors.name"
                        >
                            <input
                                v-model="formCargo.name"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                required
                            />
                        </FormField>
                        <div class="flex items-center gap-6 pt-7">
                            <label class="inline-flex items-center gap-2">
                                <input
                                    v-model="formCargo.activo"
                                    type="checkbox"
                                    class="h-4 w-4"
                                />
                                <span class="text-sm text-slate-700"
                                    >Activo</span
                                >
                            </label>
                        </div>
                    </div>
                    <FormField
                        label="Descripción"
                        :error="formCargo.errors.description"
                    >
                        <textarea
                            v-model="formCargo.description"
                            rows="2"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        />
                    </FormField>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="submit"
                            :disabled="formCargo.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-50"
                        >
                            {{
                                formCargo.processing
                                    ? "Guardando..."
                                    : "Guardar Cambios"
                            }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Agregar nueva puerta -->
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
                    Agregar Permiso de Puerta
                </h2>
                <form
                    @submit.prevent="submitPuerta"
                    class="grid grid-cols-1 gap-4"
                >
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            label="Puerta"
                            :error="formPuerta.errors.puerta_id"
                        >
                            <select
                                v-model="formPuerta.puerta_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                required
                            >
                                <option :value="null">
                                    Selecciona una puerta
                                </option>
                                <option
                                    v-for="p in todasLasPuertas"
                                    :key="p.id"
                                    :value="p.id"
                                >
                                    {{ p.nombre }}
                                    <span v-if="p.zona">
                                        - {{ p.zona.nombre }}</span
                                    >
                                </option>
                            </select>
                        </FormField>
                        <div class="flex items-center gap-6 pt-7">
                            <label class="inline-flex items-center gap-2">
                                <input
                                    v-model="formPuerta.activo"
                                    type="checkbox"
                                    class="h-4 w-4"
                                />
                                <span class="text-sm text-slate-700"
                                    >Permiso activo</span
                                >
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            label="Hora Inicio (opcional)"
                            :error="formPuerta.errors.hora_inicio"
                        >
                            <input
                                v-model="formPuerta.hora_inicio"
                                type="time"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                        </FormField>
                        <FormField
                            label="Hora Fin (opcional)"
                            :error="formPuerta.errors.hora_fin"
                        >
                            <input
                                v-model="formPuerta.hora_fin"
                                type="time"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            label="Fecha Inicio (opcional)"
                            :error="formPuerta.errors.fecha_inicio"
                        >
                            <input
                                v-model="formPuerta.fecha_inicio"
                                type="date"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                        </FormField>
                        <FormField
                            label="Fecha Fin (opcional)"
                            :error="formPuerta.errors.fecha_fin"
                        >
                            <input
                                v-model="formPuerta.fecha_fin"
                                type="date"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                        </FormField>
                    </div>

                    <FormField
                        label="Días de la Semana (opcional)"
                        :error="formPuerta.errors.dias_semana"
                    >
                        <input
                            v-model="formPuerta.dias_semana"
                            type="text"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Ej: 1,2,3,4,5 (1=Lunes, 7=Domingo)"
                        />
                        <p class="mt-1 text-xs text-slate-500">
                            Deja vacío para todos los días. Formato: números
                            separados por comas (1-7)
                        </p>
                    </FormField>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="submit"
                            :disabled="formPuerta.processing"
                            class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 disabled:opacity-50"
                        >
                            {{
                                formPuerta.processing
                                    ? "Agregando..."
                                    : "Agregar Permiso"
                            }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Lista de puertas asignadas -->
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
                    Puertas con Permiso ({{ puertasAsignadas.length }})
                </h2>

                <div
                    v-if="puertasAsignadas.length === 0"
                    class="text-center py-8 text-slate-500"
                >
                    No hay puertas asignadas a este cargo.
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="puerta in puertasAsignadas"
                        :key="puerta.id"
                        class="border border-slate-200 rounded-lg p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h3 class="font-semibold text-slate-900">
                                        {{ puerta.nombre }}
                                    </h3>
                                    <span
                                        v-if="puerta.pivot.activo"
                                        class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700"
                                    >
                                        Activo
                                    </span>
                                    <span
                                        v-else
                                        class="px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700"
                                    >
                                        Inactivo
                                    </span>
                                </div>
                                <div class="text-sm text-slate-600 space-y-1">
                                    <p v-if="puerta.zona">
                                        <span class="font-medium">Zona:</span>
                                        {{ puerta.zona.nombre }}
                                    </p>
                                    <p
                                        v-if="
                                            puerta.pivot.hora_inicio ||
                                            puerta.pivot.hora_fin
                                        "
                                    >
                                        <span class="font-medium"
                                            >Horario:</span
                                        >
                                        {{
                                            puerta.pivot.hora_inicio || "00:00"
                                        }}
                                        -
                                        {{ puerta.pivot.hora_fin || "23:59" }}
                                    </p>
                                    <p v-if="puerta.pivot.dias_semana">
                                        <span class="font-medium">Días:</span>
                                        {{
                                            formatDiasSemana(
                                                puerta.pivot.dias_semana
                                            )
                                        }}
                                    </p>
                                    <p
                                        v-if="
                                            puerta.pivot.fecha_inicio ||
                                            puerta.pivot.fecha_fin
                                        "
                                    >
                                        <span class="font-medium"
                                            >Período:</span
                                        >
                                        {{
                                            puerta.pivot.fecha_inicio ||
                                            "Sin inicio"
                                        }}
                                        -
                                        {{
                                            puerta.pivot.fecha_fin || "Sin fin"
                                        }}
                                    </p>
                                </div>
                            </div>
                            <button
                                @click="revokePuerta(puerta.id)"
                                class="px-3 py-1.5 rounded-md border border-red-200 text-red-700 hover:bg-red-50 text-sm"
                            >
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import FormField from "@/Components/FormField.vue";
import { Link, router, useForm } from "@inertiajs/vue3";

const props = defineProps({
    cargo: Object,
    puertasAsignadas: Array,
    todasLasPuertas: Array,
});

const formCargo = useForm({
    name: props.cargo.name || "",
    description: props.cargo.description || "",
    activo: !!props.cargo.activo,
});

const formPuerta = useForm({
    puerta_id: null,
    hora_inicio: null,
    hora_fin: null,
    dias_semana: "1,2,3,4,5,6,7",
    fecha_inicio: null,
    fecha_fin: null,
    activo: true,
});

const submitCargo = () => {
    formCargo.put(route("cargos.update", { cargo: props.cargo.id }));
};

const submitPuerta = () => {
    formPuerta.post(route("cargos.puertas.store", { cargo: props.cargo.id }), {
        onSuccess: () => {
            formPuerta.reset();
        },
    });
};

const revokePuerta = (puertaId) => {
    if (!confirm("¿Eliminar este permiso de puerta?")) return;
    router.delete(
        route("cargos.puertas.destroy", {
            cargo: props.cargo.id,
            puerta: puertaId,
        })
    );
};

const formatDiasSemana = (dias) => {
    const nombres = {
        1: "Lun",
        2: "Mar",
        3: "Mié",
        4: "Jue",
        5: "Vie",
        6: "Sáb",
        7: "Dom",
    };
    if (!dias) return "Todos";
    return dias
        .split(",")
        .map((d) => nombres[d.trim()] || d.trim())
        .join(", ");
};
</script>
