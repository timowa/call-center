<script setup>
import { computed, inject, ref } from 'vue';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const model = defineModel({
    type: [String, Date, Object, null],
    required: true,
});

const viewMode = inject('viewMode', ref(false));

const props = defineProps(['placeholder', 'readonly']);

const isDisabled = computed(() => {
    if (props.readonly === true) return true;
    if (viewMode.value === true) return true;

    return false;
});
</script>

<template>
    <div class="datetime-picker-wrapper">
        <input class="px-2 py-1 text-sm rounded-md border-gray-300 bg-grey-150 shadow-sm focus:outline-none focus:shadow-none focus:ring-0 focus-visible:ring-0 focus:inset-shadow-none focus:inset-ring-0 focus:border-gray-300 focus:border-b-4 disabled:bg-grey-220 disabled:cursor-not-allowed"
               v-model="model"
               :placeholder="placeholder"
               :disabled="isDisabled"
               type="datetime-local"
               ref="input">
    </div>
</template>
