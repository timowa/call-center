<script setup>
defineProps({
    value: {
        type: String,
    },
    disabled: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <label class="block text-sm font-medium whitespace-nowrap" :class="disabled ? 'text-grey-350' : 'text-gray-700'">
        <span v-if="value" class="w-full">{{ value }}</span>
        <span v-else  class="w-full"><slot /></span>
    </label>
</template>
