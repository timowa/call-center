<script setup>
import {computed, ref} from "vue";
import ModeChanger from "@/Pages/Dashboard/Incidents/Partials/ModeChanger.vue";

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
    {id: 'type', title: 'Тип вызова', visible: true},
    {id: 'caller_number', title: 'Номер звонящего', visible: true},
    {id: 'dialed_number', title: 'Набранный номер', visible: true},
    {id: 'place_of_incident', title: 'Место происшествия', visible: true},
]);
const incidents = ref([
    {id: 123, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 3, type: 3,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 124, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 1,
        type: 2,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 125, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 1,
        type: 3,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 126, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 1,
        type: 4,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 127, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 2,
        type: 5,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 128, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 2,
        type: 4,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 129, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 2,
        type: 3,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 130, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 2,
        type: 1,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 131, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 2,
        type: 2,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 132, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status':7,
        type: 1,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 133, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 7,
        type: 3,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 134, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 7,
        type: 1,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 135, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 7,
        type: 3,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 136, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 7,
        type: 2,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 137, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 3,
        type: 2,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 138, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 3,
        type: 3,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 139, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 3,
        type: 4,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 140, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 3,
        type: 3,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 141, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 3,
        type: 4,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 142, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 3,
        type: 3,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 143, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 3,
        type: 4,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
    {id: 144, datetime: '2026-02-06 14:59:52', creator: '112 Абакан (2111219)', operator: '2111245 2111245 2111245', UKIO: '4038793', 'status': 3,
        type: 3,caller_number: '89832704737', dialed_number: '3901123311', place_of_incident: 'Сорск г.'},
]);
const tableData = computed(() => {
    return incidents.value.map(item => {
        const dateObj = new Date(item.datetime);
        return {
            ...item,
            // Timestamp для идеальной сортировки (числа сравнивать проще всего)
            ts: dateObj.getTime(),
            // Готовая строка для отображения, чтобы не вызывать функцию в шаблоне 100 раз
            displayDate: dateObj.toLocaleString('ru-RU')
        };
    });
});
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
            <tr v-for="incident in tableData" :key="incident.id" class="text-sm text-grey-400 bg-grey-150">
                <td
                    v-for="col in tableColumns" :key="col.id"
                    :data-order="col.id === 'datetime' ? incident.ts : null"
                >
                    <template v-if="col.id === 'date'" v-text="incident.displayDate"></template>
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
