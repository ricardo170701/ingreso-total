<template>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">{{
            label
        }}</label>
        <slot />
        <div v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</div>
    </div>
</template>

<script setup>
defineProps({
    label: String,
    error: String,
});
</script>
