<script setup>
import {computed, inject, onMounted} from 'vue';

const model = defineModel();
const props = defineProps({
    'placeholder': String,
    'readonly': Boolean,
    'options': Array
});

const classes = computed(() => ({
    'bg-gray-200 cursor-not-allowed opacity-70': props.readonly
}));

const viewMode = inject('viewMode');
const disabled = computed(() => {
    if (props.disabled === true) {
        return true;
    }
    if (viewMode.value === true) {
        return true;
    }
    if (props.options?.length === 1) {
        return true;
    }
    return false;
})
defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <select
        class="px-2 py-1 text-sm rounded-md border-gray-300 bg-grey-150 shadow-sm focus:outline-none focus:shadow-none focus:ring-0 focus-visible:ring-0 focus:inset-shadow-none focus:inset-ring-0 focus:border-gray-300 focus:border-b-4 disabled:bg-grey-220 disabled:cursor-not-allowed"
        :class="classes"
        :placeholder="placeholder"
        :disabled="disabled"
        v-model="model">
        <option value="" selected="selected" disabled="disabled" v-if="options?.length > 1"></option>
        <option v-for="option in options"
                :key="option.id"
                :value="option.id"
                v-text="option.name"
        ></option>
        </select>
</template>

<style scoped>

</style>
