<script setup>
import {computed} from "vue";

const props = defineProps({
    label: {
        type: String,
    },
    disabled: {
        type: Boolean,
        default: false
    },
    textAlign: {
        type: String,
        default: 'left'
    }
});
const isTextAlignRight = props.textAlign === 'right';
const isDisabled = props.disabled;
const classes = computed(() => ({
    'text-right w-full': isTextAlignRight,
    'text-grey-350': isDisabled,
    'text-gray-700': !isDisabled,
}));
</script>

<template>
    <label class="block text-sm font-medium pr-3" :class="classes">
        <span v-if="label">{{ label }}</span>
        <span v-else ><slot /></span>
    </label>
</template>
