<script setup>
import {computed, inject, ref} from "vue";
const props = defineProps(['disabled'])
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
    <button type="button" v-bind="$attrs" class="text-primary-light flex disabled:cursor-not-allowed" :disabled="disabled">
        <slot/>
    </button>
</template>

<style scoped>

</style>
