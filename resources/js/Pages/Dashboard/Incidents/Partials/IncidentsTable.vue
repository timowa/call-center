<script setup>
import {computed, ref} from "vue";
import ModeChanger from "@/Pages/Dashboard/Incidents/Partials/ModeChanger.vue";
const props = defineProps(['incidents']);
const filterRowsButtons = [
    {
        id: 1,
        title: 'Все происшествия'
    },
    {
        id: 2,
        title: 'Нарушен регламент',
    },
    {
        id: 3,
        title: 'Созданные мной'
    }
];
const statusMap = {
    1: { label: 'Новая', color: '#10b981' }, // Зеленый
    2: { label: 'В работе', color: '#3b82f6' }, // Синий
    3: { label: 'Отработана', color: 'rgb(125, 133, 143)' }, // Тот самый серый
    7: { label: 'Отказ', color: '#ef4444' }, // Красный
};
const typeMap = {
    1: 'Пожарная',
    2: 'Полиция',
    3: 'Скорая',
    4: 'Горгаз',
};
const activeFilterRowButton = ref(1);
const activeFilterRowButtonClasses = computed(() => {
    return (buttonId) => ({
    'text-primary-light': activeFilterRowButton.value === buttonId,
    'text-grey-350': activeFilterRowButton.value !== buttonId
})});


const tableColumns = ref([
    {id: 'datetime', title: 'Дата', visible: true},
    {id: 'creator', title: 'Создатель', visible: true},
    {id: 'operator', title: 'ФИО Оператора', visible: true},
    {id: 'UKIO', title: 'УКИО', visible: true},
    {id: 'status', title: 'Состояние', visible: true},
    {id: 'service_name', title: 'Тип вызова', visible: true},
    {id: 'applicant_phone', title: 'Номер звонящего', visible: true},
    {id: 'dialed_number', title: 'Набранный номер', visible: true},
    {id: 'district_name', title: 'Место происшествия', visible: true},
]);
console.log(props.incidents)
</script>

<template>
    <ModeChanger/>

<div class="bg-white">
    <div class="flex gap-2">
        <button
            v-for="button in filterRowsButtons"
            v-text="button.title"
            :class="activeFilterRowButtonClasses(button.id)"
            class="px-4 py-4 text-sm"
        ></button>
    </div>
    <div class="overflow-auto h-full px-2">
        <table class="border-collapse border border-solid border-grey-300">
            <thead>
            <tr>
                <th v-for="column in tableColumns"
                    v-show="column.visible"
                    v-text="column.title"
                    v-bind:id="column.id"
                    class="text-sm text-grey-350 font-normal px-4 py-2 text-justify"
                ></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="incident in incidents" :key="incident.id" class="text-sm text-grey-400 bg-grey-150">
                <td
                    v-for="col in tableColumns" :key="col.id"
                    :data-order="col.id === 'datetime' ? incident.ts : null"
                >
                    <template v-if="col.id === 'date'" v-text="incident.datetime"></template>
                    <template v-else-if="col.id === 'status'">
                        <div class="flex items-center whitespace-nowrap" :title="statusMap[incident.status]?.label">
                            <div
                                class="w-3 h-3 rounded-full mr-2"
                                :style="{ backgroundColor: statusMap[incident.status]?.color }"
                            ></div>
                            <span
                                class="font-medium"
                                :style="{ color: statusMap[incident.status]?.color }"
                            >
                {{ statusMap[incident.status]?.label || 'Неизвестно' }}
            </span>
                        </div>
                    </template>
                    <template v-else-if="col.id === 'type'">
                        {{ typeMap[incident.type] || 'Другое' }}
                    </template>
                    <template v-else>{{incident[col.id]}}</template>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

</div>
</template>

<style scoped>
th, td {
    padding: 5px 12px;
    border: 1px solid theme('colors.grey-220');
    white-space: nowrap;
}
</style>
