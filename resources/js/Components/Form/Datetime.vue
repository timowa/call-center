<script setup>
import { computed, inject, ref } from 'vue';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const model = defineModel({
    type: [String, Date, Object, null],
    required: true,
});

const isUkioForm = inject('isUkioForm', ref(false));
const { isCreator } = inject('directories', { isCreator: false });
const viewMode = inject('viewMode', ref(false));

const props = defineProps(['placeholder', 'readonly', 'allowEditIfNotCreator']);

const isDisabled = computed(() => {
    if (props.readonly === true) return true;
    if (viewMode.value === true) return true;
    if (isUkioForm.value && !isCreator) {
        return !props.allowEditIfNotCreator;
    }
    return false;
});
</script>

<template>
    <div class="datetime-picker-wrapper">
        <VueDatePicker
            v-model="model"
            :disabled="isDisabled"
            :placeholder="placeholder"
        cancelText="Отмена"
        selectText="Применить"
        format="dd.MM.yyyy HH:mm"
        model-type="yyyy-MM-dd HH:mm:ss"
        auto-apply
        :teleport="true"
        :is-24="true"
        input-class-name="custom-datepicker-input"
        />
    </div>
</template>
