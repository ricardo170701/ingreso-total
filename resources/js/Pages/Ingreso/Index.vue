<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6 px-4 sm:px-6 lg:px-0">
            <!-- Header con fondo verde y logo centrado -->
            <div class="relative bg-linear-to-br from-[#008c3a] via-[#006a2d] to-[#008c3a] rounded-xl overflow-hidden shadow-lg">
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

            <!-- Mostrar QR personal si existe y (no puede crear para otros O es visitante O showMiQr está activo) -->
            <div
                v-if="qrPersonal && (!puedeCrearParaOtros || esVisitante || showMiQr)"
                ref="miQrRef"
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 sm:p-6 transition-colors duration-200 relative"
            >
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Tu Código QR de Acceso
                    </h2>
                    <button
                        v-if="puedeCrearParaOtros && !esVisitante"
                        type="button"
                        @click="showMiQr = false"
                        class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200 flex items-center justify-center"
                        aria-label="Cerrar"
                        title="Minimizar"
                    >
                        ×
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div
                            v-if="qrPersonal.svg"
                            class="bg-white dark:bg-white rounded-xl p-3 sm:p-4 border-2 border-slate-200 dark:border-slate-600 shadow-lg dark:shadow-slate-900/50 mb-4 w-full sm:w-auto transition-all duration-200"
                        >
                            <div class="flex justify-center">
                                <div
                                    class="max-w-full overflow-x-auto [&>svg]:max-w-full [&>svg]:h-auto [&>svg]:drop-shadow-sm"
                                    v-html="qrPersonal.svg"
                                ></div>
                            </div>
                        </div>
                        <div v-else class="text-red-600 dark:text-red-400 text-sm mb-4">
                            No se pudo generar el código QR visual.
                        </div>
                        <div class="space-y-2 text-sm">
                            <p class="text-slate-700 dark:text-slate-300">
                                <span class="font-medium text-slate-900 dark:text-slate-100">Usuario:</span>
                                <span class="ml-2">{{ qrPersonal.user_name }}</span>
                            </p>
                            <p class="text-slate-700 dark:text-slate-300">
                                <span class="font-medium text-slate-900 dark:text-slate-100">Código:</span>
                                <code
                                    v-if="qrPersonal.token"
                                    class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-slate-100 rounded text-xs ml-2 inline-block align-middle break-all max-w-full font-mono border border-slate-200 dark:border-slate-600"
                                >
                                    {{ qrPersonal.token }}
                                </code>
                                <span v-else class="text-slate-500 dark:text-slate-400 ml-2">No disponible</span>
                            </p>
                            <p class="text-slate-700 dark:text-slate-300">
                                <span class="font-medium text-slate-900 dark:text-slate-100">Expira:</span>
                                <span class="ml-2">{{ qrPersonal.expires_at_formatted }}</span>
                            </p>
                            <p class="text-slate-700 dark:text-slate-300">
                                <span class="font-medium text-slate-900 dark:text-slate-100">Generado:</span>
                                <span class="ml-2">{{ qrPersonal.fecha_generacion }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-2">
                                Instrucciones
                            </h3>
                            <ul
                                class="text-sm text-slate-600 dark:text-slate-300 space-y-1 list-disc list-inside"
                            >
                                <li>
                                    Para funcionarios: el código QR está activo hasta la fecha de expiración del usuario. Para visitantes: el QR es válido por 15 días desde su generación.
                                </li>
                                <li>
                                    Usa este QR para acceder a los accesos autorizados.
                                </li>
                                <li>
                                    Muestra este QR en portería para permitir el ingreso.
                                </li>
                            </ul>
                        </div>
                        <div class="flex flex-col gap-2">
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
                <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        {{ puedeCrearParaOtros ? 'Datos del QR' : 'Generar Nuevo Código QR' }}
                    </h2>

                    <div class="flex gap-2 flex-wrap w-full sm:w-auto">
                        <button
                            v-if="puedeGenerarQr"
                            type="button"
                            @click="irAMiQr"
                            class="w-full sm:w-auto px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium transition-colors duration-200"
                        >
                            Mi QR
                        </button>
                        <button
                            v-if="puedeAsignarTarjetasNfc"
                            type="button"
                            @click="abrirModalTarjetasAsignadas"
                            class="w-full sm:w-auto px-4 py-2 rounded-lg border border-blue-200 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 font-medium transition-colors duration-200"
                        >
                            Tarjetas NFC asignadas
                        </button>
                        <button
                            v-if="puedeCrearVisitantes"
                            type="button"
                            @click="openVisitanteModal"
                            class="w-full sm:w-auto px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-medium transition-colors duration-200"
                        >
                            Agregar visitante
                        </button>
                    </div>
                </div>
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-4">
                    <FormField label="Usuario" :error="form.errors.user_id">
                        <!-- Selector buscable (principalmente útil cuando tiene permiso create_ingreso_otros) -->
                        <div class="flex items-start gap-2">
                            <div class="relative flex-1">
                                <input
                                    v-model="userPickerQuery"
                                    type="text"
                                    :disabled="!puedeCrearParaOtros && usuariosLocal.length === 1"
                                    @focus="openUserPicker"
                                    @keydown.down.prevent="userPickerMove(1)"
                                    @keydown.up.prevent="userPickerMove(-1)"
                                    @keydown.enter.prevent="userPickerSelectActive"
                                    @keydown.esc.prevent="closeUserPicker"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent disabled:bg-slate-100 dark:disabled:bg-slate-600 disabled:cursor-not-allowed transition-colors duration-200"
                                    :placeholder="puedeCrearParaOtros ? 'Buscar por nombre, email o cédula…' : 'Usuario'"
                                    autocomplete="off"
                                />
                                <!-- Mantener el required del form -->
                                <input type="hidden" v-model="form.user_id" required />

                                <div
                                    v-if="userPickerOpen && puedeCrearParaOtros"
                                    class="absolute z-30 mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg overflow-hidden max-h-[280px] overflow-y-auto"
                                >
                                    <div v-if="filteredUsuariosForPicker.length === 0" class="px-3 py-3 text-sm text-slate-500 dark:text-slate-400">
                                        Sin resultados
                                    </div>
                                    <button
                                        v-for="(u, idx) in filteredUsuariosForPicker"
                                        :key="u.id"
                                        type="button"
                                        @click="selectUsuarioFromPicker(u)"
                                        @mouseenter="userPickerActiveIndex = idx"
                                        class="w-full text-left px-3 py-2 flex items-center justify-between gap-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                                        :class="idx === userPickerActiveIndex ? 'bg-slate-50 dark:bg-slate-700' : ''"
                                    >
                                        <div class="min-w-0">
                                            <div class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">
                                                {{ u.name || u.email }}
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                                <span v-if="u.n_identidad">CC: {{ u.n_identidad }}</span>
                                                <span v-else>Sin cédula</span>
                                                <span v-if="u.role"> · {{ u.role.name }}</span>
                                                <span v-if="u.cargo"> · {{ u.cargo.name }}</span>
                                            </div>
                                        </div>
                                        <div class="text-xs text-slate-400 dark:text-slate-500 shrink-0">
                                            #{{ u.id }}
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="limpiarDatos"
                                class="shrink-0 px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium transition-colors duration-200"
                            >
                                Limpiar datos
                            </button>
                        </div>

                        <div
                            v-if="usuarioSeleccionado?.foto_perfil"
                            class="mt-3 flex items-center gap-3 p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/40"
                        >
                            <img
                                :src="storageUrl(usuarioSeleccionado.foto_perfil)"
                                alt="Foto de perfil"
                                class="w-12 h-12 rounded-full object-cover border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 cursor-zoom-in"
                                @click="openFotoPerfilModal(usuarioSeleccionado.foto_perfil)"
                            />
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">
                                    {{ usuarioSeleccionado.name || usuarioSeleccionado.email }}
                                </div>
                                <div class="text-xs text-slate-600 dark:text-slate-300">
                                    Foto de perfil
                                </div>
                            </div>
                        </div>
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
                        <div v-if="puedeCrearParaOtros" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormField
                                label="Secretaría destino"
                                :error="form.errors.secretaria_id"
                            >
                                <select
                                    v-model="form.secretaria_id"
                                    @change="onSecretariaChange"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                    required
                                >
                                    <option :value="null">Selecciona una secretaría</option>
                                    <option
                                        v-for="sec in (secretarias || [])"
                                        :key="sec.id"
                                        :value="sec.id"
                                    >
                                        {{ sec.nombre }}
                                        <span v-if="sec.piso"> - {{ sec.piso.nombre }}</span>
                                    </option>
                                </select>
                            </FormField>
                            <FormField
                                label="Gerencia destino (opcional)"
                                :error="form.errors.gerencia_id"
                            >
                                <select
                                    v-model="form.gerencia_id"
                                    :disabled="!form.secretaria_id"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <option :value="null">Despacho</option>
                                    <option
                                        v-for="ger in gerenciasFiltradas"
                                        :key="ger.id"
                                        :value="ger.id"
                                    >
                                        {{ ger.nombre }}
                                    </option>
                                </select>
                                <p v-if="!form.secretaria_id" class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    Selecciona una secretaría primero
                                </p>
                                <p v-else class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    Si no seleccionas gerencia, el destino se registrará como <strong>Despacho</strong>.
                                </p>
                            </FormField>
                        </div>

                        <!-- Selector de responsable (solo para visitantes) -->
                        <FormField
                            v-if="usuarioSeleccionado?.role?.name === 'visitante'"
                            label="Responsable (opcional)"
                            :error="form.errors.responsable_id"
                        >
                            <div class="relative">
                                <input
                                    v-model="responsablePickerQuery"
                                    type="text"
                                    @focus="openResponsablePicker"
                                    @keydown.down.prevent="responsablePickerMove(1)"
                                    @keydown.up.prevent="responsablePickerMove(-1)"
                                    @keydown.enter.prevent="responsablePickerSelectActive"
                                    @keydown.esc.prevent="closeResponsablePicker"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                    placeholder="Buscar responsable por nombre, email o cargo…"
                                    autocomplete="off"
                                />
                                <input type="hidden" v-model="form.responsable_id" />

                                <div
                                    v-if="responsablePickerOpen"
                                    class="absolute z-30 mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg overflow-hidden max-h-[280px] overflow-y-auto"
                                >
                                    <button
                                        type="button"
                                        @click="selectResponsableFromPicker(null)"
                                        @mouseenter="responsablePickerActiveIndex = -1"
                                        class="w-full text-left px-3 py-2 flex items-center justify-between gap-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                                        :class="-1 === responsablePickerActiveIndex ? 'bg-slate-50 dark:bg-slate-700' : ''"
                                    >
                                        <div class="text-sm font-medium text-slate-500 dark:text-slate-400 italic">
                                            Sin responsable
                                        </div>
                                    </button>
                                    <div v-if="filteredResponsablesForPicker.length === 0" class="px-3 py-3 text-sm text-slate-500 dark:text-slate-400">
                                        Sin resultados
                                    </div>
                                    <button
                                        v-for="(resp, idx) in filteredResponsablesForPicker"
                                        :key="resp.id"
                                        type="button"
                                        @click="selectResponsableFromPicker(resp)"
                                        @mouseenter="responsablePickerActiveIndex = idx"
                                        class="w-full text-left px-3 py-2 flex items-center justify-between gap-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                                        :class="idx === responsablePickerActiveIndex ? 'bg-slate-50 dark:bg-slate-700' : ''"
                                    >
                                        <div class="min-w-0">
                                            <div class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">
                                                {{ resp.name || resp.email }}
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                                <span v-if="resp.email">{{ resp.email }}</span>
                                                <span v-if="resp.cargo"> · {{ resp.cargo.name }}</span>
                                            </div>
                                        </div>
                                        <div class="text-xs text-slate-400 dark:text-slate-500 shrink-0">
                                            #{{ resp.id }}
                                        </div>
                                    </button>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Busca y selecciona un servidor público o proveedor como responsable del ingreso del visitante (opcional)
                            </p>
                        </FormField>

                        <FormField
                            v-if="usuarioSeleccionado?.role?.name === 'visitante'"
                            label="Puertas (obligatorio para visitantes)"
                            :error="form.errors.puertas"
                        >
                            <div
                                v-if="pisosConPuertasLocal.length === 0"
                                class="text-sm text-slate-500 dark:text-slate-400 p-3 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-700 transition-colors duration-200"
                            >
                                No hay pisos con puertas activas.
                            </div>

                            <div
                                v-else
                                class="space-y-1 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden transition-colors duration-200"
                            >
                                <div
                                    v-for="piso in pisosConPuertasLocal"
                                    :key="piso.id"
                                    class="border-b border-slate-200 dark:border-slate-700 last:border-b-0 transition-colors duration-200"
                                >
                                    <div
                                        class="flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-700/40 hover:bg-slate-100 dark:hover:bg-slate-700/60 cursor-pointer transition-colors duration-200"
                                        @click="toggleAccordion(piso.id)"
                                    >
                                        <button
                                            type="button"
                                            class="shrink-0 w-8 h-8 flex items-center justify-center rounded text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors duration-200"
                                            :aria-label="openPisoId === piso.id ? 'Cerrar' : 'Abrir'"
                                        >
                                            <span
                                                class="transition-transform duration-200"
                                                :class="openPisoId === piso.id ? 'rotate-90' : ''"
                                            >
                                                ▶
                                            </span>
                                        </button>

                                        <label class="flex items-center gap-2 flex-1 cursor-pointer min-w-0" @click.stop>
                                            <input
                                                type="checkbox"
                                                :checked="isPisoAllSelected(piso)"
                                                :indeterminate.prop="isPisoIndeterminate(piso)"
                                                class="h-4 w-4 text-green-600 dark:text-green-500 border-slate-300 dark:border-slate-600 rounded focus:ring-green-500 dark:focus:ring-green-400 shrink-0"
                                                @change="togglePisoAll(piso)"
                                            />
                                            <span class="font-medium text-slate-900 dark:text-slate-100 truncate">
                                                {{ piso.nombre }}
                                            </span>
                                            <span
                                                v-if="(piso.puertas || []).length"
                                                class="text-xs text-slate-500 dark:text-slate-400 shrink-0"
                                            >
                                                ({{ countSelectedInPiso(piso) }}/{{ piso.puertas.length }})
                                            </span>
                                        </label>
                                    </div>

                                    <div
                                        v-show="openPisoId === piso.id"
                                        class="px-4 pb-3 pt-1 pl-12 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 transition-colors duration-200"
                                    >
                                        <div v-if="!piso.puertas || piso.puertas.length === 0" class="text-sm text-slate-500 dark:text-slate-400 py-2">
                                            Sin puertas activas
                                        </div>
                                        <div v-else class="space-y-1.5 py-2">
                                            <label
                                                v-for="puerta in piso.puertas || []"
                                                :key="puerta.id"
                                                class="flex items-center gap-2 py-1.5 px-2 rounded hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer transition-colors duration-200"
                                            >
                                                <input
                                                    type="checkbox"
                                                    :checked="form.puertas.includes(puerta.id)"
                                                    class="h-4 w-4 text-green-600 dark:text-green-500 border-slate-300 dark:border-slate-600 rounded focus:ring-green-500 dark:focus:ring-green-400 shrink-0"
                                                    @change="togglePuerta(puerta.id)"
                                                />
                                                <span class="text-sm text-slate-700 dark:text-slate-300">
                                                    {{ puerta.nombre }}
                                                    <span
                                                        v-if="puerta.codigo_fisico"
                                                        class="text-slate-500 dark:text-slate-400 text-xs ml-1"
                                                    >
                                                        ({{ puerta.codigo_fisico }})
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ (form.puertas || []).length }} puerta(s) seleccionada(s)
                            </p>
                        </FormField>

                        <!-- Asignar Tarjeta NFC (solo para visitantes con permiso) -->
                        <div
                            v-if="puedeCrearParaOtros && puedeAsignarTarjetasNfc"
                            class="border-t border-slate-200 dark:border-slate-700 pt-4"
                        >
                            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                                Asignar Tarjeta NFC (Opcional)
                            </h3>
                            <FormField label="Tarjeta NFC" :error="formTarjetaNfc.errors.tarjeta_nfc_id">
                                <select
                                    v-model="formTarjetaNfc.tarjeta_nfc_id"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                >
                                    <option :value="null">No asignar tarjeta NFC</option>
                                    <option
                                        v-for="t in tarjetasNfcDisponibles"
                                        :key="t.id"
                                        :value="t.id"
                                    >
                                        {{ t.codigo }}
                                        <span v-if="t.nombre"> - {{ t.nombre }}</span>
                                    </option>
                                </select>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    Selecciona una tarjeta NFC disponible para asignar al visitante. La tarjeta tendrá los mismos permisos que el QR.
                                </p>
                            </FormField>
                            <div v-if="tarjetaNfcAsignadaAUsuarioSeleccionado" class="mb-3 p-3 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/30">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                            Tarjeta asignada: {{ tarjetaNfcAsignadaAUsuarioSeleccionado.codigo }}
                                            <span v-if="tarjetaNfcAsignadaAUsuarioSeleccionado.nombre"> - {{ tarjetaNfcAsignadaAUsuarioSeleccionado.nombre }}</span>
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        @click="desasignarTarjetaNfc"
                                        :disabled="tarjetaNfcProcessing"
                                        class="px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium transition-colors duration-200"
                                    >
                                        {{ tarjetaNfcProcessing ? 'Procesando...' : 'Desasignar' }}
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button
                                    type="button"
                                    @click="asignarTarjetaNfc"
                                    :disabled="!formTarjetaNfc.tarjeta_nfc_id || !form.user_id || tarjetaNfcProcessing"
                                    class="px-4 py-2 rounded-lg bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed font-medium transition-colors duration-200"
                                >
                                    {{ tarjetaNfcProcessing ? 'Asignando...' : 'Asignar Tarjeta NFC' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Opciones avanzadas (horarios) - Solo para usuarios con permiso create_ingreso_otros Y que NO sea staff -->
                    <div
                        v-if="puedeCrearParaOtros && !isStaffUsuarioSeleccionado"
                        class="border-t border-slate-200 dark:border-slate-700 pt-4"
                    >
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                            Opciones de Horario (Opcional)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                    </div>

                    <!-- Mensaje informativo para staff -->
                    <div
                        v-if="puedeCrearParaOtros && isStaffUsuarioSeleccionado"
                        class="border-t border-slate-200 dark:border-slate-700 pt-4"
                    >
                        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-3 transition-colors duration-200">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <strong>Nota:</strong> Para servidor público/proveedor, el código QR estará activo hasta la fecha de expiración del contrato del usuario o hasta que se marque como inactivo. No se requieren fechas ni horarios adicionales.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            v-if="!visitanteSinCorreoSeleccionado"
                            type="submit"
                            :disabled="form.processing"
                            class="w-full sm:w-auto px-6 py-2 rounded-lg bg-green-600 dark:bg-green-700 text-white hover:bg-green-700 dark:hover:bg-green-600 disabled:opacity-50 font-medium transition-colors duration-200"
                        >
                            {{
                                form.processing ? "Generando..." : "Generar QR"
                            }}
                        </button>
                        <div
                            v-else
                            class="w-full sm:w-auto px-4 py-2 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200 text-sm"
                        >
                            Este visitante no tiene correo: no se genera QR. Asigna una tarjeta NFC.
                        </div>
                    </div>
                </form>
            </div>

            <!-- Resultado: QR generado -->
            <div
                v-if="qrGeneradoLocal"
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 sm:p-6 transition-colors duration-200"
            >
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    QR Generado Exitosamente
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div
                            class="bg-white dark:bg-white rounded-xl p-3 sm:p-4 border-2 border-slate-200 dark:border-slate-600 shadow-lg dark:shadow-slate-900/50 w-full sm:w-auto transition-all duration-200"
                        >
                            <div v-if="typeof qrGeneradoLocal.svg === 'string'" class="flex justify-center">
                                <div
                                    class="max-w-full overflow-x-auto [&>svg]:max-w-full [&>svg]:h-auto [&>svg]:drop-shadow-sm"
                                    v-html="qrGeneradoLocal.svg"
                                ></div>
                            </div>
                            <div v-else class="text-red-600 dark:text-red-400 text-sm">
                                Error: El SVG no se generó correctamente. Tipo:
                                {{ typeof qrGeneradoLocal.svg }}
                            </div>
                        </div>
                        <div class="mt-4 space-y-2 text-sm">
                            <p class="text-slate-700 dark:text-slate-300">
                                <span class="font-medium text-slate-900 dark:text-slate-100">Usuario:</span>
                                <span class="ml-2">{{ qrGeneradoLocal.user_name }}</span>
                            </p>
                            <p class="text-slate-700 dark:text-slate-300">
                                <span class="font-medium text-slate-900 dark:text-slate-100">Código:</span>
                                <code
                                    class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-slate-100 rounded text-xs ml-2 inline-block align-middle break-all max-w-full font-mono border border-slate-200 dark:border-slate-600"
                                >
                                    {{ qrGeneradoLocal.token }}
                                </code>
                            </p>
                            <p class="text-slate-700 dark:text-slate-300">
                                <span class="font-medium text-slate-900 dark:text-slate-100">Expira:</span>
                                <span class="ml-2">{{ qrGeneradoLocal.expires_at_formatted }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-2">
                                Instrucciones
                            </h3>
                            <ul
                                class="text-sm text-slate-600 dark:text-slate-300 space-y-1 list-disc list-inside"
                            >
                                <li>
                                    Para funcionarios: el código QR está activo hasta la fecha de expiración del usuario. Para visitantes: el QR es válido por 15 días desde su generación.
                                </li>
                                <li>
                                    El usuario puede usar este QR para acceder a
                                    los accesos autorizados.
                                </li>
                                <li>
                                    Muestra este QR en portería para permitir el
                                    ingreso.
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
                                v-if="!esVisitante"
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

        <!-- Modal: Crear visitante -->
        <div
            v-if="visitanteModalOpen"
            @click="closeVisitanteModal"
            class="fixed inset-0 bg-black/60 dark:bg-black/70 flex items-center justify-center z-50 p-4 transition-colors duration-200"
        >
            <div
                class="w-full max-w-lg bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl"
                @click.stop
            >
                <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Agregar visitante
                    </h3>
                    <button
                        type="button"
                        @click="closeVisitanteModal"
                        class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        aria-label="Cerrar"
                    >
                        ×
                    </button>
                </div>

                <form @submit.prevent="submitVisitante" class="p-4 sm:p-6 space-y-4">
                    <div
                        v-if="visitanteServerError"
                        class="p-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-sm text-red-800 dark:text-red-200"
                    >
                        {{ visitanteServerError }}
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <FormField label="Nombre" :error="visitanteErrors.nombre">
                            <input
                                v-model="visitanteForm.nombre"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                required
                            />
                        </FormField>
                        <FormField label="Apellido" :error="visitanteErrors.apellido">
                            <input
                                v-model="visitanteForm.apellido"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                required
                            />
                        </FormField>
                    </div>

                    <FormField label="Email (opcional)" :error="visitanteErrors.email">
                        <input
                            v-model="visitanteForm.email"
                            type="email"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            placeholder="correo@ejemplo.com"
                        />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Si no tiene correo, se creará como <strong>visitante sin correo</strong>: no tendrá login ni QR, solo se le puede asignar tarjeta NFC.
                        </p>
                    </FormField>

                    <FormField label="Foto (opcional)" :error="visitanteErrors.foto">
                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                            <button
                                type="button"
                                @click="openVisitanteCamera"
                                class="px-3 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 text-sm font-medium transition-colors duration-200"
                            >
                                Usar cámara
                            </button>
                            <button
                                v-if="visitanteForm.foto"
                                type="button"
                                @click="clearVisitanteFoto"
                                class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium transition-colors duration-200"
                            >
                                Quitar foto
                            </button>
                        </div>
                        <input
                            type="file"
                            accept="image/*"
                            @change="onVisitanteFotoChange"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                        />
                        <div
                            v-if="visitanteFotoPreviewUrl"
                            class="mt-3 p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/40"
                        >
                            <div class="text-xs text-slate-600 dark:text-slate-300 mb-2">
                                Vista previa
                            </div>
                            <img
                                :src="visitanteFotoPreviewUrl"
                                alt="Foto del visitante (vista previa)"
                                class="w-full max-h-64 object-contain rounded-md bg-white dark:bg-slate-800"
                            />
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            JPG/PNG/WEBP, máx 4MB. Para usar la cámara se requiere HTTPS (o localhost).
                        </p>
                    </FormField>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <FormField label="N° Identidad" :error="visitanteErrors.n_identidad">
                            <input
                                v-model="visitanteForm.n_identidad"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                required
                            />
                        </FormField>
                        <FormField label="Observaciones (opcional)" :error="visitanteErrors.observaciones">
                            <textarea
                                v-model="visitanteForm.observaciones"
                                rows="2"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="Observaciones adicionales"
                            />
                        </FormField>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="closeVisitanteModal"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="visitanteProcessing"
                            class="px-4 py-2 rounded-lg bg-green-600 dark:bg-green-700 text-white hover:bg-green-700 dark:hover:bg-green-600 disabled:opacity-50 font-medium transition-colors duration-200"
                        >
                            {{ visitanteProcessing ? 'Creando...' : 'Crear visitante' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Foto de perfil en grande -->
        <div
            v-if="fotoPerfilModalPath"
            @click="closeFotoPerfilModal"
            class="fixed inset-0 bg-black/90 dark:bg-black/95 flex items-center justify-center z-50 p-4 transition-colors duration-200"
        >
            <div class="relative max-w-4xl max-h-[95vh] w-full">
                <button
                    type="button"
                    @click="closeFotoPerfilModal"
                    class="absolute top-4 right-4 bg-white/20 dark:bg-white/30 hover:bg-white/30 dark:hover:bg-white/40 text-white rounded-full w-10 h-10 flex items-center justify-center text-xl font-bold z-10 transition-colors duration-200"
                    aria-label="Cerrar"
                >
                    ×
                </button>
                <div class="flex items-center justify-center h-full">
                    <img
                        :src="storageUrl(fotoPerfilModalPath)"
                        alt="Foto de perfil"
                        class="max-w-full max-h-[90vh] object-contain rounded-lg"
                        @click.stop
                    />
                </div>
            </div>
        </div>

        <!-- Modal: Cámara (crear visitante) -->
        <div
            v-if="visitanteCameraOpen"
            @click="closeVisitanteCamera"
            class="fixed inset-0 bg-black/70 dark:bg-black/80 flex items-center justify-center z-50 p-4 transition-colors duration-200"
        >
            <div
                class="w-full max-w-2xl bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl"
                @click.stop
            >
                <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Tomar foto del visitante
                    </h3>
                    <button
                        type="button"
                        @click="closeVisitanteCamera"
                        class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        aria-label="Cerrar"
                    >
                        ×
                    </button>
                </div>

                <div class="p-4 sm:p-6 space-y-4">
                    <div
                        v-if="visitanteCameraError"
                        class="p-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-sm text-red-800 dark:text-red-200"
                    >
                        {{ visitanteCameraError }}
                    </div>

                    <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-black">
                        <video
                            ref="visitanteVideoRef"
                            autoplay
                            playsinline
                            muted
                            class="w-full h-auto"
                        ></video>
                        <canvas ref="visitanteCanvasRef" class="hidden"></canvas>
                    </div>

                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <button
                            type="button"
                            @click="toggleVisitanteCameraFacing"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium transition-colors duration-200"
                        >
                            Cambiar cámara
                        </button>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="closeVisitanteCamera"
                                class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium transition-colors duration-200"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                @click="takeVisitantePhoto"
                                :disabled="visitanteCameraStarting"
                                class="px-4 py-2 rounded-lg bg-green-600 dark:bg-green-700 text-white hover:bg-green-700 dark:hover:bg-green-600 disabled:opacity-50 text-sm font-medium transition-colors duration-200"
                            >
                                {{ visitanteCameraStarting ? "Iniciando..." : "Tomar foto" }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Tarjetas NFC Asignadas -->
        <div
            v-if="modalTarjetasAsignadasOpen"
            @click="closeModalTarjetasAsignadas"
            class="fixed inset-0 bg-black/60 dark:bg-black/70 flex items-center justify-center z-50 p-4 transition-colors duration-200"
        >
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col border border-slate-200 dark:border-slate-700 transition-colors duration-200"
                @click.stop
            >
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Tarjetas NFC Asignadas
                    </h3>
                    <button
                        type="button"
                        @click="closeModalTarjetasAsignadas"
                        class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200 flex items-center justify-center"
                        aria-label="Cerrar"
                    >
                        ×
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-6">
                    <div
                        v-if="loadingTarjetasAsignadas"
                        class="text-center py-8 text-slate-600 dark:text-slate-400"
                    >
                        Cargando tarjetas asignadas...
                    </div>
                    <div
                        v-else-if="tarjetasAsignadas.length === 0"
                        class="text-center py-8 text-slate-500 dark:text-slate-400"
                    >
                        No hay tarjetas NFC asignadas actualmente.
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="tarjeta in tarjetasAsignadas"
                            :key="tarjeta.id"
                            class="border border-slate-200 dark:border-slate-700 rounded-lg p-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <h4 class="font-semibold text-slate-900 dark:text-slate-100">
                                            {{ tarjeta.codigo }}
                                        </h4>
                                        <span
                                            v-if="tarjeta.nombre"
                                            class="text-sm text-slate-600 dark:text-slate-400"
                                        >
                                            - {{ tarjeta.nombre }}
                                        </span>
                                    </div>
                                    <div class="space-y-1 text-sm">
                                        <p v-if="tarjeta.user" class="text-slate-700 dark:text-slate-300">
                                            <span class="font-medium text-slate-900 dark:text-slate-100">Usuario:</span>
                                            <span class="ml-2">{{ tarjeta.user.name }}</span>
                                            <span v-if="tarjeta.user.email" class="ml-2 text-slate-500 dark:text-slate-400">
                                                ({{ tarjeta.user.email }})
                                            </span>
                                        </p>
                                        <p v-if="tarjeta.user?.n_identidad" class="text-slate-700 dark:text-slate-300">
                                            <span class="font-medium text-slate-900 dark:text-slate-100">Identidad:</span>
                                            <span class="ml-2">{{ tarjeta.user.n_identidad }}</span>
                                        </p>
                                        <p v-if="tarjeta.gerencia" class="text-slate-700 dark:text-slate-300">
                                            <span class="font-medium text-slate-900 dark:text-slate-100">Gerencia:</span>
                                            <span class="ml-2">
                                                {{ tarjeta.gerencia.nombre }}
                                                <span v-if="tarjeta.gerencia.secretaria" class="text-slate-500 dark:text-slate-400">
                                                    - {{ tarjeta.gerencia.secretaria.nombre }}
                                                </span>
                                            </span>
                                        </p>
                                        <p v-if="tarjeta.fecha_asignacion" class="text-slate-700 dark:text-slate-300">
                                            <span class="font-medium text-slate-900 dark:text-slate-100">Asignada:</span>
                                            <span class="ml-2">{{ tarjeta.fecha_asignacion }}</span>
                                        </p>
                                        <p v-if="tarjeta.fecha_expiracion" class="text-slate-700 dark:text-slate-300">
                                            <span class="font-medium text-slate-900 dark:text-slate-100">Expira:</span>
                                            <span class="ml-2">{{ tarjeta.fecha_expiracion }}</span>
                                        </p>
                                    </div>
                                </div>
                                <button
                                    v-if="puedeAsignarTarjetasNfc"
                                    type="button"
                                    @click="desasignarTarjetaDesdeModal(tarjeta)"
                                    :disabled="tarjetaNfcProcessing"
                                    class="shrink-0 px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium transition-colors duration-200"
                                >
                                    Desasignar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sistema de Notificaciones -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-x-full"
            enter-to-class="opacity-100 translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-x-0"
            leave-to-class="opacity-0 translate-x-full"
        >
            <div
                v-if="notification.show"
                class="fixed top-4 right-4 z-50 max-w-md"
            >
                <div
                    :class="[
                        'rounded-xl border shadow-lg p-4 flex items-start gap-3 transition-colors duration-200',
                        notification.type === 'success'
                            ? 'bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-800'
                            : notification.type === 'error'
                            ? 'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800'
                            : notification.type === 'warning'
                            ? 'bg-yellow-50 dark:bg-yellow-900/30 border-yellow-200 dark:border-yellow-800'
                            : 'bg-blue-50 dark:bg-blue-900/30 border-blue-200 dark:border-blue-800',
                    ]"
                >
                    <div class="shrink-0">
                        <span
                            v-if="notification.type === 'success'"
                            class="text-2xl"
                        >
                            ✅
                        </span>
                        <span
                            v-else-if="notification.type === 'error'"
                            class="text-2xl"
                        >
                            ❌
                        </span>
                        <span
                            v-else-if="notification.type === 'warning'"
                            class="text-2xl"
                        >
                            ⚠️
                        </span>
                        <span v-else class="text-2xl">ℹ️</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p
                            :class="[
                                'text-sm font-medium',
                                notification.type === 'success'
                                    ? 'text-green-800 dark:text-green-200'
                                    : notification.type === 'error'
                                    ? 'text-red-800 dark:text-red-200'
                                    : notification.type === 'warning'
                                    ? 'text-yellow-800 dark:text-yellow-200'
                                    : 'text-blue-800 dark:text-blue-200',
                            ]"
                        >
                            {{ notification.message }}
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="notification.show = false"
                        :class="[
                            'shrink-0 transition-colors',
                            notification.type === 'success'
                                ? 'text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200'
                                : notification.type === 'error'
                                ? 'text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200'
                                : notification.type === 'warning'
                                ? 'text-yellow-600 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-200'
                                : 'text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200',
                        ]"
                        aria-label="Cerrar"
                    >
                        ×
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Modal de Confirmación -->
        <div
            v-if="confirmModal.show"
            @click="confirmModal.show = false"
            class="fixed inset-0 bg-black/60 dark:bg-black/70 flex items-center justify-center z-50 p-4 transition-colors duration-200"
        >
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-md w-full border border-slate-200 dark:border-slate-700 transition-colors duration-200"
                @click.stop
            >
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Confirmar
                    </h3>
                    <button
                        type="button"
                        @click="confirmModal.show = false"
                        class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200 flex items-center justify-center"
                        aria-label="Cerrar"
                    >
                        ×
                    </button>
                </div>

                <div class="p-6">
                    <p class="text-sm text-slate-700 dark:text-slate-300 mb-4">
                        {{ confirmModal.message }}
                    </p>

                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="confirmModal.show = false"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            @click="confirmModalConfirm"
                            class="px-4 py-2 rounded-lg bg-red-600 dark:bg-red-700 text-white hover:bg-red-700 dark:hover:bg-red-600 font-medium transition-colors duration-200"
                        >
                            Confirmar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import FormField from "@/Components/FormField.vue";
import { computed, ref, onMounted, watch, onUnmounted, nextTick } from "vue";
import { useForm, router, usePage } from "@inertiajs/vue3";

const page = usePage();
const props = defineProps({
    usuarios: Array,
    puertas: Array,
    pisos: Array,
    pisosConPuertas: Array,
    secretarias: Array,
    gerencias: Array,
    responsables: Array,
    qrGenerado: Object,
    puedeCrearParaOtros: Boolean,
    qrPersonal: Object,
    tarjetasNfcDisponibles: Array,
});

// Mantener una copia local del QR generado para poder ocultarlo (p.ej. "Limpiar datos")
const qrGeneradoLocal = ref(props.qrGenerado || null);
watch(
    () => props.qrGenerado,
    (val) => {
        qrGeneradoLocal.value = val || null;
    },
);

const currentUser = computed(() => page.props.auth?.user || page.props.user);
const esVisitante = computed(() => currentUser.value?.role?.name === "visitante");
const userPermissions = computed(() => page.props.auth?.user?.permissions || []);
const puedeCrearVisitantes = computed(() => {
    if (esVisitante.value) return false;
    return userPermissions.value.includes("create_ingreso_visitantes");
});
const puedeGenerarQr = computed(() => {
    if (esVisitante.value) return false;
    return userPermissions.value.includes("create_ingreso");
});
const puedeAsignarTarjetasNfc = computed(() => {
    if (esVisitante.value) return false;
    return userPermissions.value.includes("asignar_tarjetas_nfc");
});

const form = useForm({
    user_id: null,
    secretaria_id: null,
    gerencia_id: null,
    responsable_id: null,
    pisos: [],
    puertas: [],
    hora_inicio: null,
    hora_fin: null,
    dias_semana: "1,2,3,4,5,6,7",
    fecha_inicio: null,
    fecha_fin: null,
});

const formTarjetaNfc = useForm({
    tarjeta_nfc_id: null,
    user_id: null,
    gerencia_id: null,
    puertas: [],
    hora_inicio: null,
    hora_fin: null,
    dias_semana: "1,2,3,4,5,6,7",
    fecha_inicio: null,
    fecha_fin: null,
});

// ===== Selección de puertas por piso (acordeón estilo Cargos) =====
const openPisoId = ref(null);
const pisosConPuertasLocal = computed(() => {
    const arr = Array.isArray(props.pisosConPuertas) ? props.pisosConPuertas : [];
    return arr.filter((p) => Array.isArray(p.puertas) && p.puertas.length > 0);
});

function toggleAccordion(pisoId) {
    openPisoId.value = openPisoId.value === pisoId ? null : pisoId;
}

function puertasDelPiso(piso) {
    return piso?.puertas || [];
}

function isPisoAllSelected(piso) {
    const puertas = puertasDelPiso(piso);
    if (puertas.length === 0) return false;
    return puertas.every((puerta) => form.puertas.includes(puerta.id));
}

function isPisoIndeterminate(piso) {
    const puertas = puertasDelPiso(piso);
    if (puertas.length === 0) return false;
    const selected = puertas.filter((puerta) => form.puertas.includes(puerta.id)).length;
    return selected > 0 && selected < puertas.length;
}

function countSelectedInPiso(piso) {
    return puertasDelPiso(piso).filter((puerta) => form.puertas.includes(puerta.id)).length;
}

function togglePisoAll(piso) {
    const puertas = puertasDelPiso(piso);
    const ids = puertas.map((p) => p.id);
    const allSelected = ids.every((id) => form.puertas.includes(id));
    if (allSelected) {
        form.puertas = form.puertas.filter((id) => !ids.includes(id));
    } else {
        form.puertas = [...new Set([...form.puertas, ...ids])];
    }
}

function togglePuerta(puertaId) {
    const idx = form.puertas.indexOf(puertaId);
    if (idx === -1) {
        form.puertas = [...form.puertas, puertaId];
    } else {
        form.puertas = form.puertas.filter((id) => id !== puertaId);
    }
}

const tarjetaNfcProcessing = ref(false);

// Modal de tarjetas asignadas
const modalTarjetasAsignadasOpen = ref(false);
const tarjetasAsignadas = ref([]);
const loadingTarjetasAsignadas = ref(false);

// Sistema de notificaciones
const notification = ref({
    show: false,
    type: "success", // success, error, warning, info
    message: "",
});

const showNotification = (message, type = "success") => {
    notification.value = {
        show: true,
        type,
        message,
    };
    // Auto-cerrar después de 5 segundos para success/info, 7 para error/warning
    setTimeout(() => {
        notification.value.show = false;
    }, type === "error" || type === "warning" ? 7000 : 5000);
};

// Modal de confirmación
const confirmModal = ref({
    show: false,
    message: "",
    callback: null,
});

const showConfirm = (message, callback) => {
    confirmModal.value = {
        show: true,
        message,
        callback,
    };
};

const confirmModalConfirm = () => {
    if (confirmModal.value.callback) {
        confirmModal.value.callback();
    }
    confirmModal.value.show = false;
};

const tarjetaNfcAsignadaAUsuarioSeleccionado = computed(() => {
    if (!form.user_id) return null;
    const usuario = usuariosLocal.value.find((u) => u.id === form.user_id);
    return usuario?.tarjeta_nfc_asignada || null;
});

// Filtrar gerencias por secretaría seleccionada
const gerenciasFiltradas = computed(() => {
    if (!form.secretaria_id) return [];
    return props.gerencias?.filter(g => g.secretaria_id === form.secretaria_id) || [];
});

// Limpiar gerencia cuando cambia la secretaría
const onSecretariaChange = () => {
    form.gerencia_id = null;
};

// Copia local de usuarios para poder agregar visitantes sin recargar toda la página
const usuariosLocal = ref(Array.isArray(props.usuarios) ? [...props.usuarios] : []);
watch(
    () => props.usuarios,
    (newVal) => {
        usuariosLocal.value = Array.isArray(newVal) ? [...newVal] : [];
    }
);

const toIsoDateLocal = (d) => {
    const pad = (n) => String(n).padStart(2, "0");
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
};

const todayIsoLocal = () => toIsoDateLocal(new Date());

// Si no puede crear para otros, pre-seleccionar el usuario actual
onMounted(() => {
    if (!props.puedeCrearParaOtros && currentUser.value && usuariosLocal.value.length === 1) {
        form.user_id = usuariosLocal.value[0].id;
    }

    // showMiQr ya se inicializa antes del onMounted, pero aquí lo confirmamos por si acaso
    if (!props.puedeCrearParaOtros && props.qrPersonal && props.qrPersonal.id && !showMiQr.value) {
        showMiQr.value = true;
    }

    // Sincronizar formTarjetaNfc con form cuando cambia el usuario seleccionado
    watch(
        () => form.user_id,
        (userId) => {
            // Si el usuario seleccionado es visitante y tiene un QR activo, precargar su configuración
            const u = (usuariosLocal.value || []).find((x) => x.id === userId);
            if (u?.role?.name === "visitante") {
                const qr = u?.qr_activo;
                if (qr && Array.isArray(qr.puerta_ids)) {
                    form.puertas = [...qr.puerta_ids];

                    // Gerencia/secretaría
                    form.gerencia_id = qr.gerencia_id ?? null;
                    if (form.gerencia_id) {
                        const g = (props.gerencias || []).find(
                            (x) => x.id === form.gerencia_id,
                        );
                        form.secretaria_id = g?.secretaria_id ?? null;
                    } else {
                        form.secretaria_id = null;
                    }

                    // Responsable
                    form.responsable_id = qr.responsable_id ?? null;

                    // Reglas horarias (si existen)
                    form.hora_inicio = qr.hora_inicio ?? null;
                    form.hora_fin = qr.hora_fin ?? null;
                    form.dias_semana =
                        qr.dias_semana ?? "1,2,3,4,5,6,7";
                    form.fecha_inicio = qr.fecha_inicio ?? form.fecha_inicio;
                    form.fecha_fin = qr.fecha_fin ?? form.fecha_fin;
                } else {
                    // Si no tiene QR activo, evitar que queden puertas del usuario anterior
                    form.puertas = [];
                    form.responsable_id = null;
                }
            }

            if (userId && usuarioSeleccionado.value?.role?.name === 'visitante') {
                formTarjetaNfc.user_id = userId;
                formTarjetaNfc.gerencia_id = form.gerencia_id;
                formTarjetaNfc.puertas = [...form.puertas];
                formTarjetaNfc.hora_inicio = form.hora_inicio;
                formTarjetaNfc.hora_fin = form.hora_fin;
                formTarjetaNfc.dias_semana = form.dias_semana;
                formTarjetaNfc.fecha_inicio = form.fecha_inicio;
                formTarjetaNfc.fecha_fin = form.fecha_fin;
            } else {
                // Si no es visitante, limpiar responsable_id
                if (usuarioSeleccionado.value?.role?.name !== 'visitante') {
                    form.responsable_id = null;
                    responsablePickerQuery.value = "";
                }
                formTarjetaNfc.user_id = null;
                formTarjetaNfc.tarjeta_nfc_id = null;
            }
        }
    );

    // Sincronizar campos relacionados cuando cambian en form
    watch(
        [() => form.gerencia_id, () => form.puertas, () => form.hora_inicio, () => form.hora_fin, () => form.dias_semana, () => form.fecha_inicio, () => form.fecha_fin],
        () => {
            if (usuarioSeleccionado.value?.role?.name === 'visitante' && form.user_id) {
                formTarjetaNfc.gerencia_id = form.gerencia_id;
                formTarjetaNfc.puertas = [...form.puertas];
                formTarjetaNfc.hora_inicio = form.hora_inicio;
                formTarjetaNfc.hora_fin = form.hora_fin;
                formTarjetaNfc.dias_semana = form.dias_semana;
                formTarjetaNfc.fecha_inicio = form.fecha_inicio;
                formTarjetaNfc.fecha_fin = form.fecha_fin;
            }
        }
    );
});

const enviandoCorreo = ref(false);
const mostrarFormulario = ref(false);

const usuarioSeleccionado = computed(() => {
    if (!form.user_id) return null;
    return usuariosLocal.value.find((u) => u.id === form.user_id);
});

const isStaffUsuarioSeleccionado = computed(() => {
    const roleName = usuarioSeleccionado.value?.role?.name;
    return ["servidor_publico", "proveedor", "funcionario"].includes(roleName);
});

const visitanteSinCorreoSeleccionado = computed(() => {
    return (
        usuarioSeleccionado.value?.role?.name === "visitante" &&
        !usuarioSeleccionado.value?.email
    );
});

// ===== Selector buscable de usuarios (Ingreso) =====
const userPickerOpen = ref(false);
const userPickerQuery = ref("");
const userPickerActiveIndex = ref(0);

const formatUsuarioLabel = (u) => {
    if (!u) return "";
    const base = u.name || u.email || "";
    const cc = u.n_identidad ? ` (CC: ${u.n_identidad})` : "";
    return `${base}${cc}`.trim();
};

const openUserPicker = () => {
    if (!props.puedeCrearParaOtros) return; // solo es útil cuando puede escoger entre muchos
    userPickerOpen.value = true;
};
const closeUserPicker = () => {
    userPickerOpen.value = false;
    userPickerActiveIndex.value = 0;
};

const filteredUsuariosForPicker = computed(() => {
    const q = String(userPickerQuery.value || "").trim().toLowerCase();
    let arr = usuariosLocal.value || [];
    if (!props.puedeCrearParaOtros) return arr;
    if (!q) return arr.slice(0, 50);

    const matches = (u) => {
        const name = String(u?.name || "").toLowerCase();
        const email = String(u?.email || "").toLowerCase();
        const cc = String(u?.n_identidad || "").toLowerCase();
        return name.includes(q) || email.includes(q) || cc.includes(q);
    };

    return arr.filter(matches).slice(0, 50);
});

const selectUsuarioFromPicker = (u) => {
    if (!u) return;
    form.user_id = u.id;
    userPickerQuery.value = formatUsuarioLabel(u);
    closeUserPicker();
};

const userPickerMove = (delta) => {
    if (!userPickerOpen.value) {
        openUserPicker();
    }
    const len = filteredUsuariosForPicker.value.length;
    if (len <= 0) return;
    const next = (userPickerActiveIndex.value + delta + len) % len;
    userPickerActiveIndex.value = next;
};

const userPickerSelectActive = () => {
    const u = filteredUsuariosForPicker.value[userPickerActiveIndex.value];
    if (u) selectUsuarioFromPicker(u);
};

// Mantener input sincronizado cuando cambia el user_id (por defaults o por crear visitante)
watch(
    () => form.user_id,
    (id) => {
        const u = usuariosLocal.value.find((x) => x.id === id);
        if (u) {
            userPickerQuery.value = formatUsuarioLabel(u);
        } else if (!id) {
            userPickerQuery.value = "";
        }
    },
    { immediate: true }
);

// ===== Selector buscable de responsables =====
const responsablePickerOpen = ref(false);
const responsablePickerQuery = ref("");
const responsablePickerActiveIndex = ref(0);

const formatResponsableLabel = (r) => {
    if (!r) return "";
    const base = r.name || r.email || "";
    const cargo = r.cargo ? ` - ${r.cargo.name}` : "";
    return `${base}${cargo}`.trim();
};

const openResponsablePicker = () => {
    responsablePickerOpen.value = true;
};
const closeResponsablePicker = () => {
    responsablePickerOpen.value = false;
    responsablePickerActiveIndex.value = 0;
};

const filteredResponsablesForPicker = computed(() => {
    const q = String(responsablePickerQuery.value || "").trim().toLowerCase();
    let arr = props.responsables || [];
    if (!q) return arr.slice(0, 50);

    const matches = (r) => {
        const name = String(r?.name || "").toLowerCase();
        const email = String(r?.email || "").toLowerCase();
        const cargo = String(r?.cargo?.name || "").toLowerCase();
        return name.includes(q) || email.includes(q) || cargo.includes(q);
    };

    return arr.filter(matches).slice(0, 50);
});

const selectResponsableFromPicker = (r) => {
    if (!r) {
        form.responsable_id = null;
        responsablePickerQuery.value = "";
    } else {
        form.responsable_id = r.id;
        responsablePickerQuery.value = formatResponsableLabel(r);
    }
    closeResponsablePicker();
};

const responsablePickerMove = (delta) => {
    if (!responsablePickerOpen.value) {
        openResponsablePicker();
    }
    const len = filteredResponsablesForPicker.value.length + 1; // +1 por la opción "Sin responsable"
    if (len <= 0) return;
    const next = (responsablePickerActiveIndex.value + delta + len) % len;
    responsablePickerActiveIndex.value = next;
};

const responsablePickerSelectActive = () => {
    if (responsablePickerActiveIndex.value === -1) {
        selectResponsableFromPicker(null);
        return;
    }
    const r = filteredResponsablesForPicker.value[responsablePickerActiveIndex.value];
    if (r) selectResponsableFromPicker(r);
};

// Mantener input sincronizado cuando cambia el responsable_id
watch(
    () => form.responsable_id,
    (id) => {
        const r = props.responsables?.find((x) => x.id === id);
        if (r) {
            responsablePickerQuery.value = formatResponsableLabel(r);
        } else if (!id) {
            responsablePickerQuery.value = "";
        }
    },
    { immediate: true }
);

// Cerrar dropdown al click fuera / ESC global
const onDocumentClick = (e) => {
    const el = e?.target;
    // Cerrar selector de usuarios
    if (userPickerOpen.value) {
        // Si el click fue dentro de un input/boton del picker, no cerrar (heurística simple)
        if (el && (el.closest?.(".relative") || el.closest?.("button"))) {
            return;
        }
        closeUserPicker();
    }
    // Cerrar selector de responsables
    if (responsablePickerOpen.value) {
        // Si el click fue dentro de un input/boton del picker, no cerrar (heurística simple)
        if (el && (el.closest?.(".relative") || el.closest?.("button"))) {
            return;
        }
        closeResponsablePicker();
    }
};
const onKeyDownGlobal = (e) => {
    if (e.key === "Escape") {
        closeUserPicker();
        closeResponsablePicker();
    }
};

onMounted(() => {
    if (typeof document !== "undefined") {
        document.addEventListener("click", onDocumentClick);
    }
    if (typeof window !== "undefined") {
        window.addEventListener("keydown", onKeyDownGlobal);
    }
});
onUnmounted(() => {
    if (typeof document !== "undefined") {
        document.removeEventListener("click", onDocumentClick);
    }
    if (typeof window !== "undefined") {
        window.removeEventListener("keydown", onKeyDownGlobal);
    }

    // Limpiar recursos de cámara / preview
    revokeVisitanteFotoPreviewUrl();
    stopVisitanteCameraStream();
});

const storageUrl = (path) => {
    if (!path) return "";
    if (String(path).startsWith("http")) return path;
    return `/storage/${path}`;
};

const fotoPerfilModalPath = ref("");
const openFotoPerfilModal = (path) => {
    fotoPerfilModalPath.value = path || "";
};
const closeFotoPerfilModal = () => {
    fotoPerfilModalPath.value = "";
};

// Si deja de ser visitante, limpiar departamento destino para evitar enviar basura
// Si se selecciona un visitante, establecer valores por defecto de seguridad
// Si se selecciona un funcionario, limpiar campos de fecha y horario
watch(
    () => usuarioSeleccionado.value?.role?.name,
    (roleName) => {
        if (roleName !== "visitante") {
            form.secretaria_id = null;
            form.gerencia_id = null;
            form.pisos = [];
            form.puertas = [];
        } else {
            // Cuando se selecciona un visitante, establecer valores por defecto de seguridad
            const fechaHoy = todayIsoLocal(); // Formato YYYY-MM-DD (local)

            // Para visitantes, no establecer hora_inicio ni hora_fin
            form.hora_inicio = null;
            form.hora_fin = null;

            if (!form.fecha_inicio) {
                form.fecha_inicio = fechaHoy;
            }
            if (!form.fecha_fin) {
                // Predeterminado: el día vigente (mismo día de inicio)
                form.fecha_fin = form.fecha_inicio || fechaHoy;
            }

            // No preseleccionar puertas automáticamente: el operador decide en el acordeón
        }

        // Si se selecciona staff (servidor público/proveedor), limpiar campos de fecha y horario
        if (["servidor_publico", "proveedor", "funcionario"].includes(roleName)) {
            form.hora_inicio = null;
            form.hora_fin = null;
            form.fecha_inicio = null;
            form.fecha_fin = null;
            form.dias_semana = "1,2,3,4,5,6,7";
        }
    }
);

const submit = () => {
    // Siempre: todos los días
    form.dias_semana = "1,2,3,4,5,6,7";
    form.post(route("ingreso.generate"), {
        preserveScroll: true,
    });
};

// Inicializar showMiQr: si no puede crear para otros y tiene QR personal, mostrarlo automáticamente
const showMiQr = ref(!props.puedeCrearParaOtros && props.qrPersonal && props.qrPersonal.id ? true : false);
const miQrRef = ref(null);
const irAMiQr = async () => {
    showMiQr.value = true;
    await nextTick();
    // Si existe QR personal, solo mostrarlo y hacer scroll. Si no, generar uno para el usuario actual.
    if (!props.qrPersonal && puedeGenerarQr.value && currentUser.value?.id) {
        form.user_id = currentUser.value.id;
        submit();
        return;
    }
    if (miQrRef.value?.scrollIntoView) {
        miQrRef.value.scrollIntoView({ behavior: "smooth", block: "start" });
    }
};

const enviarCorreo = () => {
    if (!qrGeneradoLocal.value) return;

    const email = prompt(
        "Ingresa el correo electrónico donde enviar el QR:",
        qrGeneradoLocal.value.user_email || ""
    );

    if (!email) return;

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showNotification("Por favor ingresa un correo electrónico válido.", "error");
        return;
    }

    enviandoCorreo.value = true;

    router.post(
        route("ingreso.send-email", { qr: qrGeneradoLocal.value.id }),
        {
            email,
            token: qrGeneradoLocal.value.token,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                enviandoCorreo.value = false;
            },
        }
    );
};

const limpiarDatos = () => {
    // Cerrar pickers abiertos
    closeUserPicker();
    closeResponsablePicker();

    // Ocultar QR generado (dejar la pantalla como recién abierta)
    qrGeneradoLocal.value = null;
    showMiQr.value = false;

    // Resetear formularios y errores
    form.clearErrors();
    form.reset();
    formTarjetaNfc.clearErrors();
    formTarjetaNfc.reset();

    // Resetear UI auxiliar
    openPisoId.value = null;
    responsablePickerQuery.value = "";

    // Estado inicial: sin usuario seleccionado
    form.user_id = null;
    userPickerQuery.value = "";
};

const generarNuevo = () => {
    form.reset();
    form.user_id = null;
    form.puertas = [];
};

// ===== Visitante modal =====
const visitanteModalOpen = ref(false);
const visitanteProcessing = ref(false);
const visitanteServerError = ref("");
const visitanteErrors = ref({});
const visitanteFotoPreviewUrl = ref("");
const revokeVisitanteFotoPreviewUrl = () => {
    if (visitanteFotoPreviewUrl.value) {
        try {
            URL.revokeObjectURL(visitanteFotoPreviewUrl.value);
        } catch {
            // ignore
        }
        visitanteFotoPreviewUrl.value = "";
    }
};
const visitanteForm = ref({
    nombre: "",
    apellido: "",
    email: "",
    n_identidad: "",
    observaciones: "",
    foto: null,
});

const setVisitanteFoto = (file) => {
    visitanteForm.value.foto = file instanceof File ? file : null;
    revokeVisitanteFotoPreviewUrl();
    if (visitanteForm.value.foto) {
        visitanteFotoPreviewUrl.value = URL.createObjectURL(visitanteForm.value.foto);
    }
};

const onVisitanteFotoChange = (e) => {
    const file = e?.target?.files?.[0] || null;
    setVisitanteFoto(file);
};

const clearVisitanteFoto = () => {
    setVisitanteFoto(null);
};

// ===== Cámara para foto de visitante =====
const visitanteCameraOpen = ref(false);
const visitanteCameraError = ref("");
const visitanteCameraStarting = ref(false);
const visitanteVideoRef = ref(null);
const visitanteCanvasRef = ref(null);
const visitanteCameraStream = ref(null);
const visitanteCameraFacing = ref("environment"); // 'environment' | 'user'

const stopVisitanteCameraStream = () => {
    const stream = visitanteCameraStream.value;
    if (stream && typeof stream.getTracks === "function") {
        for (const t of stream.getTracks()) {
            try { t.stop(); } catch { /* ignore */ }
        }
    }
    visitanteCameraStream.value = null;
    if (visitanteVideoRef.value) {
        try { visitanteVideoRef.value.srcObject = null; } catch { /* ignore */ }
    }
};

const startVisitanteCamera = async () => {
    visitanteCameraError.value = "";
    visitanteCameraStarting.value = true;

    try {
        if (typeof window === "undefined") {
            visitanteCameraError.value = "La cámara no está disponible en este entorno.";
            return;
        }
        // Requisito típico del navegador: contexto seguro (HTTPS) o localhost
        if (!window.isSecureContext) {
            visitanteCameraError.value = "La cámara requiere HTTPS (o localhost).";
            return;
        }
        const md = navigator?.mediaDevices;
        if (!md?.getUserMedia) {
            visitanteCameraError.value = "Este navegador no soporta acceso a cámara.";
            return;
        }

        stopVisitanteCameraStream();

        // Preferir cámara trasera; si falla, caer a video:true
        let stream = null;
        try {
            stream = await md.getUserMedia({
                video: { facingMode: { ideal: visitanteCameraFacing.value } },
                audio: false,
            });
        } catch {
            stream = await md.getUserMedia({ video: true, audio: false });
        }

        visitanteCameraStream.value = stream;
        await nextTick();
        if (visitanteVideoRef.value) {
            visitanteVideoRef.value.srcObject = stream;
            try { await visitanteVideoRef.value.play(); } catch { /* ignore */ }
        }
    } catch (e) {
        visitanteCameraError.value =
            "No se pudo acceder a la cámara. Revisa permisos del navegador y vuelve a intentar.";
    } finally {
        visitanteCameraStarting.value = false;
    }
};

const openVisitanteCamera = async () => {
    visitanteCameraOpen.value = true;
    await nextTick();
    await startVisitanteCamera();
};

const closeVisitanteCamera = () => {
    stopVisitanteCameraStream();
    visitanteCameraOpen.value = false;
    visitanteCameraError.value = "";
};

const toggleVisitanteCameraFacing = async () => {
    visitanteCameraFacing.value =
        visitanteCameraFacing.value === "environment" ? "user" : "environment";
    if (visitanteCameraOpen.value) {
        await startVisitanteCamera();
    }
};

const takeVisitantePhoto = async () => {
    visitanteCameraError.value = "";
    const video = visitanteVideoRef.value;
    const canvas = visitanteCanvasRef.value;
    if (!video || !canvas) {
        visitanteCameraError.value = "La cámara aún no está lista.";
        return;
    }

    const w = video.videoWidth || 1280;
    const h = video.videoHeight || 720;
    canvas.width = w;
    canvas.height = h;
    const ctx = canvas.getContext("2d");
    if (!ctx) {
        visitanteCameraError.value = "No se pudo inicializar el canvas.";
        return;
    }
    ctx.drawImage(video, 0, 0, w, h);

    const blob = await new Promise((resolve) => {
        canvas.toBlob(
            (b) => resolve(b),
            "image/jpeg",
            0.9
        );
    });

    if (!blob) {
        visitanteCameraError.value = "No se pudo capturar la foto.";
        return;
    }

    const file = new File([blob], `visitante_${Date.now()}.jpg`, { type: blob.type });
    setVisitanteFoto(file);
    closeVisitanteCamera();
};

const openVisitanteModal = () => {
    visitanteServerError.value = "";
    visitanteErrors.value = {};
    visitanteModalOpen.value = true;
};

const closeVisitanteModal = () => {
    if (visitanteProcessing.value) return;
    if (visitanteCameraOpen.value) closeVisitanteCamera();
    visitanteModalOpen.value = false;
};

const resetVisitanteForm = () => {
    revokeVisitanteFotoPreviewUrl();
    visitanteForm.value = {
        nombre: "",
        apellido: "",
        email: "",
        n_identidad: "",
        observaciones: "",
        foto: null,
    };
    visitanteErrors.value = {};
    visitanteServerError.value = "";
};

const submitVisitante = async () => {
    visitanteProcessing.value = true;
    visitanteErrors.value = {};
    visitanteServerError.value = "";

    try {
        const fd = new FormData();
        fd.append("nombre", visitanteForm.value.nombre || "");
        fd.append("apellido", visitanteForm.value.apellido || "");
        fd.append("email", visitanteForm.value.email || "");
        fd.append("n_identidad", visitanteForm.value.n_identidad || "");
        fd.append("observaciones", visitanteForm.value.observaciones || "");
        if (visitanteForm.value.foto instanceof File) {
            fd.append("foto", visitanteForm.value.foto);
        }

        const res = await window.axios.post(route("ingreso.visitantes.store"), fd, {
            headers: { "Content-Type": "multipart/form-data" },
        });

        const nuevo = res?.data?.data;
        if (!nuevo || !nuevo.id) {
            visitanteServerError.value = "Respuesta inesperada del servidor. Verifica tu sesión/permisos e intenta nuevamente.";
            return;
        }
        if (nuevo && nuevo.id) {
            // Evitar duplicados (por si el usuario recarga o hay retry)
            const existe = usuariosLocal.value.some((u) => u.id === nuevo.id);
            if (!existe) {
                usuariosLocal.value = [nuevo, ...usuariosLocal.value];
            }

            // Si puede generar QR para otros, dejarlo seleccionado para generar el QR inmediatamente
            if (props.puedeCrearParaOtros) {
                form.user_id = nuevo.id;
            }
        }

        // Cerrar la modal y resetear el formulario después de crear exitosamente
        visitanteProcessing.value = false;
        resetVisitanteForm();
        closeVisitanteModal();
    } catch (err) {
        const status = err?.response?.status;
        if (status === 422) {
            const raw = err?.response?.data?.errors || {};
            const normalized = {};
            for (const key of Object.keys(raw)) {
                const val = raw[key];
                normalized[key] = Array.isArray(val) ? (val[0] || "") : (val || "");
            }
            visitanteErrors.value = normalized;
        } else if (status === 403) {
            visitanteServerError.value = "No tienes permiso para crear visitantes.";
        } else {
            visitanteServerError.value = "Error al crear el visitante. Intenta nuevamente.";
        }
    } finally {
        visitanteProcessing.value = false;
    }
};

// ===== Asignar Tarjeta NFC =====
const asignarTarjetaNfc = async () => {
    if (!formTarjetaNfc.tarjeta_nfc_id || !form.user_id) {
        showNotification("Por favor selecciona un usuario y una tarjeta NFC.", "warning");
        return;
    }

    // Sincronizar datos del form principal al formTarjetaNfc
    formTarjetaNfc.user_id = form.user_id;
    formTarjetaNfc.gerencia_id = form.gerencia_id;
    formTarjetaNfc.puertas = [...form.puertas];
    formTarjetaNfc.hora_inicio = form.hora_inicio;
    formTarjetaNfc.hora_fin = form.hora_fin;
    // Siempre: todos los días
    formTarjetaNfc.dias_semana = "1,2,3,4,5,6,7";
    formTarjetaNfc.fecha_inicio = form.fecha_inicio;
    formTarjetaNfc.fecha_fin = form.fecha_fin;

    tarjetaNfcProcessing.value = true;
    formTarjetaNfc.clearErrors();

    try {
        await window.axios.post(route("ingreso.tarjetas-nfc.asignar"), {
            tarjeta_nfc_id: formTarjetaNfc.tarjeta_nfc_id,
            user_id: formTarjetaNfc.user_id,
            gerencia_id: formTarjetaNfc.gerencia_id,
            puertas: formTarjetaNfc.puertas,
            hora_inicio: formTarjetaNfc.hora_inicio,
            hora_fin: formTarjetaNfc.hora_fin,
            dias_semana: formTarjetaNfc.dias_semana,
            fecha_inicio: formTarjetaNfc.fecha_inicio,
            fecha_fin: formTarjetaNfc.fecha_fin,
        });

        showNotification("Tarjeta NFC asignada correctamente.", "success");

        // Actualizar estado local para que se refleje sin recargar toda la página
        const assignedUserId = formTarjetaNfc.user_id;
        const assignedTarjetaId = formTarjetaNfc.tarjeta_nfc_id;
        const tarjetaObj =
            (props.tarjetasNfcDisponibles || []).find((t) => t.id === assignedTarjetaId) ||
            { id: assignedTarjetaId, codigo: String(assignedTarjetaId) };
        usuariosLocal.value = (usuariosLocal.value || []).map((u) =>
            u.id === assignedUserId ? { ...u, tarjeta_nfc_asignada: tarjetaObj } : u
        );

        // Limpiar los datos del formulario para buscar a otro visitante
        form.reset();
        formTarjetaNfc.reset();

        // Recargar también usuarios para mantener consistencia con el backend
        router.reload({ only: ["tarjetasNfcDisponibles", "usuarios"], preserveScroll: true });
    } catch (err) {
        const status = err?.response?.status;
        if (status === 422) {
            const msg = err?.response?.data?.message;
            if (msg) {
                showNotification(msg, "error");
                return;
            }
            const raw = err?.response?.data?.errors || {};
            for (const key of Object.keys(raw)) {
                const val = raw[key];
                const msg = Array.isArray(val) ? (val[0] || "") : (val || "");
                if (msg) formTarjetaNfc.setError(key, msg);
            }
        } else if (status === 403) {
            showNotification("No tienes permiso para asignar tarjetas NFC.", "error");
        } else {
            showNotification("Error al asignar la tarjeta NFC. Intenta nuevamente.", "error");
        }
    } finally {
        tarjetaNfcProcessing.value = false;
    }
};

const desasignarTarjetaNfc = async () => {
    if (!tarjetaNfcAsignadaAUsuarioSeleccionado.value || !form.user_id) return;

    showConfirm("¿Desasignar esta tarjeta NFC del visitante seleccionado?", () => {
        ejecutarDesasignarTarjetaNfc();
    });
};

const ejecutarDesasignarTarjetaNfc = async () => {

    tarjetaNfcProcessing.value = true;
    formTarjetaNfc.clearErrors();

    try {
        await window.axios.post(route("ingreso.tarjetas-nfc.desasignar"), {
            tarjeta_nfc_id: tarjetaNfcAsignadaAUsuarioSeleccionado.value.id,
            user_id: form.user_id,
        });

        showNotification("Tarjeta NFC desasignada correctamente.", "success");
        router.reload({ only: ["tarjetasNfcDisponibles", "usuarios"], preserveScroll: true });
    } catch (err) {
        const status = err?.response?.status;
        if (status === 422) {
            showNotification(err?.response?.data?.message || "No se pudo desasignar la tarjeta.", "error");
        } else if (status === 403) {
            showNotification("No tienes permiso para desasignar tarjetas NFC.", "error");
        } else {
            showNotification("Error al desasignar la tarjeta NFC. Intenta nuevamente.", "error");
        }
    } finally {
        tarjetaNfcProcessing.value = false;
    }
};

// ===== Modal Tarjetas Asignadas =====
const abrirModalTarjetasAsignadas = async () => {
    modalTarjetasAsignadasOpen.value = true;
    loadingTarjetasAsignadas.value = true;
    tarjetasAsignadas.value = [];

    try {
        const response = await window.axios.get(route("ingreso.tarjetas-nfc.asignadas"));
        tarjetasAsignadas.value = response.data.data || [];
    } catch (err) {
        const status = err?.response?.status;
        if (status === 403) {
            showNotification("No tienes permiso para ver las tarjetas asignadas.", "error");
            modalTarjetasAsignadasOpen.value = false;
        } else {
            showNotification("Error al cargar las tarjetas asignadas. Intenta nuevamente.", "error");
        }
    } finally {
        loadingTarjetasAsignadas.value = false;
    }
};

const closeModalTarjetasAsignadas = () => {
    modalTarjetasAsignadasOpen.value = false;
};

const desasignarTarjetaDesdeModal = (tarjeta) => {
    if (!tarjeta.user) return;

    showConfirm(
        `¿Desasignar la tarjeta NFC "${tarjeta.codigo}" del usuario "${tarjeta.user.name}"?`,
        async () => {
            tarjetaNfcProcessing.value = true;

            try {
                await window.axios.post(route("ingreso.tarjetas-nfc.desasignar"), {
                    tarjeta_nfc_id: tarjeta.id,
                    user_id: tarjeta.user.id,
                });

                showNotification("Tarjeta NFC desasignada correctamente.", "success");

                // Remover la tarjeta de la lista local
                tarjetasAsignadas.value = tarjetasAsignadas.value.filter((t) => t.id !== tarjeta.id);

                // Recargar datos necesarios
                router.reload({ only: ["tarjetasNfcDisponibles", "usuarios"], preserveScroll: true });
            } catch (err) {
                const status = err?.response?.status;
                if (status === 422) {
                    showNotification(err?.response?.data?.message || "No se pudo desasignar la tarjeta.", "error");
                } else if (status === 403) {
                    showNotification("No tienes permiso para desasignar tarjetas NFC.", "error");
                } else {
                    showNotification("Error al desasignar la tarjeta NFC. Intenta nuevamente.", "error");
                }
            } finally {
                tarjetaNfcProcessing.value = false;
            }
        }
    );
};
</script>
