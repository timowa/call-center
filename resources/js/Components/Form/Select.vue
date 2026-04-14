<script setup>
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import {computed, inject, ref, watch} from 'vue';

const model = defineModel();

const props = defineProps({
    options: { type: Array, default: () => [] },
    placeholder: {type: String, default: ''},
    readonly: Boolean,
    reduce: { type: Function, default: (option) => option.id  },
});
const viewMode = inject('viewMode', ref(false));

const isDisabled = computed(() => {
    if (props.readonly) return true;
    if (viewMode.value) return true;

    return false;
});

const emit = defineEmits(['search']);

const onSearch = (search, loading) => {
    emit('search', search, loading);
};
watch(() => props.options, (newOptions) => {
    if (newOptions && newOptions.length === 1 && (model.value === null || model.value === undefined)) {
        model.value = props.reduce(newOptions[0]);
    }
}, { immediate: true });
</script>

<template>
    <div class="v-select-wrapper">
        <v-select
            v-model="model"
            :options="options"
            :reduce="reduce"
            :label="'name'"
            :clearable="false"
            :placeholder="placeholder"
            :disabled="isDisabled"
            :filterable="!$attrs.onSearch" @search="onSearch"
            class="custom-v-select bg-grey-150 disabled:bg-grey-220 disabled:cursor-not-allowed"
        >

            <template #no-options="{ search, searching }">
                <span v-if="searching" class="text-xs text-gray-500">
                    Ничего не найдено по запросу "{{ search }}"
                </span>
                <span v-else class="text-xs text-gray-500">Начните вводить название...</span>
            </template>
        </v-select>
    </div>
</template>

<style>
/* 1. Общие стили контейнера и поиска */
.custom-v-select {
    @apply h-full !important;
    max-width: 100%;

}

.custom-v-select .vs__dropdown-toggle {
    @apply border-gray-300 h-full rounded-md !important;
    min-height: 34px;
    padding: 2px 0;
}

.custom-v-select .vs__search {
    @apply text-sm !important;
}

/* 2. Текст выбранного элемента (решаем проблему вылетания) */
.custom-v-select .vs__selected-options {
    @apply flex-nowrap overflow-hidden !important;
    max-width: 65%;
}

.custom-v-select .vs__selected {
    @apply text-sm !important;
    display: block !important;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
    width: 100% !important;
    margin: 0;
}

/* 3. Выпадающее меню */
.custom-v-select .vs__actions {
    position: absolute;
    right: 5px;
    top: 0px;
    bottom: 0px;
    width: 25px;
}
.custom-v-select .vs__dropdown-menu {
    @apply text-sm shadow-lg border-gray-300 !important;
}

/* 4. Стили для состояния DISABLED */
.custom-v-select.vs--disabled .vs__dropdown-toggle {
    @apply border-0 bg-grey-220 cursor-not-allowed !important;
}

.custom-v-select.vs--disabled .vs__selected {
    @apply opacity-100 cursor-not-allowed !important;
}

.custom-v-select.vs--disabled .vs__search,
.custom-v-select.vs--disabled .vs__open-indicator,
.custom-v-select.vs--disabled .vs__actions {
    @apply bg-grey-220 cursor-not-allowed !important;
}
.custom-v-select.vs--open {

}
</style>
