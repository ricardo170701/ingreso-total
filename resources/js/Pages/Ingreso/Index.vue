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
                                    Usa este QR para acceder a las puertas autorizadas.
                                </li>
                                <li>
                                    Puedes descargar o imprimir el código QR mostrado.
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
                        <div class="relative">
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
                                label="Gerencia destino (obligatorio para visitantes)"
                                :error="form.errors.gerencia_id"
                            >
                                <select
                                    v-model="form.gerencia_id"
                                    :disabled="!form.secretaria_id"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                    required
                                >
                                    <option :value="null">Selecciona una gerencia</option>
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
                                    Este dato se registra en el QR del visitante.
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
                                Busca y selecciona un servidor público o contratista como responsable del ingreso del visitante (opcional)
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
                                    class="flex items-center gap-2 p-2 hover:bg-slate-50 dark:hover:bg-slate-600 rounded transition-colors duration-200 cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :value="p.id"
                                        v-model="form.pisos"
                                        class="h-4 w-4 text-green-600 dark:text-green-500 border-slate-300 dark:border-slate-600 rounded focus:ring-green-500 dark:focus:ring-green-400"
                                    />
                                    <div class="flex-1">
                                        <span class="font-medium text-slate-900 dark:text-white">{{ p.nombre }}</span>
                                    </div>
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Selecciona al menos un piso. El sistema asignará automáticamente las puertas activas de esos pisos.
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

                    <!-- Mensaje informativo para staff -->
                    <div
                        v-if="puedeCrearParaOtros && isStaffUsuarioSeleccionado"
                        class="border-t border-slate-200 dark:border-slate-700 pt-4"
                    >
                        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-3 transition-colors duration-200">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <strong>Nota:</strong> Para servidor público/contratista, el código QR estará activo hasta la fecha de expiración del contrato del usuario o hasta que se marque como inactivo. No se requieren fechas ni horarios adicionales.
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
                            class="bg-white dark:bg-white rounded-xl p-3 sm:p-4 border-2 border-slate-200 dark:border-slate-600 shadow-lg dark:shadow-slate-900/50 w-full sm:w-auto transition-all duration-200"
                        >
                            <div v-if="typeof qrGenerado.svg === 'string'" class="flex justify-center">
                                <div
                                    class="max-w-full overflow-x-auto [&>svg]:max-w-full [&>svg]:h-auto [&>svg]:drop-shadow-sm"
                                    v-html="qrGenerado.svg"
                                ></div>
                            </div>
                            <div v-else class="text-red-600 dark:text-red-400 text-sm">
                                Error: El SVG no se generó correctamente. Tipo:
                                {{ typeof qrGenerado.svg }}
                            </div>
                        </div>
                        <div class="mt-4 space-y-2 text-sm">
                            <p class="text-slate-700 dark:text-slate-300">
                                <span class="font-medium text-slate-900 dark:text-slate-100">Usuario:</span>
                                <span class="ml-2">{{ qrGenerado.user_name }}</span>
                            </p>
                            <p class="text-slate-700 dark:text-slate-300">
                                <span class="font-medium text-slate-900 dark:text-slate-100">Código:</span>
                                <code
                                    class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-slate-100 rounded text-xs ml-2 inline-block align-middle break-all max-w-full font-mono border border-slate-200 dark:border-slate-600"
                                >
                                    {{ qrGenerado.token }}
                                </code>
                            </p>
                            <p class="text-slate-700 dark:text-slate-300">
                                <span class="font-medium text-slate-900 dark:text-slate-100">Expira:</span>
                                <span class="ml-2">{{ qrGenerado.expires_at_formatted }}</span>
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

                    <FormField label="Email" :error="visitanteErrors.email">
                        <input
                            v-model="visitanteForm.email"
                            type="email"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            placeholder="correo@ejemplo.com"
                            required
                        />
                    </FormField>

                    <FormField label="Foto (opcional)" :error="visitanteErrors.foto">
                        <input
                            type="file"
                            accept="image/*"
                            @change="onVisitanteFotoChange"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                        />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            JPG/PNG, máx 4MB.
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
    secretarias: Array,
    gerencias: Array,
    responsables: Array,
    qrGenerado: Object,
    puedeCrearParaOtros: Boolean,
    qrPersonal: Object,
    tarjetasNfcDisponibles: Array,
});

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
    pisos: [],
    hora_inicio: null,
    hora_fin: null,
    dias_semana: "1,2,3,4,5,6,7",
    fecha_inicio: null,
    fecha_fin: null,
});

const tarjetaNfcProcessing = ref(false);

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

// Si no puede crear para otros, pre-seleccionar el usuario actual
onMounted(() => {
    if (!props.puedeCrearParaOtros && currentUser.value && usuariosLocal.value.length === 1) {
        form.user_id = usuariosLocal.value[0].id;
    }

    // showMiQr ya se inicializa antes del onMounted, pero aquí lo confirmamos por si acaso
    if (!props.puedeCrearParaOtros && props.qrPersonal && props.qrPersonal.id && !showMiQr.value) {
        showMiQr.value = true;
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
        const usuario = usuariosLocal.value.find((u) => u.id === form.user_id);
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

    // Sincronizar formTarjetaNfc con form cuando cambia el usuario seleccionado
    watch(
        () => form.user_id,
        (userId) => {
            if (userId && usuarioSeleccionado.value?.role?.name === 'visitante') {
                formTarjetaNfc.user_id = userId;
                formTarjetaNfc.gerencia_id = form.gerencia_id;
                formTarjetaNfc.pisos = [...form.pisos];
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
        [() => form.gerencia_id, () => form.pisos, () => form.hora_inicio, () => form.hora_fin, () => form.dias_semana, () => form.fecha_inicio, () => form.fecha_fin],
        () => {
            if (usuarioSeleccionado.value?.role?.name === 'visitante' && form.user_id) {
                formTarjetaNfc.gerencia_id = form.gerencia_id;
                formTarjetaNfc.pisos = [...form.pisos];
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
    return ["servidor_publico", "contratista", "funcionario"].includes(roleName);
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

        // Si se selecciona staff (servidor público/contratista), limpiar campos de fecha y horario
        if (["servidor_publico", "contratista", "funcionario"].includes(roleName)) {
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
const visitanteForm = ref({
    nombre: "",
    apellido: "",
    email: "",
    n_identidad: "",
    observaciones: "",
    foto: null,
});

const onVisitanteFotoChange = (e) => {
    const file = e?.target?.files?.[0] || null;
    visitanteForm.value.foto = file;
};

const openVisitanteModal = () => {
    visitanteServerError.value = "";
    visitanteErrors.value = {};
    visitanteModalOpen.value = true;
};

const closeVisitanteModal = () => {
    if (visitanteProcessing.value) return;
    visitanteModalOpen.value = false;
};

const resetVisitanteForm = () => {
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

        closeVisitanteModal();
        resetVisitanteForm();
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
        alert("Por favor selecciona un usuario y una tarjeta NFC.");
        return;
    }

    // Sincronizar datos del form principal al formTarjetaNfc
    formTarjetaNfc.user_id = form.user_id;
    formTarjetaNfc.gerencia_id = form.gerencia_id;
    formTarjetaNfc.pisos = [...form.pisos];
    formTarjetaNfc.hora_inicio = form.hora_inicio;
    formTarjetaNfc.hora_fin = form.hora_fin;
    formTarjetaNfc.dias_semana = form.dias_semana;
    formTarjetaNfc.fecha_inicio = form.fecha_inicio;
    formTarjetaNfc.fecha_fin = form.fecha_fin;

    tarjetaNfcProcessing.value = true;
    formTarjetaNfc.clearErrors();

    try {
        await window.axios.post(route("ingreso.tarjetas-nfc.asignar"), {
            tarjeta_nfc_id: formTarjetaNfc.tarjeta_nfc_id,
            user_id: formTarjetaNfc.user_id,
            gerencia_id: formTarjetaNfc.gerencia_id,
            pisos: formTarjetaNfc.pisos,
            hora_inicio: formTarjetaNfc.hora_inicio,
            hora_fin: formTarjetaNfc.hora_fin,
            dias_semana: formTarjetaNfc.dias_semana,
            fecha_inicio: formTarjetaNfc.fecha_inicio,
            fecha_fin: formTarjetaNfc.fecha_fin,
        });

        alert("Tarjeta NFC asignada correctamente.");
        formTarjetaNfc.tarjeta_nfc_id = null;
        router.reload({ only: ["tarjetasNfcDisponibles"], preserveScroll: true });
    } catch (err) {
        const status = err?.response?.status;
        if (status === 422) {
            const raw = err?.response?.data?.errors || {};
            for (const key of Object.keys(raw)) {
                const val = raw[key];
                const msg = Array.isArray(val) ? (val[0] || "") : (val || "");
                if (msg) formTarjetaNfc.setError(key, msg);
            }
        } else if (status === 403) {
            alert("No tienes permiso para asignar tarjetas NFC.");
        } else {
            alert("Error al asignar la tarjeta NFC. Intenta nuevamente.");
        }
    } finally {
        tarjetaNfcProcessing.value = false;
    }
};

const desasignarTarjetaNfc = async () => {
    if (!tarjetaNfcAsignadaAUsuarioSeleccionado.value || !form.user_id) return;
    if (!confirm("¿Desasignar esta tarjeta NFC del visitante seleccionado?")) return;

    tarjetaNfcProcessing.value = true;
    formTarjetaNfc.clearErrors();

    try {
        await window.axios.post(route("ingreso.tarjetas-nfc.desasignar"), {
            tarjeta_nfc_id: tarjetaNfcAsignadaAUsuarioSeleccionado.value.id,
            user_id: form.user_id,
        });

        alert("Tarjeta NFC desasignada correctamente.");
        router.reload({ only: ["tarjetasNfcDisponibles", "usuarios"], preserveScroll: true });
    } catch (err) {
        const status = err?.response?.status;
        if (status === 422) {
            alert(err?.response?.data?.message || "No se pudo desasignar la tarjeta.");
        } else if (status === 403) {
            alert("No tienes permiso para desasignar tarjetas NFC.");
        } else {
            alert("Error al desasignar la tarjeta NFC. Intenta nuevamente.");
        }
    } finally {
        tarjetaNfcProcessing.value = false;
    }
};
</script>
