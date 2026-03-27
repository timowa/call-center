<script setup>
import {computed, inject, ref} from "vue";
const props = defineProps({
    disabled: Boolean
});
const viewMode = inject('viewMode', ref(false));
const disabled = computed(() => {
    if (props.disabled === false) {
        return false;
    }
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
        class="disabled:bg-primary-dark disabled:cursor-not-allowed sinline-flex items-center rounded-md border border-transparent bg-primary px-4 py-2 text-xs font-semibold tracking-widest text-white transition duration-150 ease-in-out hover:bg-primary-dark focus:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-primary-dark"
            :disabled="disabled"
    >
        <slot />
    </button>
</template>
