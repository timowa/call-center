<script setup>
import {computed, onMounted} from 'vue';

const model = defineModel();
const props = defineProps(['placeholder', 'readonly', 'options']);

const classes = computed(() => ({
    'bg-gray-200 cursor-not-allowed opacity-70': props.readonly
}));


defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <select
        class="px-2 py-1 text-sm rounded-md border-gray-300 bg-grey-150 shadow-sm focus:outline-none focus:shadow-none focus:ring-0 focus-visible:ring-0 focus:inset-shadow-none focus:inset-ring-0 focus:border-gray-300 focus:border-b-4"
        :class="classes"
        :placeholder="placeholder"
        :disabled="readonly"
    v-model="model">
        <option value="" selected="selected" disabled="disabled"></option>
        <option v-for="option in options"
                :key="option.id"
                :value="option.id"
                v-text="option.name"
        ></option>
        </select>
</template>

<style scoped>

</style>
