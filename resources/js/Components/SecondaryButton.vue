<script setup>
import {computed, inject, ref, useAttrs} from "vue";

const props = defineProps({
    type: {
        type: String,
        default: 'button',
    },
    disabled: Boolean
});
const attrs = useAttrs();

// Проверяем, передал ли пользователь какой-либо класс скругления в родителе
const hasRoundedClass = computed(() => {
    const className = attrs.class || '';
    return className.includes('rounded');
})

const viewMode = inject('viewMode', ref(false));
const disabled = computed(() => {
    if (props.disabled === true) {
        return true;
    }
    if (viewMode.value === true) {
        return true;
    }
    return false;
})


</script>

<template>
    <button
        :type="type"
        :class="['disabled:cursor-not-allowed inline-flex items-center bg-secondary  text-xs font-semibold tracking-widest text-primary shadow-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50',
        {'rounded-md px-4 py-2': !hasRoundedClass},
        ]"
        :disabled="disabled"
    >
        <slot />
    </button>
</template>
