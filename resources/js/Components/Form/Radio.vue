<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        required: true,
    },
    value: {
        default: null,
    },
    disabled: Boolean
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});
const bgColor = props.disabled ? 'bg-grey-200' : 'bg-white'
</script>

<template>
    <input
        type="radio"
        :value="value"
        :disabled="disabled"
        v-model="proxyChecked"
        class="rounded-full border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mr-2"
        :class="bgColor"
    />
</template>
