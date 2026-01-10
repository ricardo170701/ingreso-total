<template>
    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1 transition-colors duration-200">{{
            label
        }}</label>
        <slot />
        <div v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400 transition-colors duration-200">{{ error }}</div>
    </div>
</template>

<script setup>
defineProps({
    label: String,
    error: String,
});
</script>
