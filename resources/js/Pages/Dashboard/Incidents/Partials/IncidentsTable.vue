<script setup>
import {computed, onMounted, ref} from "vue";
import ModeChanger from "@/Pages/Dashboard/Incidents/Partials/ModeChanger.vue";
import {router} from "@inertiajs/vue3";
import Block from "@/Components/Block.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
const props = defineProps(['incidents']);
const selectedIncident = ref(null);
DataTable.use(DataTablesCore);
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
    {data: 'datetime', title: 'Дата', visible: true},
    {data: 'creator', title: 'Создатель', visible: true},
    {data: 'operator', title: 'ФИО Оператора', visible: true},
    {data: 'id', title: 'УКИО', visible: true},
    {data: 'status', title: 'Состояние', visible: true},
    {data: 'call_type', title: 'Тип вызова', visible: true},
    {data: 'applicant_phone', title: 'Номер звонящего', visible: true},
    {data: 'dialed_number', title: 'Набранный номер', visible: true},
    {data: 'district_name', title: 'Место происшествия', visible: true},
]);
const tableOptions = ref({
    searching: false,
    lengthChange: false,
    paging: false,
    pageLength: 20,
    language: {
        info: "Показано с _START_ по _END_ из _TOTAL_ записей",
        infoEmpty: "Записи отсутствуют",
        infoFiltered: "(отфильтровано из _MAX_ записей)",
        zeroRecords: "Ничего не найдено",
        paginate: {
            first: "Первая",
            last: "Последняя",
            next: "Следующая",
            previous: "Предыдущая"
        }
    },
    order: [[0, 'desc']],
    searchPanes: {
        controls: false
    }
});
const previewData = computed(() => {
    if (selectedIncident.value === null) {
        return [];
    }
    return [
        {'title': 'Дата', 'ref': selectedIncident.value.datetime},
        {'title': 'Создатель', 'ref': selectedIncident.value.creator},
        {'title': 'ФИО оператора', 'ref': selectedIncident.value.operator},
        {'title': 'УКИО', 'ref': selectedIncident.value.id},
        {'title': 'Состояние', 'ref': ''},
        {'title': '01', 'ref': ''},
        {'title': '02', 'ref': ''},
        {'title': '03', 'ref': ''},
        {'title': '04', 'ref': ''},
        {'title': 'АТ', 'ref': ''},
        {'title': 'ЕЖС', 'ref': ''},
        {'title': 'Тип вызова', 'ref': selectedIncident.value.call_type},
        {'title': 'Номер звонящего', 'ref': selectedIncident.value.applicant_phone},
        {'title': 'Набранный номер', 'ref': ''},
        {'title': 'Место происшествия', 'ref': ''},
        {'title': 'Район', 'ref': ''},
        {'title': 'Округ', 'ref': ''},
        {'title': 'Улица происшествия', 'ref': ''},
        {'title': 'Район города', 'ref': ''},
        {'title': 'Фамилия звонившего', 'ref': ''},
        {'title': 'Тип происшествия', 'ref': ''},
        {'title': 'Регион', 'ref': ''},
        {'title': 'Контакный телефон', 'ref': ''},
        {'title': 'Дата рождения', 'ref': ''},
        {'title': 'Описание происшествия', 'ref': ''},
    ]
});



const openWindow = function(url) {
    window.open(url, '_blank')
}
let dt;
const table = ref(); // This variable is used in the `ref` attribute for the component

onMounted(function () {
    dt = table.value.dt;

    dt.on('click', 'tr', (e) => {
        dt.rows().every(function(rowId, tableLoop, rowLoop) {
            let tr = this.node();
            tr.classList.remove('active')
        })
        const rowData = dt.row(e.currentTarget).data();
        const tr = dt.row(e.currentTarget).node();
        if (rowData && rowData.id) {
            selectedIncident.value = rowData
            tr.classList.add('active')
        }
    });
    dt.on('dblclick', 'tr', (e) => {
        const rowData = dt.row(e.currentTarget).data();
        if (rowData && rowData.id) {
            router.get(route('incidents.edit', rowData.id));
        }
    });
});
</script>

<template>
    <ModeChanger/>
<div class="grid grid-cols-4 gap-6">
    <Block class="col-span-3">
        <div class="flex gap-2">
            <button
                v-for="button in filterRowsButtons"
                v-text="button.title"
                :class="activeFilterRowButtonClasses(button.id)"
                class="px-4 py-4 text-sm"
            ></button>
        </div>
        <div class="overflow-auto h-full px-2">
            <DataTable
                ref="table"
                :options="tableOptions"
                :data="incidents"
                :columns="tableColumns"
                class="border-collapse border border-solid border-grey-300">

            </DataTable >
        </div>
    </Block>
    <Block>
        <div>
            <span class="text-lg">Предпросмотр карточки</span>
        </div>
        <div class="pt-6">
            <div
                v-for="data in previewData"
                class="grid grid-cols-2">
                <div class="text-grey-350 text-sm">{{ data.title }}</div>
                <div class="text-sm">{{ data.ref }}</div>
            </div>
            <div v-if="selectedIncident !== null" class="mt-6">
                <div class="grid grid-cols-4 gap-3">
                    <SecondaryButton class="col-span-3"
                    @click="openWindow(route('incidents.edit', selectedIncident.id))"
                    >
                        <span class="text-nowrap overflow-hidden">Открыть в отдельной вкладке</span>
                    </SecondaryButton>
                    <PrimaryButton class="text-nowrap overflow-hidden"
                                   @click="router.get(route('incidents.edit', selectedIncident.id))"><span>Открыть</span></PrimaryButton>
                </div>
            </div>
        </div>
    </Block>
</div>

</template>

<style scoped>
:deep(.border-collapse) th,
:deep(.border-collapse) td {
    padding: 5px 12px;
    border-left: 1px solid theme('colors.grey-220');
    border-right: 1px solid theme('colors.grey-220');
    white-space: nowrap;
    color: theme('colors.grey-370');
    font-weight: 400;
    cursor: pointer;
}

:deep(.border-collapse) tr.active td {
    background-color: #EFF5FE;
}
:deep(.border-collapse) tr {
    border-top: 1px solid theme('colors.grey-220');
    border-bottom: 1px solid theme('colors.grey-220');
}

:deep(.border-collapse) tr:hover td {
    border-top: 1px solid theme('colors.primary-light');
    border-bottom: 1px solid theme('colors.primary-light');
}


:deep(.border-collapse) th.dt-ordering-desc,
:deep(.border-collapse) th.dt-ordering-asc {
    box-shadow: inset 0 0 10px theme('colors.grey-220');
}
</style>
