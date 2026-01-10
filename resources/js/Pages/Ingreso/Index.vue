<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6 px-4 sm:px-6 lg:px-0">
            <!-- Header con fondo verde y logo centrado -->
            <div class="relative bg-gradient-to-br from-[#008c3a] via-[#006a2d] to-[#008c3a] rounded-xl overflow-hidden shadow-lg">
                <!-- Contenido centrado -->
                <div class="relative z-10 p-4 sm:p-6 lg:p-8 text-center">
                    <!-- Logo centrado -->
                    <div class="flex justify-center mb-3 sm:mb-4">
                        <img
                            src="/images/logo-gobernacion-meta.png"
                            alt="Gobernación del Meta"
                            class="h-16 sm:h-20 lg:h-24 w-auto object-contain drop-shadow-lg"
                            onerror="this.style.display='none'"
                        />
                    </div>

                    <!-- Título móvil: solo "Tu Código QR de Acceso" -->
                    <div class="sm:hidden">
                        <h1 class="text-xl font-bold text-white">
                            Tu Código QR de Acceso
                        </h1>
                    </div>

                    <!-- Título y descripción desktop -->
                    <div class="hidden sm:block">
                        <h1 class="text-2xl lg:text-3xl font-bold text-white mb-3">
                            Generar Código QR de Ingreso
                        </h1>
                        <p class="text-sm lg:text-base text-white/90 max-w-2xl mx-auto">
                            Genera un código QR para acceso al edificio. Para funcionarios, el QR estará activo hasta la fecha de expiración del usuario. Para visitantes, el QR es válido por 15 días.
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="Object.keys($page.props.errors || {}).length > 0"
                class="p-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 transition-colors duration-200"
            >
                <ul class="list-disc list-inside text-sm text-red-800 dark:text-red-200">
                    <li v-for="(error, key) in $page.props.errors" :key="key">
                        <span v-if="Array.isArray(error)">{{ error[0] }}</span>
                        <span v-else>{{ error }}</span>
                    </li>
                </ul>
            </div>

            <!-- Mostrar QR personal si existe y no puede crear para otros -->
            <div
                v-if="qrPersonal && !puedeCrearParaOtros"
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 sm:p-6 transition-colors duration-200"
            >
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    Tu Código QR de Acceso
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div
                            v-if="qrPersonal.svg"
                            class="bg-white dark:bg-slate-900 rounded-xl p-3 sm:p-4 border border-slate-200 dark:border-slate-700 mb-4 w-full sm:w-auto transition-colors duration-200"
                        >
                            <div class="flex justify-center">
                                <div
                                    class="max-w-full overflow-x-auto [&>svg]:max-w-full [&>svg]:h-auto"
                                    v-html="qrPersonal.svg"
                                ></div>
                            </div>
                        </div>
                        <div v-else class="text-red-600 text-sm mb-4">
                            No se pudo generar el código QR visual.
                        </div>
                        <div class="space-y-2 text-sm">
                            <p>
                                <span class="font-medium">Usuario:</span>
                                {{ qrPersonal.user_name }}
                            </p>
                            <p>
                                <span class="font-medium">Código:</span>
                                <code
                                    v-if="qrPersonal.token"
                                    class="px-2 py-1 bg-slate-100 rounded text-xs ml-2 inline-block align-middle break-all max-w-full"
                                >
                                    {{ qrPersonal.token }}
                                </code>
                                <span v-else class="text-slate-500 ml-2">No disponible</span>
                            </p>
                            <p>
                                <span class="font-medium">Expira:</span>
                                {{ qrPersonal.expires_at_formatted }}
                            </p>
                            <p>
                                <span class="font-medium">Generado:</span>
                                {{ qrPersonal.fecha_generacion }}
                            </p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-semibold text-slate-900 mb-2">
                                Instrucciones
                            </h3>
                            <ul
                                class="text-sm text-slate-600 space-y-1 list-disc list-inside"
                            >
                                <li>
                                    Para funcionarios: el código QR está activo hasta la fecha de expiración del usuario. Para visitantes: el QR es válido por 15 días desde su generación.
                                </li>
                                <li>
                                    Usa este QR para acceder a las puertas autorizadas.
                                </li>
                                <li>
                                    Puedes descargar o imprimir el código QR mostrado.
                                </li>
                            </ul>
                        </div>
                        <div class="flex flex-col gap-2">
                            <button
                                v-if="qrPersonal.token"
                                @click="descargarQRPersonal"
                                class="px-4 py-2 rounded-lg bg-green-600 dark:bg-green-700 text-white hover:bg-green-700 dark:hover:bg-green-600 text-center transition-colors duration-200"
                            >
                                Descargar QR
                            </button>
                            <button
                                v-if="!esVisitante"
                                @click="mostrarFormulario = true"
                                class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                            >
                                Generar Nuevo QR
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visitante sin QR activo -->
            <div
                v-if="esVisitante && !qrPersonal"
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 sm:p-6 transition-colors duration-200"
            >
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">
                    Tu Código QR de Acceso
                </h2>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    No tienes un QR activo en este momento. Si necesitas acceso,
                    solicita que te generen un QR.
                </p>
            </div>

            <!-- Formulario de generación (solo mostrar si no tiene QR activo o si puede crear para otros o si quiere generar nuevo) -->
            <div
                v-if="!esVisitante && (!qrPersonal || puedeCrearParaOtros || mostrarFormulario)"
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 sm:p-6 transition-colors duration-200"
            >
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    {{ puedeCrearParaOtros ? 'Datos del QR' : 'Generar Nuevo Código QR' }}
                </h2>
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-4">
                    <FormField label="Usuario" :error="form.errors.user_id">
                        <select
                            v-model="form.user_id"
                            :disabled="!puedeCrearParaOtros && usuarios.length === 1"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent disabled:bg-slate-100 dark:disabled:bg-slate-600 disabled:cursor-not-allowed transition-colors duration-200"
                            required
                        >
                            <option v-if="puedeCrearParaOtros" :value="null">Selecciona un usuario</option>
                            <option
                                v-for="u in usuarios"
                                :key="u.id"
                                :value="u.id"
                            >
                                {{ u.name || u.email }}
                                <span v-if="u.role"> - {{ u.role.name }}</span>
                                <span v-if="u.cargo">
                                    ({{ u.cargo.name }})</span
                                >
                            </option>
                        </select>
                        <p
                            v-if="!puedeCrearParaOtros"
                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                        >
                            Solo puedes generar QR para ti mismo. Si necesitas generar QR para otros usuarios, solicita el permiso correspondiente.
                        </p>
                    </FormField>

                    <!-- Visitante: seleccionar piso(s) en vez de puertas -->
                    <div
                        v-if="usuarioSeleccionado?.role?.name === 'visitante'"
                        class="space-y-4"
                    >
                        <FormField
                            v-if="puedeCrearParaOtros"
                            label="Departamento destino (obligatorio para visitantes)"
                            :error="form.errors.departamento_id"
                        >
                            <select
                                v-model="form.departamento_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                required
                            >
                                <option :value="null">Selecciona un departamento</option>
                                <option
                                    v-for="d in (departamentos || [])"
                                    :key="d.id"
                                    :value="d.id"
                                >
                                    {{ d.nombre }}
                                    <span v-if="d.piso"> - {{ d.piso.nombre }}</span>
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Este dato se registra en el QR del visitante.
                            </p>
                        </FormField>

                        <FormField
                            label="Pisos (obligatorio para visitantes)"
                            :error="form.errors.pisos"
                        >
                            <div
                                class="space-y-2 max-h-48 overflow-y-auto border border-slate-200 dark:border-slate-700 rounded-lg p-3 bg-white dark:bg-slate-700 transition-colors duration-200"
                            >
                                <label
                                    v-for="p in (pisos || [])"
                                    :key="p.id"
                                    class="flex items-center gap-2 p-2 hover:bg-slate-50 dark:hover:bg-slate-600 rounded transition-colors duration-200"
                                >
                                    <input
                                        type="checkbox"
                                        :value="p.id"
                                        v-model="form.pisos"
                                        class="h-4 w-4"
                                    />
                                    <div class="flex-1">
                                        <span class="font-medium">{{ p.nombre }}</span>
                                    </div>
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Selecciona al menos un piso. El sistema asignará automáticamente las puertas activas de esos pisos.
                            </p>
                        </FormField>
                    </div>

                    <!-- Opciones avanzadas (horarios) - Solo para usuarios con permiso create_ingreso_otros Y que NO sea funcionario -->
                    <div
                        v-if="puedeCrearParaOtros && usuarioSeleccionado?.role?.name !== 'funcionario'"
                        class="border-t border-slate-200 dark:border-slate-700 pt-4"
                    >
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                            Opciones de Horario (Opcional)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormField
                                label="Hora Inicio"
                                :error="form.errors.hora_inicio"
                            >
                                <input
                                    v-model="form.hora_inicio"
                                    type="time"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                />
                            </FormField>
                            <FormField
                                label="Hora Fin"
                                :error="form.errors.hora_fin"
                            >
                                <input
                                    v-model="form.hora_fin"
                                    type="time"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                />
                            </FormField>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <FormField
                                label="Fecha Inicio"
                                :error="form.errors.fecha_inicio"
                            >
                                <input
                                    v-model="form.fecha_inicio"
                                    type="date"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                />
                            </FormField>
                            <FormField
                                label="Fecha Fin"
                                :error="form.errors.fecha_fin"
                            >
                                <input
                                    v-model="form.fecha_fin"
                                    type="date"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                />
                            </FormField>
                        </div>
                        <FormField
                            label="Días de la Semana"
                            :error="form.errors.dias_semana"
                        >
                            <input
                                v-model="form.dias_semana"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="Ej: 1,2,3,4,5 (1=Lunes, 7=Domingo)"
                            />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Deja vacío para todos los días. Formato: números
                                separados por comas (1-7)
                            </p>
                        </FormField>
                    </div>

                    <!-- Mensaje informativo para funcionarios -->
                    <div
                        v-if="puedeCrearParaOtros && usuarioSeleccionado?.role?.name === 'funcionario'"
                        class="border-t border-slate-200 dark:border-slate-700 pt-4"
                    >
                        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-3 transition-colors duration-200">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <strong>Nota:</strong> Para funcionarios, el código QR estará activo hasta la fecha de expiración del contrato del usuario o hasta que se marque como inactivo. No se requieren fechas ni horarios adicionales.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full sm:w-auto px-6 py-2 rounded-lg bg-green-600 dark:bg-green-700 text-white hover:bg-green-700 dark:hover:bg-green-600 disabled:opacity-50 font-medium transition-colors duration-200"
                        >
                            {{
                                form.processing ? "Generando..." : "Generar QR"
                            }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Resultado: QR generado -->
            <div
                v-if="qrGenerado"
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 sm:p-6 transition-colors duration-200"
            >
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    QR Generado Exitosamente
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div
                            class="bg-white dark:bg-slate-900 rounded-xl p-3 sm:p-4 border border-slate-200 dark:border-slate-700 w-full sm:w-auto transition-colors duration-200"
                        >
                            <div v-if="typeof qrGenerado.svg === 'string'" class="flex justify-center">
                                <div
                                    class="max-w-full overflow-x-auto [&>svg]:max-w-full [&>svg]:h-auto"
                                    v-html="qrGenerado.svg"
                                ></div>
                            </div>
                            <div v-else class="text-red-600 text-sm">
                                Error: El SVG no se generó correctamente. Tipo:
                                {{ typeof qrGenerado.svg }}
                            </div>
                        </div>
                        <div class="mt-4 space-y-2 text-sm">
                            <p>
                                <span class="font-medium">Usuario:</span>
                                {{ qrGenerado.user_name }}
                            </p>
                            <p>
                                <span class="font-medium">Código:</span>
                                <code
                                    class="px-2 py-1 bg-slate-100 rounded text-xs ml-2 inline-block align-middle break-all max-w-full"
                                >
                                    {{ qrGenerado.token }}
                                </code>
                            </p>
                            <p>
                                <span class="font-medium">Expira:</span>
                                {{ qrGenerado.expires_at_formatted }}
                            </p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-semibold text-slate-900 mb-2">
                                Instrucciones
                            </h3>
                            <ul
                                class="text-sm text-slate-600 space-y-1 list-disc list-inside"
                            >
                                <li>
                                    Para funcionarios: el código QR está activo hasta la fecha de expiración del usuario. Para visitantes: el QR es válido por 15 días desde su generación.
                                </li>
                                <li>
                                    El usuario puede usar este QR para acceder a
                                    las puertas autorizadas.
                                </li>
                                <li>
                                    Puedes descargar o imprimir el código QR
                                    mostrado.
                                </li>
                            </ul>
                        </div>
                        <div class="flex flex-col gap-2">
                            <button
                                @click="enviarCorreo"
                                :disabled="enviandoCorreo"
                                class="px-4 py-2 rounded-lg bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                            >
                                {{
                                    enviandoCorreo
                                        ? "Enviando..."
                                        : "Enviar por Correo"
                                }}
                            </button>
                            <button
                                @click="descargarQR"
                                class="px-4 py-2 rounded-lg bg-green-600 dark:bg-green-700 text-white hover:bg-green-700 dark:hover:bg-green-600 text-center transition-colors duration-200"
                            >
                                Descargar QR
                            </button>
                            <button
                                @click="generarNuevo"
                                class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                            >
                                Generar Nuevo QR
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
import { computed, ref, onMounted, watch } from "vue";
import { useForm, router, usePage } from "@inertiajs/vue3";

const page = usePage();
const props = defineProps({
    usuarios: Array,
    puertas: Array,
    pisos: Array,
    departamentos: Array,
    qrGenerado: Object,
    puedeCrearParaOtros: Boolean,
    qrPersonal: Object,
});

const currentUser = computed(() => page.props.auth?.user || page.props.user);
const esVisitante = computed(() => currentUser.value?.role?.name === "visitante");

const form = useForm({
    user_id: null,
    departamento_id: null,
    pisos: [],
    puertas: [],
    hora_inicio: null,
    hora_fin: null,
    dias_semana: "1,2,3,4,5,6,7",
    fecha_inicio: null,
    fecha_fin: null,
});

// Si no puede crear para otros, pre-seleccionar el usuario actual
onMounted(() => {
    if (!props.puedeCrearParaOtros && currentUser.value && props.usuarios.length === 1) {
        form.user_id = props.usuarios[0].id;
    }

    // Si el usuario actual es visitante, establecer valores por defecto de seguridad
    if (esVisitante.value) {
        const hoy = new Date();
        const fechaHoy = hoy.toISOString().split('T')[0]; // Formato YYYY-MM-DD

        form.hora_inicio = '08:00';
        form.hora_fin = '19:00';
        form.fecha_inicio = fechaHoy;
        form.fecha_fin = fechaHoy;
    }

    // Si hay un usuario seleccionado y es visitante, establecer valores por defecto
    if (form.user_id) {
        const usuario = props.usuarios.find((u) => u.id === form.user_id);
        if (usuario?.role?.name === 'visitante') {
            const hoy = new Date();
            const fechaHoy = hoy.toISOString().split('T')[0];

            if (!form.hora_inicio) form.hora_inicio = '08:00';
            if (!form.hora_fin) form.hora_fin = '19:00';
            if (!form.fecha_inicio) form.fecha_inicio = fechaHoy;
            if (!form.fecha_fin) form.fecha_fin = fechaHoy;

            // Seleccionar automáticamente el piso 1 (ID 3) si está disponible
            if (props.pisos && props.pisos.length > 0) {
                // Buscar primero por ID 3 (piso 1)
                const piso1 = props.pisos.find(p => p.id === 3);

                if (piso1 && form.pisos.length === 0) {
                    form.pisos = [piso1.id];
                }
            }
        }
    }
});

const enviandoCorreo = ref(false);
const mostrarFormulario = ref(false);

const usuarioSeleccionado = computed(() => {
    if (!form.user_id) return null;
    return props.usuarios.find((u) => u.id === form.user_id);
});

// Si deja de ser visitante, limpiar departamento destino para evitar enviar basura
// Si se selecciona un visitante, establecer valores por defecto de seguridad
// Si se selecciona un funcionario, limpiar campos de fecha y horario
watch(
    () => usuarioSeleccionado.value?.role?.name,
    (roleName) => {
        if (roleName !== "visitante") {
            form.departamento_id = null;
            form.pisos = [];
        } else {
            // Cuando se selecciona un visitante, establecer valores por defecto de seguridad
            const hoy = new Date();
            const fechaHoy = hoy.toISOString().split('T')[0]; // Formato YYYY-MM-DD

            // Solo establecer si no tienen valores ya asignados
            if (!form.hora_inicio) {
                form.hora_inicio = '08:00';
            }
            if (!form.hora_fin) {
                form.hora_fin = '19:00';
            }
            if (!form.fecha_inicio) {
                form.fecha_inicio = fechaHoy;
            }
            if (!form.fecha_fin) {
                form.fecha_fin = fechaHoy;
            }

            // Seleccionar automáticamente el piso 1 (ID 3) si está disponible
            if (props.pisos && props.pisos.length > 0) {
                // Buscar piso con ID 3 (piso 1)
                const piso1 = props.pisos.find(p => p.id === 3);

                if (piso1 && !form.pisos.includes(piso1.id)) {
                    // Si no hay pisos seleccionados, agregar el piso 1
                    if (form.pisos.length === 0) {
                        form.pisos = [piso1.id];
                    }
                }
            }
        }

        // Si se selecciona un funcionario, limpiar campos de fecha y horario
        if (roleName === "funcionario") {
            form.hora_inicio = null;
            form.hora_fin = null;
            form.fecha_inicio = null;
            form.fecha_fin = null;
            form.dias_semana = "1,2,3,4,5,6,7";
        }
    }
);

const submit = () => {
    form.post(route("ingreso.generate"), {
        preserveScroll: true,
    });
};

const enviarCorreo = () => {
    if (!props.qrGenerado) return;

    const email = prompt(
        "Ingresa el correo electrónico donde enviar el QR:",
        props.qrGenerado.user_email || ""
    );

    if (!email) return;

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert("Por favor ingresa un correo electrónico válido.");
        return;
    }

    enviandoCorreo.value = true;

    router.post(
        route("ingreso.send-email", { qr: props.qrGenerado.id }),
        {
            email,
            token: props.qrGenerado.token,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                enviandoCorreo.value = false;
            },
        }
    );
};

const descargarQR = () => {
    if (!props.qrGenerado) return;

    const url = route("ingreso.download", {
        qr: props.qrGenerado.id,
        token: props.qrGenerado.token,
    });

    window.open(url, "_blank");
};

const descargarQRPersonal = () => {
    if (!props.qrPersonal || !props.qrPersonal.token || !props.qrPersonal.id) return;

    const url = route("ingreso.download", {
        qr: props.qrPersonal.id,
        token: props.qrPersonal.token,
    });

    window.open(url, "_blank");
};

const generarNuevo = () => {
    form.reset();
    form.user_id = null;
    form.puertas = [];
};
</script>
