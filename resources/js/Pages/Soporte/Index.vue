<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <div>
                <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                    Centro de Soporte
                </h1>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Encuentra respuestas a las preguntas más frecuentes sobre el uso del sistema.
                </p>
            </div>

            <!-- Preguntas Frecuentes -->
            <div class="space-y-4">
                <!-- Pregunta 1: Registrar Usuarios -->
                <div
                    v-if="!esVisitante && hasAnyPermission(['view_users', 'create_users'])"
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
                >
                    <button
                        @click="toggleFAQ('faq1')"
                        class="w-full text-left p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span>👤</span>
                            <span>¿Cómo registrar usuarios (enrollar)?</span>
                        </h2>
                        <svg
                            class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isOpen('faq1') }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        v-show="isOpen('faq1')"
                        class="px-6 pb-6 space-y-3 text-sm text-slate-700 dark:text-slate-300 transition-all"
                    >
                        <p>
                            Para registrar un nuevo usuario en el sistema de control de accesos, sigue estos pasos:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede al módulo de Usuarios:</strong> Haz clic en "Usuarios" en el menú lateral (requiere permisos <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded text-slate-900 dark:text-slate-100 transition-colors duration-200">view_users</code> y <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded text-slate-900 dark:text-slate-100 transition-colors duration-200">create_users</code>).
                            </li>
                            <li>
                                <strong>Crear nuevo usuario:</strong> Haz clic en el botón "Nuevo Usuario" o "Crear Usuario" en la parte superior de la lista.
                            </li>
                            <li>
                                <strong>Completa el formulario:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li><strong>Correo electrónico:</strong> Dirección de correo única que servirá como usuario de acceso</li>
                                    <li><strong>Contraseña:</strong> Establece una contraseña temporal (el usuario deberá cambiarla en su primer acceso)</li>
                                    <li><strong>Datos personales:</strong> Nombre completo, apellido, número de documento</li>
                                    <li><strong>Departamento:</strong> Selecciona el departamento al que pertenece (opcional)</li>
                                    <li><strong>Tipo de vinculación:</strong> Selecciona entre:
                                        <ul class="list-circle list-inside ml-4 mt-1">
                                            <li><strong>Visitante:</strong> Acceso temporal y limitado</li>
                                            <li><strong>Servidor Público:</strong> Personal de la organización</li>
                                            <li><strong>Proveedor:</strong> Personal externo con acceso regular</li>
                                        </ul>
                                    </li>
                                    <li><strong>Cargo:</strong> Asigna un cargo para definir sus permisos de acceso a puertas y funciones del sistema</li>
                                    <li><strong>Fecha de expiración:</strong> Configura si el acceso tiene fecha límite (opcional)</li>
                                    <li><strong>Activo:</strong> Marca esta opción para habilitar el acceso inmediatamente</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Guardar:</strong> Haz clic en "Guardar" o "Crear Usuario" para completar el registro.
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>Nota importante:</strong> Debes proporcionarle manualmente el correo electrónico y la contraseña temporal al usuario. En su primer acceso, el sistema le solicitará cambiar la contraseña por seguridad.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pregunta 2: Configurar Permisos -->
                <div
                    v-if="!esVisitante && hasPermission('view_cargos')"
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
                >
                    <button
                        @click="toggleFAQ('faq2')"
                        class="w-full text-left p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span>🔐</span>
                            <span>¿Cómo configurar permisos?</span>
                        </h2>
                        <svg
                            class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isOpen('faq2') }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        v-show="isOpen('faq2')"
                        class="px-6 pb-6 space-y-3 text-sm text-slate-700 dark:text-slate-300 transition-all"
                    >
                        <p>
                            Los permisos se configuran a través de los <strong>Cargos</strong>. Cada cargo define qué puede hacer un usuario en el sistema y a qué puertas físicas tiene acceso:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede al módulo de Cargos:</strong> Haz clic en "Cargos" o "Permisos" en el menú lateral (requiere permiso <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded text-slate-900 dark:text-slate-100 transition-colors duration-200">view_cargos</code>).
                            </li>
                            <li>
                                <strong>Selecciona o crea un cargo:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Si el cargo ya existe, haz clic en "Ver" o "Gestionar Permisos"</li>
                                    <li>Si necesitas crear uno nuevo, haz clic en "Nuevo Cargo" y completa el nombre y descripción</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Configura permisos del sistema (software):</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>En la sección <strong>"Permisos de la Sidebar"</strong>, marca los permisos que controlan qué botones del menú puede ver el usuario (ej: ver usuarios, ver puertas, ver reportes)</li>
                                    <li>En las demás secciones, marca los permisos de crear, editar, eliminar o gestionar según corresponda</li>
                                    <li>Estos permisos determinan qué funciones del sistema web puede usar el usuario</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Configura permisos físicos (acceso a puertas):</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>En la sección "Agregar Permiso de Puerta", selecciona las puertas específicas a las que tendrá acceso</li>
                                    <li>Para cada puerta, configura:
                                        <ul class="list-circle list-inside ml-4 mt-1">
                                            <li><strong>Horarios:</strong> Hora de inicio y fin del acceso</li>
                                            <li><strong>Días de la semana:</strong> Qué días es válido el acceso</li>
                                            <li><strong>Fechas:</strong> Fecha de inicio y fin de validez (opcional)</li>
                                        </ul>
                                    </li>
                                    <li>Marca como activo para habilitar el acceso a esa puerta</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Asigna el cargo al usuario:</strong> Al crear o editar un usuario en el módulo de Usuarios, selecciona el cargo que acabas de configurar en el campo "Cargo".
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>💡 Tip importante:</strong> Los permisos del sistema controlan qué secciones del menú puede ver el usuario en la aplicación web. Los permisos físicos (puertas) controlan a qué puertas físicas puede acceder con su QR o tarjeta NFC. <strong>Para funcionarios:</strong> Los permisos físicos del cargo se aplican automáticamente al generar el QR - no necesitas configurarlos manualmente en el QR. <strong>Para visitantes:</strong> Los permisos se configuran explícitamente al generar el QR seleccionando los pisos/puertas permitidos.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pregunta 3: Subir Mantenimiento -->
                <div
                    v-if="!esVisitante && hasAnyPermission(['view_mantenimientos', 'create_mantenimientos'])"
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
                >
                    <button
                        @click="toggleFAQ('faq3')"
                        class="w-full text-left p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span>🔧</span>
                            <span>¿Cómo subir un mantenimiento?</span>
                        </h2>
                        <svg
                            class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isOpen('faq3') }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        v-show="isOpen('faq3')"
                        class="px-6 pb-6 space-y-3 text-sm text-slate-700 dark:text-slate-300 transition-all"
                    >
                        <p>
                            Para registrar un mantenimiento de una puerta de acceso, sigue estos pasos detallados:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede a una puerta:</strong> Ve al módulo <strong>Puertas</strong> en el menú lateral y haz clic en <strong>"Ver Puerta"</strong> en la puerta que necesitas registrar.
                            </li>
                            <li>
                                <strong>Crear nuevo mantenimiento:</strong> En la sección "Hoja de Vida" o "Mantenimientos", haz clic en el botón <strong>"Nuevo Mantenimiento"</strong>.
                            </li>
                            <li>
                                <strong>Completa la información básica:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>La <strong>puerta</strong> ya estará seleccionada automáticamente</li>
                                    <li>Selecciona la <strong>fecha</strong> en que se realizó o se realizará el mantenimiento</li>
                                    <li>Elige el <strong>tipo</strong> de mantenimiento:
                                        <ul class="list-circle list-inside ml-4 mt-1">
                                            <li><strong>Realizado:</strong> Si el mantenimiento ya se completó (aparecerá en el historial)</li>
                                            <li><strong>Programado:</strong> Si el mantenimiento está programado para el futuro (la puerta mostrará un indicador visual amarillo/rojo en el sistema)</li>
                                        </ul>
                                    </li>
                                    <li>Si es programado, indica la <strong>fecha de fin</strong> del período de mantenimiento</li>
                                </ul>
                            </li>

                            <li>
                                <strong>Agrega información adicional:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>En <strong>"Descripción de mantenimiento"</strong> agrega notas detalladas sobre el mantenimiento, acciones realizadas, repuestos usados, etc.</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Sube imágenes de evidencia:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Haz clic en <strong>"Seleccionar Imágenes"</strong> o arrastra archivos al área designada</li>
                                    <li>Puedes subir hasta <strong>10 imágenes</strong> por mantenimiento</li>
                                    <li>Cada imagen debe tener un <strong>máximo de 2MB</strong></li>
                                    <li>Formatos aceptados: <strong>JPG, PNG o GIF</strong></li>
                                    <li>Las imágenes sirven como evidencia fotográfica del estado de la puerta</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Guardar:</strong> Haz clic en <strong>"Guardar"</strong> o <strong>"Crear Mantenimiento"</strong> para completar el registro. El mantenimiento quedará registrado en la hoja de vida de la puerta.
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>📋 Nota importante:</strong> Si registras un mantenimiento programado, la puerta mostrará un indicador amarillo en el sistema mientras esté en período de mantenimiento, y cambiará a rojo si pasa la fecha programada sin que se marque como realizado. Esto ayuda a identificar puertas que requieren atención.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pregunta 4: Generar Códigos QR -->
                <div
                    v-if="esVisitante || hasPermission('view_ingreso')"
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
                >
                    <button
                        @click="toggleFAQ('faq4')"
                        class="w-full text-left p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span>📱</span>
                            <span>¿Cómo generar códigos QR para acceso?</span>
                        </h2>
                        <svg
                            class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isOpen('faq4') }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        v-show="isOpen('faq4')"
                        class="px-6 pb-6 space-y-3 text-sm text-slate-700 dark:text-slate-300 transition-all"
                    >
                        <p>
                            Para generar un código QR que permita a un usuario acceder a las puertas del sistema, sigue estos pasos. <strong>Nota:</strong> Los visitantes NO pueden generar QR, solo pueden ver y descargar su QR activo si ya fue generado.
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede al módulo de Ingreso:</strong> Haz clic en <strong>"Ingreso"</strong> en el menú lateral. Esta sección está disponible para todos los usuarios autenticados, pero solo usuarios con permisos pueden generar QR.
                            </li>
                            <li>
                                <strong>Selecciona el usuario:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Si tienes el permiso <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded text-slate-900 dark:text-slate-100 transition-colors duration-200">create_ingreso_otros</code>, podrás seleccionar cualquier usuario del sistema</li>
                                    <li>Si no tienes ese permiso, solo podrás generar QR para ti mismo (el selector estará deshabilitado y mostrará tu nombre)</li>
                                    <li><strong>Importante:</strong> Los visitantes NO pueden generar QR. Solo pueden ver y descargar su QR activo si ya fue generado por un administrador. Los visitantes deben solicitar que un usuario con permisos les genere el QR.</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Configura el acceso:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li><strong>Para funcionarios (servidor público/proveedor):</strong> Las puertas se asignan automáticamente según los permisos configurados en el cargo del usuario. No necesitas seleccionar puertas manualmente. El sistema usará los permisos físicos (puertas) que tiene configurados el cargo del usuario.</li>
                                    <li><strong>Para visitantes:</strong> Debes seleccionar los pisos (el sistema expandirá automáticamente a las puertas activas de esos pisos). Las puertas se guardan explícitamente en el QR del visitante.</li>
                                    <li>Si el usuario es visitante sin correo, solo se puede asignar tarjeta NFC (no se genera QR)</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Configura horarios y fechas (opcional):</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li><strong>Fechas de validez:</strong> Fecha de inicio y fecha de fin del período de validez del QR</li>
                                    <li>Si no configuras horarios, el acceso será válido las 24 horas del día</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Generar QR:</strong> Haz clic en el botón <strong>"Generar QR"</strong>. El sistema creará el código y lo mostrará en pantalla.
                            </li>
                            <li>
                                <strong>Descargar o correo:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Haz clic en <strong>"Descargar QR"</strong> para guardar la imagen en tu dispositivo</li>
                                    <li>Si el usuario tiene correo en el sistema, el sistema envía automáticamente una copia del QR a esa dirección al generarlo</li>
                                </ul>
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>📱 Información importante:</strong> Para funcionarios, el QR permanece activo hasta la fecha de expiración del usuario (si está configurada) o indefinidamente si tiene contrato indefinido. El acceso a puertas se evalúa automáticamente según los permisos del cargo del usuario en el momento de usar el QR. Para visitantes, el QR es válido por defecto durante 1 día laborable, aunque puede configurarse para un período mayor. Una vez vencido, el visitante necesitará solicitar que se le genere uno nuevo.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pregunta 5: Ver y Descargar PDF de Mantenimientos -->
                <div
                    v-if="!esVisitante && hasPermission('view_mantenimientos')"
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
                >
                    <button
                        @click="toggleFAQ('faq5')"
                        class="w-full text-left p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span>📄</span>
                            <span>¿Cómo ver y descargar el PDF de un mantenimiento?</span>
                        </h2>
                        <svg
                            class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isOpen('faq5') }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        v-show="isOpen('faq5')"
                        class="px-6 pb-6 space-y-3 text-sm text-slate-700 dark:text-slate-300 transition-all"
                    >
                        <p>
                            Para ver los detalles de un mantenimiento y descargar su PDF en formato oficial:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede a una puerta:</strong> Ve al módulo <strong>Puertas</strong> en el menú lateral y haz clic en <strong>"Ver Puerta"</strong> en la puerta que necesitas consultar.
                            </li>
                            <li>
                                <strong>Busca el mantenimiento:</strong> En la sección <strong>"Mantenimientos"</strong> o <strong>"Hoja de Vida"</strong>, localiza el registro del mantenimiento que deseas ver. Los mantenimientos están ordenados por fecha (más recientes primero).
                            </li>
                            <li>
                                <strong>Ver detalles:</strong> Haz clic en el mantenimiento en la lista o en el botón <strong>"Ver"</strong> para abrir la vista detallada completa.
                            </li>
                            <li>
                                <strong>Descargar PDF:</strong> En la vista de detalle del mantenimiento, localiza y haz clic en el botón <strong>"📄 Descargar PDF"</strong>. El archivo se generará y descargará automáticamente.
                            </li>
                            <li>
                                <strong>Abrir el PDF:</strong> El archivo PDF se guardará en tu carpeta de descargas. Puedes abrirlo con cualquier lector de PDF (Adobe Reader, navegador, etc.) para verlo o imprimirlo.
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>📄 Contenido del PDF:</strong> El documento incluye información completa del equipo (puerta), datos del cliente/organización, evaluación detallada de defectos con niveles de gravedad codificados por colores, observaciones del técnico, referencias a evidencia fotográfica adjunta, resultado de la inspección y espacios designados para firmas de aprobación.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pregunta 6: Exportar Reportes CSV -->
                <div
                    v-if="!esVisitante && hasPermission('view_reportes')"
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
                >
                    <button
                        @click="toggleFAQ('faq6')"
                        class="w-full text-left p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span>📊</span>
                            <span>¿Cómo exportar reportes en formato CSV?</span>
                        </h2>
                        <svg
                            class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isOpen('faq6') }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        v-show="isOpen('faq6')"
                        class="px-6 pb-6 space-y-3 text-sm text-slate-700 dark:text-slate-300 transition-all"
                    >
                        <p>
                            Para exportar datos del sistema en formato CSV compatible con Excel y otras herramientas de análisis:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede al módulo de Reportes:</strong> Haz clic en <strong>"Reportes"</strong> en el menú lateral. Este módulo requiere el permiso <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded text-slate-900 dark:text-slate-100 transition-colors duration-200">view_reportes</code>.
                            </li>
                            <li>
                                <strong>Selecciona el tipo de reporte:</strong> El sistema ofrece 4 tipos de reportes exportables:
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li><strong>Usuarios:</strong> Lista completa de usuarios con sus roles, cargos, departamentos, fechas de creación y estado</li>
                                    <li><strong>Accesos:</strong> Historial completo de accesos a puertas con fecha, hora, usuario, puerta, tipo de evento (entrada/salida) y resultado</li>
                                    <li><strong>Mantenimientos:</strong> Registros de todos los mantenimientos realizados con fechas, técnicos, defectos y observaciones</li>
                                    <li><strong>Puertas:</strong> Información técnica de todas las puertas del sistema incluyendo códigos, IPs, estados y ubicaciones</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Aplica filtros (opcional):</strong> Usa los campos de filtro disponibles para restringir los datos que deseas exportar:
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Filtros por fecha (rango de fechas)</li>
                                    <li>Filtros por usuario, puerta, departamento, etc.</li>
                                    <li>Filtros por estado (activo/inactivo)</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Exportar:</strong> Haz clic en el botón <strong>"📥 Exportar"</strong> correspondiente al tipo de reporte que deseas. El archivo CSV se generará con todos los datos visibles según los filtros aplicados.
                            </li>
                            <li>
                                <strong>Abrir en Excel:</strong> El archivo CSV se descargará automáticamente en tu carpeta de descargas. Ábrelo con Microsoft Excel, Google Sheets o cualquier herramienta de hojas de cálculo para ver y analizar los datos formateados.
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>🔤 Compatibilidad de caracteres:</strong> Los archivos CSV están codificados en UTF-8 con BOM (Byte Order Mark), por lo que los caracteres especiales (á, é, í, ó, ú, ñ, etc.) se verán correctamente en Excel. Si al abrir el archivo no se ven bien los caracteres especiales, en Excel selecciona "Datos" → "Obtener datos" → "Desde texto/CSV" y elige "65001: Unicode (UTF-8)" como codificación de origen.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pregunta 7: Gestionar Departamentos -->
                <div
                    v-if="!esVisitante && hasPermission('view_departamentos')"
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
                >
                    <button
                        @click="toggleFAQ('faq7')"
                        class="w-full text-left p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span>🏢</span>
                            <span>¿Cómo gestionar departamentos?</span>
                        </h2>
                        <svg
                            class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isOpen('faq7') }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        v-show="isOpen('faq7')"
                        class="px-6 pb-6 space-y-3 text-sm text-slate-700 dark:text-slate-300 transition-all"
                    >
                        <p>
                            Los departamentos organizan a los usuarios por área o dependencia dentro de la organización, facilitando la gestión y los reportes:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede al módulo de Departamentos:</strong> Haz clic en <strong>"Departamentos"</strong> en el menú lateral. Este módulo requiere el permiso <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded text-slate-900 dark:text-slate-100 transition-colors duration-200">view_departamentos</code>.
                            </li>
                            <li>
                                <strong>Crear nuevo departamento:</strong> Haz clic en el botón <strong>"Nuevo Departamento"</strong> y completa el formulario:
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li><strong>Nombre:</strong> Nombre descriptivo del departamento (ej: "Recursos Humanos", "Tecnología", "Administración")</li>
                                    <li><strong>Piso:</strong> Piso o nivel donde se encuentra físicamente el departamento (opcional, útil para organizar por ubicación)</li>
                                    <li><strong>Descripción:</strong> Información adicional sobre el departamento, su función o responsabilidades</li>
                                    <li><strong>Activo:</strong> Marca esta opción si el departamento está actualmente activo en la organización</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Asignar a usuarios:</strong> Al crear o editar un usuario en el módulo de Usuarios, selecciona el departamento correspondiente en el campo <strong>"Departamento"</strong>. Cada usuario puede pertenecer a un solo departamento.
                            </li>
                            <li>
                                <strong>Gestionar departamentos existentes:</strong> Puedes editar o desactivar departamentos existentes desde la lista. Los departamentos desactivados no aparecerán en los selectores al crear nuevos usuarios.
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>💡 Tip útil:</strong> Los departamentos son muy útiles para organizar reportes, filtrar usuarios en listados y generar estadísticas por área. Un usuario solo puede pertenecer a un departamento a la vez. Los departamentos también pueden estar organizados dentro de Secretarías y Gerencias para una estructura jerárquica más compleja.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pregunta 8: Protocolo de Emergencia -->
                <div
                    v-if="!esVisitante && hasPermission('view_protocolo')"
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
                >
                    <button
                        @click="toggleFAQ('faq8')"
                        class="w-full text-left p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span>🚨</span>
                            <span>¿Cómo usar el Protocolo de Emergencia?</span>
                        </h2>
                        <svg
                            class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isOpen('faq8') }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        v-show="isOpen('faq8')"
                        class="px-6 pb-6 space-y-3 text-sm text-slate-700 dark:text-slate-300 transition-all"
                    >
                        <p>
                            El Protocolo de Emergencia permite abrir todas las puertas del sistema simultáneamente en caso de emergencia real, facilitando la evacuación o acceso de emergencia:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Requisito de permiso:</strong> Necesitas el permiso especial <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded text-slate-900 dark:text-slate-100 transition-colors duration-200">protocol_emergencia_open_all</code> para ejecutar el protocolo. Este permiso está restringido por seguridad.
                            </li>
                            <li>
                                <strong>Accede al módulo de Protocolo:</strong> Haz clic en <strong>"Protocolo"</strong> en el menú lateral (ícono 🚨). Este módulo requiere el permiso <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded text-slate-900 dark:text-slate-100 transition-colors duration-200">view_protocolo</code>.
                            </li>
                            <li>
                                <strong>Revisa las puertas activas:</strong> El sistema mostrará todas las puertas activas con sus IPs configuradas, estado de conexión y última comunicación. Verifica que las puertas críticas estén en línea antes de activar.
                            </li>
                            <li>
                                <strong>Activar protocolo:</strong> Haz clic en el botón <strong>"Activar Protocolo de Emergencia"</strong>. El sistema te pedirá confirmación antes de ejecutar.
                            </li>
                            <li>
                                <strong>Confirmar y monitorear:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>El sistema abrirá todas las puertas en paralelo mediante comunicación simultánea con cada dispositivo</li>
                                    <li>Las puertas se mantendrán abiertas durante 15 minutos por defecto (tiempo configurable)</li>
                                    <li>Verás en tiempo real cuántas puertas se abrieron exitosamente y cuáles fallaron</li>
                                    <li>Puedes desactivar el protocolo manualmente antes de que expire el tiempo</li>
                                </ul>
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-yellow-800 dark:text-yellow-300">
                                <strong>⚠️ ADVERTENCIA CRÍTICA:</strong> Esta acción se registra permanentemente en el historial con tu usuario y timestamp. Las puertas se mantendrán abiertas incluso si se corta la conexión de red (el comando se ejecuta localmente en cada dispositivo). Solo usar en casos de emergencia real (incendio, evacuación, emergencia médica, etc.). El uso indebido puede comprometer la seguridad del edificio.
                            </p>
                        </div>
                        <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>📋 Historial y auditoría:</strong> Puedes ver las últimas ejecuciones del protocolo en la tabla inferior del módulo, incluyendo quién lo ejecutó, fecha y hora exacta, cuántas puertas se abrieron exitosamente, cuáles fallaron (y por qué), y el tiempo de duración. Esta información es importante para auditorías de seguridad.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pregunta 9: Cambio de Contraseña -->
                <div
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
                >
                    <button
                        @click="toggleFAQ('faq9')"
                        class="w-full text-left p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span>🔑</span>
                            <span>¿Cómo cambiar mi contraseña?</span>
                        </h2>
                        <svg
                            class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isOpen('faq9') }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        v-show="isOpen('faq9')"
                        class="px-6 pb-6 space-y-3 text-sm text-slate-700 dark:text-slate-300 transition-all"
                    >
                        <p>
                            Si es la primera vez que ingresas al sistema o necesitas cambiar tu contraseña:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Primera vez:</strong> Si es tu primer acceso, el sistema te redirigirá automáticamente a la pantalla de cambio de contraseña después de iniciar sesión.
                            </li>
                            <li>
                                <strong>Cambio posterior:</strong> Puedes cambiar tu contraseña desde tu perfil:
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Haz clic en tu nombre en la esquina superior derecha</li>
                                    <li>Selecciona "Perfil"</li>
                                    <li>En la sección de contraseña, ingresa tu contraseña actual y la nueva contraseña</li>
                                    <li>Confirma la nueva contraseña y guarda los cambios</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Requisitos de contraseña:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Mínimo 8 caracteres</li>
                                    <li>Se recomienda usar una combinación de letras, números y caracteres especiales</li>
                                </ul>
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>Nota para visitantes:</strong> Después de cambiar tu contraseña por primera vez, serás redirigido automáticamente a la sección de Ingreso. Si ya tienes un QR activo, podrás verlo y descargarlo. Si no tienes QR, deberás solicitar a un administrador que te genere uno.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pregunta 10: Recuperar Contraseña -->
                <div
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
                >
                    <button
                        @click="toggleFAQ('faq10')"
                        class="w-full text-left p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span>🔓</span>
                            <span>¿Qué hacer si olvidé mi contraseña?</span>
                        </h2>
                        <svg
                            class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isOpen('faq10') }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        v-show="isOpen('faq10')"
                        class="px-6 pb-6 space-y-3 text-sm text-slate-700 dark:text-slate-300 transition-all"
                    >
                        <p>
                            Si olvidaste tu contraseña, puedes recuperarla siguiendo estos pasos:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede a la pantalla de login:</strong> En la página de inicio de sesión, haz clic en "¿Olvidaste tu contraseña?" o "Recuperar contraseña".
                            </li>
                            <li>
                                <strong>Ingresa tu correo:</strong> Proporciona el correo electrónico asociado a tu cuenta.
                            </li>
                            <li>
                                <strong>Revisa tu correo:</strong> Recibirás un enlace de recuperación en tu bandeja de entrada (revisa también la carpeta de spam).
                            </li>
                            <li>
                                <strong>Restablece tu contraseña:</strong> Haz clic en el enlace del correo y sigue las instrucciones para crear una nueva contraseña.
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-yellow-800 dark:text-yellow-300">
                                <strong>⚠️ Importante:</strong> El enlace de recuperación tiene un tiempo limitado de validez. Si no recibes el correo, verifica que tu dirección de correo esté correcta en el sistema o contacta al administrador.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pregunta 11: Tarjetas NFC -->
                <div
                    v-if="!esVisitante && hasPermission('view_tarjetas_nfc')"
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
                >
                    <button
                        @click="toggleFAQ('faq11')"
                        class="w-full text-left p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span>💳</span>
                            <span>¿Cómo gestionar tarjetas NFC?</span>
                        </h2>
                        <svg
                            class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isOpen('faq11') }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        v-show="isOpen('faq11')"
                        class="px-6 pb-6 space-y-3 text-sm text-slate-700 dark:text-slate-300 transition-all"
                    >
                        <p>
                            Las tarjetas NFC son una alternativa física a los códigos QR para acceder a las puertas:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede al módulo de Tarjetas NFC:</strong> Haz clic en "Tarjetas NFC" en el menú lateral (requiere permiso <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded text-slate-900 dark:text-slate-100 transition-colors duration-200">view_tarjetas_nfc</code>).
                            </li>
                            <li>
                                <strong>Registrar una nueva tarjeta:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Haz clic en "Nueva Tarjeta"</li>
                                    <li>Ingresa el código de la tarjeta NFC (se obtiene al acercar la tarjeta al lector)</li>
                                    <li>Asigna un nombre descriptivo para identificar la tarjeta</li>
                                    <li>Marca como activa si está en uso</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Asignar tarjeta a un usuario:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Desde el módulo de Ingreso, selecciona el usuario</li>
                                    <li>Haz clic en "Asignar Tarjeta NFC"</li>
                                    <li>Selecciona la tarjeta disponible y confirma</li>
                                    <li>Configura las puertas y horarios de acceso (similar a los QR)</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Desasignar tarjeta:</strong> Puedes desasignar una tarjeta desde el módulo de Ingreso o desde Tarjetas NFC cuando ya no se necesite.
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>Ventajas de las tarjetas NFC:</strong> Son más rápidas y convenientes que los QR, especialmente para acceso frecuente. Funcionan igual que los QR en términos de permisos y horarios configurados.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pregunta 12: Problemas de Acceso -->
                <div
                    v-if="esVisitante || hasPermission('view_ingreso')"
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
                >
                    <button
                        @click="toggleFAQ('faq12')"
                        class="w-full text-left p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span>❌</span>
                            <span>¿Por qué mi QR o tarjeta NFC no funciona?</span>
                        </h2>
                        <svg
                            class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isOpen('faq12') }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        v-show="isOpen('faq12')"
                        class="px-6 pb-6 space-y-3 text-sm text-slate-700 dark:text-slate-300 transition-all"
                    >
                        <p>
                            Si tu QR o tarjeta NFC no está funcionando, verifica estos puntos:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>QR/Tarjeta activa:</strong> Verifica que tu QR o tarjeta esté marcada como activa en el sistema.
                            </li>
                            <li>
                                <strong>Fecha de expiración:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Para visitantes: verifica que el QR no haya expirado (por defecto duran 1 día laborable)</li>
                                    <li>Para funcionarios: verifica que tu cuenta de usuario no haya expirado</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Permisos de puerta:</strong> Asegúrate de que tu QR o tarjeta tenga permisos configurados para la puerta que intentas abrir.
                            </li>
                            <li>
                                <strong>Horarios:</strong> Verifica que estés intentando acceder dentro del horario configurado para tu QR o tarjeta.
                            </li>
                            <li>
                                <strong>Usuario activo:</strong> Tu cuenta de usuario debe estar marcada como activa en el sistema.
                            </li>
                            <li>
                                <strong>Calidad del QR:</strong> Si usas QR, asegúrate de que la imagen esté nítida y completa en tu dispositivo. Intenta aumentar el brillo de la pantalla.
                            </li>
                            <li>
                                <strong>Distancia del lector:</strong> Para tarjetas NFC, acércate lo suficiente al lector (generalmente 2-5 cm).
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-yellow-800 dark:text-yellow-300">
                                <strong>💡 Solución rápida:</strong> Si eres visitante y tu QR expiró, debes solicitar a un administrador o personal autorizado que te genere uno nuevo, ya que los visitantes no pueden generar QR por sí mismos. Si eres funcionario y tu QR no funciona, verifica que tu cuenta esté activa y que tengas permisos en tu cargo para acceder a esa puerta. Si el problema persiste, contacta al administrador del sistema.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pregunta 13: Mantenimientos de UPS -->
                <div
                    v-if="!esVisitante && hasAnyPermission(['view_ups', 'create_mantenimientos'])"
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
                >
                    <button
                        @click="toggleFAQ('faq13')"
                        class="w-full text-left p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span>🔋</span>
                            <span>¿Cómo registrar mantenimientos de UPS?</span>
                        </h2>
                        <svg
                            class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isOpen('faq13') }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        v-show="isOpen('faq13')"
                        class="px-6 pb-6 space-y-3 text-sm text-slate-700 dark:text-slate-300 transition-all"
                    >
                        <p>
                            Para registrar un mantenimiento de un equipo UPS:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede al módulo de UPS:</strong> Haz clic en "UPS" en el menú lateral.
                            </li>
                            <li>
                                <strong>Selecciona el UPS:</strong> Haz clic en "Ver" en el UPS al que se le realizó el mantenimiento.
                            </li>
                            <li>
                                <strong>Crear nuevo mantenimiento:</strong> En la sección de mantenimientos, haz clic en "Nuevo Mantenimiento".
                            </li>
                            <li>
                                <strong>Completa el formulario:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Selecciona la fecha del mantenimiento</li>
                                    <li>Elige el tipo: Realizado o Programado</li>
                                    <li>Si es programado, indica la fecha de fin</li>
                                    <li>Completa los indicadores técnicos (voltaje, temperatura, etc.)</li>
                                    <li>Agrega observaciones sobre el mantenimiento</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Sube evidencia:</strong> Puedes subir imágenes de la bitácora física o documentos relacionados.
                            </li>
                            <li>
                                <strong>Guardar:</strong> Haz clic en "Guardar" para completar el registro.
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>Nota:</strong> Los mantenimientos de UPS también pueden incluir análisis de imágenes de bitácoras físicas usando la función de análisis de imágenes en la sección de Bitácora.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pregunta 14: Uso del QR como Visitante -->
                <div
                    v-if="esVisitante"
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
                >
                    <button
                        @click="toggleFAQ('faq14')"
                        class="w-full text-left p-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span>👋</span>
                            <span>¿Cómo usar mi QR como visitante?</span>
                        </h2>
                        <svg
                            class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform duration-200"
                            :class="{ 'rotate-180': isOpen('faq14') }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        v-show="isOpen('faq14')"
                        class="px-6 pb-6 space-y-3 text-sm text-slate-700 dark:text-slate-300 transition-all"
                    >
                        <p>
                            Como visitante, tu acceso al sistema está limitado a ver y usar tu código QR. <strong>No puedes generar QR por ti mismo</strong>:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Solicitar tu QR:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Debes solicitar a un usuario con permisos (administrador o personal autorizado) que genere tu código QR</li>
                                    <li>El administrador configurará las puertas, pisos y horarios de acceso según tu autorización</li>
                                    <li>Una vez generado, podrás ver y descargar tu QR desde la sección de Ingreso</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Ver y descargar tu QR:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Después de iniciar sesión, serás redirigido automáticamente a la sección de Ingreso</li>
                                    <li>Si ya tienes un QR activo, lo verás en pantalla</li>
                                    <li>Haz clic en "Descargar QR" para guardar la imagen en tu dispositivo móvil</li>
                                    <li>Cuando un administrador genera tu QR y tu cuenta tiene correo, recibirás una copia automática por correo electrónico</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Usar tu QR:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Guarda el QR descargado en tu dispositivo móvil (galería de fotos)</li>
                                    <li>Al llegar a una puerta, abre la imagen del QR en tu teléfono</li>
                                    <li>Acerca el QR al lector de la puerta</li>
                                    <li>El lector escaneará el código y abrirá la puerta si tienes permiso y estás dentro del horario configurado</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Validez del QR:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Por defecto, tu QR es válido por 1 día laborable desde su generación</li>
                                    <li>Una vez expirado, necesitarás solicitar a un administrador que genere uno nuevo</li>
                                    <li>Puedes verificar la fecha de expiración en la sección de Ingreso</li>
                                </ul>
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-yellow-800 dark:text-yellow-300">
                                <strong>⚠️ Importante:</strong> Como visitante, no puedes generar QR por ti mismo. Si necesitas un nuevo QR o tu QR ha expirado, debes contactar a un administrador o personal autorizado para que te lo genere.
                            </p>
                        </div>
                        <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>💡 Tip:</strong> Guarda el QR en tu galería de fotos para acceso rápido. Asegúrate de que la imagen esté nítida y completa para que el lector pueda escanearla correctamente. Mantén el brillo de la pantalla alto al acercar el QR al lector.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const page = usePage();

// Estado para controlar qué FAQs están abiertas
const openFAQs = ref({});

// Obtener permisos del usuario
const userPermissions = computed(() => page.props.auth?.user?.permissions || []);
const esVisitante = computed(() => page.props.auth?.user?.role?.name === "visitante");

// Función para verificar si el usuario tiene un permiso
const hasPermission = (permission) => {
    return userPermissions.value.includes(permission);
};

// Función para verificar si el usuario tiene alguno de los permisos
const hasAnyPermission = (permissions) => {
    return permissions.some(perm => userPermissions.value.includes(perm));
};

const toggleFAQ = (faqId) => {
    openFAQs.value[faqId] = !openFAQs.value[faqId];
};

const isOpen = (faqId) => {
    return openFAQs.value[faqId] || false;
};

// Visitante: abrir automáticamente la guía de QR
onMounted(() => {
    if (esVisitante.value) {
        openFAQs.value = { faq4: true };
    }
});
</script>

