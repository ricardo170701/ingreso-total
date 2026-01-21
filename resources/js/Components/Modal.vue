<template>
    <Teleport to="body">
        <div
            v-if="show"
            @click="handleBackdropClick"
            class="fixed inset-0 bg-black/60 dark:bg-black/70 flex items-center justify-center z-[10000] p-4 transition-colors duration-200"
        >
            <div
                :class="[
                    'bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-md w-full border border-slate-200 dark:border-slate-700 transition-colors duration-200',
                    size === 'lg' ? 'max-w-2xl' : size === 'sm' ? 'max-w-sm' : 'max-w-md'
                ]"
                @click.stop
            >
                <!-- Header -->
                <div
                    v-if="title || closable"
                    class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700"
                >
                    <h3 v-if="title" class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        {{ title }}
                    </h3>
                    <div v-else></div>
                    <button
                        v-if="closable"
                        type="button"
                        @click="handleClose"
                        class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200 flex items-center justify-center"
                        aria-label="Cerrar"
                    >
                        ×
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <slot>
                        <p v-if="message" class="text-sm text-slate-700 dark:text-slate-300">
                            {{ message }}
                        </p>
                    </slot>
                </div>

                <!-- Footer -->
                <div
                    v-if="showFooter"
                    class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-200 dark:border-slate-700"
                >
                    <slot name="footer">
                        <button
                            v-if="showCancel"
                            type="button"
                            @click="handleCancel"
                            :class="[
                                'px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200',
                                cancelClass
                            ]"
                        >
                            {{ cancelText }}
                        </button>
                        <button
                            v-if="showConfirm"
                            type="button"
                            @click="handleConfirm"
                            :class="[
                                'px-4 py-2 rounded-lg text-white font-medium transition-colors duration-200',
                                confirmClass
                            ]"
                        >
                            {{ confirmText }}
                        </button>
                    </slot>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
    message: {
        type: String,
        default: '',
    },
    closable: {
        type: Boolean,
        default: true,
    },
    closeOnBackdrop: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    showFooter: {
        type: Boolean,
        default: true,
    },
    showCancel: {
        type: Boolean,
        default: true,
    },
    showConfirm: {
        type: Boolean,
        default: true,
    },
    cancelText: {
        type: String,
        default: 'Cancelar',
    },
    confirmText: {
        type: String,
        default: 'Confirmar',
    },
    cancelClass: {
        type: String,
        default: '',
    },
    confirmClass: {
        type: String,
        default: 'bg-green-600 dark:bg-green-700 hover:bg-green-700 dark:hover:bg-green-600',
    },
});

const emit = defineEmits(['update:show', 'close', 'confirm', 'cancel']);

const handleClose = () => {
    emit('update:show', false);
    emit('close');
};

const handleBackdropClick = () => {
    if (props.closeOnBackdrop) {
        handleClose();
    }
};

const handleConfirm = () => {
    emit('confirm');
};

const handleCancel = () => {
    emit('cancel');
    handleClose();
};
</script>
