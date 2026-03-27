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
        required: true
    },
    label: String,
    allowEditIfNotCreator: Boolean
});

const emit = defineEmits(['update:modelValue']);
const hasNotPermissionToEdit = inject('hasNotPermissionToEdit', false)

const isModalOpen = ref(false);
const editingIndex = ref(null);
const formEntries = ref({});

const openAdd = () => {
    editingIndex.value = null;
    formEntries.value = [{}];
    isModalOpen.value = true;
};

const openEdit = (index) => {
    editingIndex.value = index;
    formEntries.value = { ...props.modelValue[index] };
    isModalOpen.value = true;
};

const save = () => {
    const currentData = [...props.modelValue];

    const newEntries = formEntries.value.filter(entry =>
        Object.values(entry).some(val => val !== undefined && val !== '')
    );

    if (editingIndex.value !== null) {
        currentData[editingIndex.value] = formEntries.value[0];
    } else {
        currentData.push(...newEntries);
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
    if (hasNotPermissionToEdit.value === true) {
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

        <div class="overflow-x-auto border rounded-lg">
            <table class="w-full text-left text-13">
                <thead class=" border-b text-gray-500">
                <tr>
                    <th v-for="col in columns" :key="col.key" class="border-r px-4 py-2 text-sm">
                        {{ col.label }}
                    </th>
                    <th class="px-4 py-2 w-20"></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <tr v-for="(row, index) in modelValue" :key="index" class="hover:bg-gray-50 group">
                    <td v-for="col in columns" :key="col.key" class="px-4 py-3 text-gray-700">
                        {{ row[col.key] }}
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <button @click="openEdit(index)" class="text-gray-400 hover:text-blue-600">✎</button>
                        <button @click="remove(index)" class="text-gray-400 hover:text-red-600">×</button>
                    </td>
                </tr>
                <tr v-if="!modelValue.length">
                    <td :colspan="columns.length + 1" class="px-4 py-8 text-center text-gray-400 text-sm">
                        Нет данных
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div v-if="isModalOpen" @click.self="isModalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl p-6">
                <h2 class="text-lg font-bold mb-6 border-b pb-2">
                    {{ editingIndex !== null ? 'Редактирование' : 'Добавление' }}
                </h2>
                <div  v-for="(entry, index) in formEntries" :key="index" class="flex items-end justify-between">
                    <div class="grid grid-cols-6 gap-x-6 gap-y-4 w-90"
                        :class="{'mt-6': index > 0}"
                    >
                        <FormField
                            v-for="col in columns"
                            :key="col.key"
                            v-bind="col"
                            v-model="formEntries[index][col.key]"
                            :vertical="true"
                        />
                    </div>
                    <AdditionalActionButton @click="formEntries.splice(index, 1)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-grey-370" viewBox="0 0 32 32" >
                            <path d="M 15 4 C 14.476563 4 13.941406 4.183594 13.5625 4.5625 C 13.183594 4.941406 13 5.476563 13 6 L 13 7 L 7 7 L 7 9 L 8 9 L 8 25 C 8 26.644531 9.355469 28 11 28 L 23 28 C 24.644531 28 26 26.644531 26 25 L 26 9 L 27 9 L 27 7 L 21 7 L 21 6 C 21 5.476563 20.816406 4.941406 20.4375 4.5625 C 20.058594 4.183594 19.523438 4 19 4 Z M 15 6 L 19 6 L 19 7 L 15 7 Z M 10 9 L 24 9 L 24 25 C 24 25.554688 23.554688 26 23 26 L 11 26 C 10.445313 26 10 25.554688 10 25 Z M 12 12 L 12 23 L 14 23 L 14 12 Z M 16 12 L 16 23 L 18 23 L 18 12 Z M 20 12 L 20 23 L 22 23 L 22 12 Z"></path>
                        </svg>
                    </AdditionalActionButton>
                </div>

                <AdditionalActionButton class="mt-6" @click="addRow">
                    <span class="mr-3 items-center flex ">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" stroke="currentColor" fill="currentColor">
                        <path fill-rule="evenodd" d="M 11 2 L 11 11 L 2 11 L 2 13 L 11 13 L 11 22 L 13 22 L 13 13 L 22 13 L 22 11 L 13 11 L 13 2 Z"></path>
                        </svg>
                    </span>
                    Добавить еще</AdditionalActionButton>

                <div class="mt-8 flex justify-end space-x-3">
                    <SecondaryButton @click="isModalOpen = false">Отменить</SecondaryButton>
                    <PrimaryButton @click="save">Добавить</PrimaryButton>
                </div>
            </div>
        </div>
    </div>
</template>
