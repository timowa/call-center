<script setup>
import {ref, computed, inject} from 'vue';
import FormField from './FormField.vue';
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AdditionalActionButton from "@/Components/AdditionalActionButton.vue";

const viewMode = inject('viewMode');
const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    columns: {
        type: Array,
    },
    label: String,
    allowEditIfNotCreator: Boolean,
    onCustomSave: Function,
    withActionButtons: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['update:modelValue', 'custom-save']);
const hasNotPermissionToEdit = inject('hasNotPermissionToEdit', false)

const isModalOpen = ref(false);
const editingIndex = ref(null);
const formEntries = ref({});

const openAdd = () => {
    editingIndex.value = null;
    formEntries.value = {}; // Инициализируем как ПУСТОЙ ОБЪЕКТ
    isModalOpen.value = true;
};

const openEdit = (index) => {
    editingIndex.value = index;
    // Копируем существующий объект из массива данных
    formEntries.value = { ...props.modelValue[index] };
    isModalOpen.value = true;
};

const save = () => {
    if (props.onCustomSave) {
        emit('custom-save', formEntries.value);
        isModalOpen.value = false;
        return;
    }

    const currentData = [...props.modelValue];

    // Упрощаем логику сохранения для объекта
    if (editingIndex.value !== null) {
        // Редактирование
        currentData[editingIndex.value] = { ...formEntries.value };
    } else {
        // Создание: проверяем, что объект не совсем пустой перед пушем
        if (Object.keys(formEntries.value).length > 0) {
            currentData.push({ ...formEntries.value });
        }
    }

    emit('update:modelValue', currentData);
    isModalOpen.value = false;
};

const addRow = () => {
    formEntries.value.push({});
}

const remove = (index) => {
    const currentData = props.modelValue.filter((_, i) => i !== index);
    emit('update:modelValue', currentData);
};
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
</script>

<template>
    <div class="w-full bg-white shadow-sm overflow-hidden">
        <div class="flex items-center px-4 py-2 gap-6 border-b">
            <span class="font-bold text-gray-700">{{ label }}</span>
            <SecondaryButton
                class="rounded-full p-2"
                :disabled="disabled"
                @click="openAdd"
            ><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg></SecondaryButton>

        </div>
        <div class=" border rounded-lg">
            <table class="w-full text-left text-13">
                <thead class=" border-b text-gray-500">
                <tr v-if="columns">
                    <th v-for="col in columns" :key="col.key" class="border-r">
                        {{ col.label }}
                    </th>
                    <th v-if="withActionButtons" class="w-20"></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <tr v-for="(row, index) in modelValue" :key="index" class="group hover:bg-gray-50">

                    <slot name="rows" :row="row" :index="index"></slot>

                    <td v-if="withActionButtons" class="text-right space-x-2 whitespace-nowrap">
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                            <button type="button" @click="openEdit(index)" class="text-blue-600 hover:text-blue-800 p-1">✎</button>
                            <button type="button" @click="remove(index)" class="text-red-600 hover:text-red-800 p-1">×</button>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6">
                <h3 class="text-lg font-bold mb-4">{{ editingIndex !== null ? 'Редактировать' : 'Добавить' }}</h3>

                <slot name="modal-fields" :form="formEntries"></slot>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="isModalOpen = false">Отмена</SecondaryButton>
                    <PrimaryButton @click="save">Сохранить</PrimaryButton>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
:deep(td) {
    @apply px-4 py-2 text-sm border-r border-b;
}

thead th {
    @apply px-4 py-2 text-sm;
}
</style>
