<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">
                    Centro de Soporte
                </h1>
                <p class="text-sm text-slate-600">
                    Encuentra respuestas a las preguntas más frecuentes sobre el uso del sistema.
                </p>
            </div>

            <!-- Preguntas Frecuentes -->
            <div class="space-y-4">
                <!-- Pregunta 1: Registrar Usuarios -->
                <div class="bg-white border border-slate-200 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <span>👤</span>
                        <span>¿Cómo registrar usuarios (enrollar)?</span>
                    </h2>
                    <div class="space-y-3 text-sm text-slate-700">
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
                                    <li>Selecciona el <strong>rol</strong> del usuario (super_usuario, operador, rrhh, funcionario, visitante)</li>
                                    <li>Asigna un <strong>cargo</strong> para definir sus permisos físicos (acceso a puertas)</li>
                                    <li>Configura la <strong>fecha de expiración</strong> si es necesario</li>
                                    <li>Marca como <strong>activo</strong> para habilitar el acceso</li>
                                </ul>
                            </li>
                            <li>
                                <strong>Guardar:</strong> Haz clic en "Guardar" o "Crear Usuario" para completar el registro.
                            </li>
                        </ol>
                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-xs text-blue-800">
                                <strong>Nota:</strong> El usuario recibirá un correo con sus credenciales (si está configurado) o deberás proporcionarle manualmente el correo y contraseña para que pueda iniciar sesión.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pregunta 2: Configurar Permisos -->
                <div class="bg-white border border-slate-200 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <span>🔐</span>
                        <span>¿Cómo configurar permisos?</span>
                    </h2>
                    <div class="space-y-3 text-sm text-slate-700">
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
                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-xs text-blue-800">
                                <strong>Tip:</strong> Los permisos del sistema controlan qué secciones del menú puede ver el usuario. Los permisos físicos (puertas) controlan a qué puertas físicas puede acceder con su QR.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pregunta 3: Subir Mantenimiento -->
                <div class="bg-white border border-slate-200 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <span>🔧</span>
                        <span>¿Cómo subir un mantenimiento?</span>
                    </h2>
                    <div class="space-y-3 text-sm text-slate-700">
                        <p>
                            Para registrar un mantenimiento de una puerta, sigue estos pasos:
                        </p>
                        <ol class="list-decimal list-inside space-y-2 ml-2">
                            <li>
                                <strong>Accede al módulo de Mantenimientos:</strong> Haz clic en "Mantenimientos" en el menú lateral.
                            </li>
                            <li>
                                <strong>Crear nuevo mantenimiento:</strong> Haz clic en "Nuevo Mantenimiento" o "Crear Mantenimiento".
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
                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-xs text-blue-800">
                                <strong>Nota:</strong> Si registras un mantenimiento programado, la puerta mostrará un indicador amarillo mientras esté en mantenimiento, y rojo si pasa la fecha programada sin realizarse.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
</script>

