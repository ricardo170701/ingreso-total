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
                            Para registrar un nuevo usuario en el sistema, sigue estos pasos:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede al módulo de Usuarios:</strong> Haz clic en el botón "Usuarios" en el menú lateral.
                            </li>
                            <li>
                                <strong>Crear nuevo usuario:</strong> Haz clic en el botón "Nuevo Usuario" o "Crear Usuario".
                            </li>
                            <li>
                                <strong>Completa el formulario:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Ingresa el <strong>correo electrónico</strong> del usuario</li>
                                    <li>Establece una <strong>contraseña</strong> temporal</li>
                                    <li>Completa los datos personales: nombre, apellido, departamento</li>
                                    <li>Selecciona el <strong>tipo de vinculación</strong> del usuario (visitante, servidor_publico, proveedor)</li>
                                    <li>Asigna un <strong>cargo</strong> para definir sus permisos físicos (acceso a puertas)</li>
                                    <li>Configura la <strong>fecha de expiración</strong> si es necesario</li>
                                    <li>Marca como <strong>activo</strong> para habilitar el acceso</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Guardar:</strong> Haz clic en "Guardar" o "Crear Usuario" para completar el registro.
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>Nota:</strong> El usuario recibirá un correo con sus credenciales (si está configurado) o deberás proporcionarle manualmente el correo y contraseña para que pueda iniciar sesión.
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
                            Los permisos se configuran a través de los <strong>Cargos</strong>. Cada cargo define qué puede hacer un usuario en el sistema:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede al módulo de Permisos:</strong> Haz clic en "Permisos" en el menú lateral.
                            </li>
                            <li>
                                <strong>Selecciona o crea un cargo:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Si el cargo ya existe, haz clic en "Gestionar Permisos"</li>
                                    <li>Si necesitas crear uno nuevo, haz clic en "Nuevo Cargo"</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Configura permisos del sistema:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>En la sección <strong>"Permisos de la Sidebar"</strong>, marca los permisos que controlan qué botones del menú puede ver el usuario</li>
                                    <li>En las demás secciones, marca los permisos de crear, editar o eliminar según corresponda</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Configura permisos físicos (puertas):</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>En la sección "Agregar Permiso de Puerta", selecciona las puertas a las que tendrá acceso</li>
                                    <li>Configura horarios, días de la semana y fechas de validez si es necesario</li>
                                    <li>Marca como activo para habilitar el acceso</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Asigna el cargo al usuario:</strong> Al crear o editar un usuario, selecciona el cargo que acabas de configurar.
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>Tip:</strong> Los permisos del sistema controlan qué secciones del menú puede ver el usuario. Los permisos físicos (puertas) controlan a qué puertas físicas puede acceder con su QR.
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
                            Para registrar un mantenimiento de una puerta, sigue estos pasos:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede a una puerta:</strong> Ve a <strong>Puertas</strong> y haz clic en <strong>"Ver Puerta"</strong>.
                            </li>
                            <li>
                                <strong>Crear nuevo mantenimiento:</strong> En la hoja de vida, usa <strong>"Nuevo mantenimiento"</strong>.
                            </li>
                            <li>
                                <strong>Completa el formulario:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Selecciona la <strong>puerta</strong> a la que se le realizó el mantenimiento</li>
                                    <li>Selecciona la <strong>fecha</strong> del mantenimiento</li>
                                    <li>Elige el <strong>tipo</strong> de mantenimiento:
                                        <ul class="list-circle list-inside ml-4 mt-1">
                                            <li><strong>Realizado:</strong> Si el mantenimiento ya se completó</li>
                                            <li><strong>Programado:</strong> Si el mantenimiento está programado para el futuro (la puerta mostrará un indicador amarillo/rojo)</li>
                                        </ul>
                                    </li>
                                    <li>Si es programado, indica la <strong>fecha de fin</strong> del mantenimiento</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Evalúa los defectos:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Para cada defecto (Equipo estado interno, Accesorios norma, Mantenimiento, etc.), selecciona el nivel de gravedad:
                                        <ul class="list-circle list-inside ml-4 mt-1">
                                            <li><strong>Sin defecto</strong> (verde)</li>
                                            <li><strong>Defecto ligero</strong> (amarillo)</li>
                                            <li><strong>Defecto grave</strong> (naranja)</li>
                                            <li><strong>Defecto muy grave</strong> (rojo)</li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <strong>Agrega información adicional:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>En "Otros Defectos" puedes agregar defectos no listados</li>
                                    <li>En "Observaciones" agrega notas adicionales sobre el mantenimiento</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Sube imágenes de evidencia:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Haz clic en "Seleccionar Imágenes"</li>
                                    <li>Puedes subir hasta 10 imágenes (máximo 2MB cada una)</li>
                                    <li>Las imágenes deben ser en formato JPG, PNG o GIF</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Guardar:</strong> Haz clic en "Guardar" o "Crear Mantenimiento" para completar el registro.
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>Nota:</strong> Si registras un mantenimiento programado, la puerta mostrará un indicador amarillo mientras esté en mantenimiento, y rojo si pasa la fecha programada sin realizarse.
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
                            Para generar un código QR que permita a un usuario acceder a las puertas, sigue estos pasos:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede al módulo de Ingreso:</strong> Haz clic en "Ingreso" en el menú lateral.
                            </li>
                            <li>
                                <strong>Selecciona el usuario:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Si tienes permiso para generar QR para otros usuarios, podrás seleccionar cualquier usuario</li>
                                    <li>Si no tienes ese permiso, solo podrás generar QR para ti mismo (el selector estará deshabilitado)</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Selecciona las puertas:</strong> Marca las puertas a las que el usuario tendrá acceso con este QR.
                            </li>
                            <li>
                                <strong>Configura horarios (opcional):</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Hora de inicio y hora de fin del acceso</li>
                                    <li>Días de la semana en que el acceso es válido</li>
                                    <li>Fecha de inicio y fecha de fin de validez del QR</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Generar QR:</strong> Haz clic en "Generar QR" y se mostrará el código QR.
                            </li>
                            <li>
                                <strong>Descargar o enviar:</strong>
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li>Haz clic en "Descargar QR" para guardar la imagen</li>
                                    <li>Haz clic en "Enviar por Correo" para enviarlo directamente al usuario</li>
                                </ul>
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>Nota:</strong> Para funcionarios, el QR permanece activo hasta la fecha de expiración del usuario. Para visitantes, el QR es válido por defecto durante 1 día laborable, aunque puede configurarse para un período mayor. Una vez vencido, el usuario necesitará generar uno nuevo. El QR solo funciona si el usuario tiene permisos en su cargo para acceder a las puertas seleccionadas.
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
                            Para ver los detalles de un mantenimiento y descargar su PDF:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede a una puerta:</strong> Ve a <strong>Puertas</strong> y haz clic en <strong>"Ver Puerta"</strong>.
                            </li>
                            <li>
                                <strong>Busca el mantenimiento:</strong> En la sección <strong>Mantenimientos</strong> de la hoja de vida, localiza el registro.
                            </li>
                            <li>
                                <strong>Ver detalles:</strong> Haz clic en el mantenimiento o en el botón "Ver" para abrir la vista detallada.
                            </li>
                            <li>
                                <strong>Descargar PDF:</strong> En la vista de detalle, haz clic en el botón "📄 Descargar PDF".
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>El PDF incluye:</strong> Información del equipo, datos del cliente, evaluación de defectos con niveles de gravedad, observaciones, evidencia fotográfica (referencia), resultado de la inspección y espacios para firmas.
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
                            Para exportar datos del sistema en formato CSV compatible con Excel:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede al módulo de Reportes:</strong> Haz clic en "Reportes" en el menú lateral (requiere permiso <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded text-slate-900 dark:text-slate-100 transition-colors duration-200">view_reportes</code>).
                            </li>
                            <li>
                                <strong>Selecciona el tipo de reporte:</strong> Hay 4 tipos disponibles:
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li><strong>Usuarios:</strong> Lista de usuarios con sus roles, cargos y departamentos</li>
                                    <li><strong>Accesos:</strong> Historial de accesos a puertas</li>
                                    <li><strong>Mantenimientos:</strong> Registros de mantenimientos realizados</li>
                                    <li><strong>Puertas:</strong> Información de las puertas del sistema</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Aplica filtros (opcional):</strong> Usa los campos de filtro para restringir los datos que deseas exportar.
                            </li>
                            <li>
                                <strong>Exportar:</strong> Haz clic en el botón "📥 Exportar" correspondiente al tipo de reporte.
                            </li>
                            <li>
                                <strong>Abrir en Excel:</strong> El archivo CSV se descargará automáticamente. Ábrelo con Excel para ver los datos formateados.
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>Compatibilidad:</strong> Los archivos CSV están codificados en UTF-8 con BOM, por lo que los caracteres especiales (á, é, í, ó, ú, ñ) se verán correctamente en Excel. Si no se ven bien, abre Excel y selecciona "Datos" → "Obtener datos" → "Desde texto/CSV" y elige "65001: Unicode (UTF-8)" como origen de archivo.
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
                            Los departamentos organizan a los usuarios por área dentro de la organización:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede al módulo de Departamentos:</strong> Haz clic en "Departamentos" en el menú lateral.
                            </li>
                            <li>
                                <strong>Crear nuevo departamento:</strong> Haz clic en "Nuevo Departamento" y completa:
                                <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                    <li><strong>Nombre:</strong> Nombre del departamento</li>
                                    <li><strong>Piso:</strong> Piso donde se encuentra (opcional)</li>
                                    <li><strong>Descripción:</strong> Información adicional sobre el departamento</li>
                                    <li><strong>Activo:</strong> Marca si el departamento está activo</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Asignar a usuarios:</strong> Al crear o editar un usuario, selecciona el departamento correspondiente en el campo "Departamento".
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>Tip:</strong> Los departamentos son útiles para organizar reportes y filtros. Un usuario puede pertenecer a un solo departamento.
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
                            El Protocolo de Emergencia permite abrir todas las puertas del sistema simultáneamente en caso de emergencia:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Requisito:</strong> Necesitas el permiso <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded text-slate-900 dark:text-slate-100 transition-colors duration-200">protocol_emergencia_open_all</code> para ejecutar el protocolo.
                            </li>
                            <li>
                                <strong>Accede al módulo de Protocolo:</strong> Haz clic en "Protocolo" en el menú lateral (ícono 🚨).
                            </li>
                            <li>
                                <strong>Revisa las puertas activas:</strong> El sistema mostrará todas las puertas activas con sus IPs configuradas.
                            </li>
                            <li>
                                <strong>Activar protocolo:</strong> Haz clic en el botón "Activar Protocolo de Emergencia".
                            </li>
                            <li>
                                <strong>Confirmar:</strong> El sistema abrirá todas las puertas en paralelo y las mantendrá abiertas durante 15 minutos (configurable).
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-yellow-800 dark:text-yellow-300">
                                <strong>⚠️ IMPORTANTE:</strong> Esta acción se registra en el historial. Las puertas se mantendrán abiertas incluso si se corta la conexión de red. Solo usar en casos de emergencia real.
                            </p>
                        </div>
                        <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                <strong>Historial:</strong> Puedes ver las últimas ejecuciones del protocolo en la tabla inferior, incluyendo quién lo ejecutó, cuántas puertas se abrieron exitosamente y cuáles fallaron.
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

