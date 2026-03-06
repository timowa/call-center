<script setup>
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { computed, inject, ref } from 'vue';

const model = defineModel();

const props = defineProps({
    options: { type: Array, default: () => [] },
    placeholder: String,
    readonly: Boolean,
    reduce: { type: Function, default: (option) => option.id  },
});
const isUkioForm = inject('isUkioForm', ref(false));
const { isCreator } = inject('directories', { isCreator: false });
const viewMode = inject('viewMode', ref(false));

const isDisabled = computed(() => {
    if (props.readonly) return true;
    if (viewMode.value) return true;
    if (isUkioForm.value && !isCreator) return true;
    return false;
});
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
            class="custom-v-select bg-grey-150 disabled:bg-grey-220 disabled:cursor-not-allowed"
        >
            <template #no-options>
                <span class="text-xs text-gray-500">Ничего не найдено</span>
            </template>
        </v-select>
    </div>
</template>

<style>
.custom-v-select.vs--disabled .vs__dropdown-toggle {
    @apply border-0 bg-grey-220 rounded-md !important;
    padding: 2px 0;
    min-height: 34px;
}

.custom-v-select .vs__selected {
    @apply text-sm !important;
}

.custom-v-select.vs--disabled .vs__search {
    @apply text-sm !important;
    margin: 0;
    @apply bg-grey-220 cursor-not-allowed !important;
}
.custom-v-select.vs--disabled .vs__open-indicator {
    @apply text-sm !important;
    margin: 0;
    @apply bg-grey-220 cursor-not-allowed !important;
}
.custom-v-select.vs--disabled .vs__selected {
    @apply text-sm !important;
    margin: 0;
    @apply bg-grey-220 cursor-not-allowed !important;
}
.custom-v-select.vs--disabled .vs__actions {
    @apply text-sm !important;
    margin: 0;
    @apply bg-grey-220 cursor-not-allowed !important;
}

.custom-v-select .vs__dropdown-menu {
    @apply text-sm shadow-lg border-gray-300 !important;
}

.custom-v-select.vs--open .vs__dropdown-toggle {
    @apply border-b-4 border-gray-300 !important;
}

.vs--disabled .vs__dropdown-toggle. {
    @apply bg-grey-220 cursor-not-allowed !important;
}
</style>
