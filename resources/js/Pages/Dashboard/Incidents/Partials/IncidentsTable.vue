<script setup>
import {computed, inject, onMounted, ref} from "vue";
import ModeChanger from "@/Pages/Dashboard/Incidents/Partials/ModeChanger.vue";
import {router, usePage} from "@inertiajs/vue3";
import Block from "@/Components/Block.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import {getConditionColor, getConditionLabel} from '@/Utils/conditions.js'

const incidents = inject('incidents');
const {setSelected} = inject('selectedIncident');
const page = usePage();
const user = computed(() => page.props.auth.user);

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
    {data: (i, l, c) => {
            if (user.value.roles.includes('cov_112')) {
                return i.condition;
            }
            if (user.value.roles.includes('op_01')) {
                return i.fireReport?.condition
            }
        }, title: 'Состояние', visible: true},
    {data: 'call_type', title: 'Тип вызова', visible: true},
    {data: 'incoming_number', title: 'Номер звонящего', visible: true},
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
    },
    scrollCollapse: true,
    fixedHeader: true,
    info: false
});

let dt;
const table = ref();

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
            setSelected(rowData);
            tr.classList.add('active');
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
    <Block class="h-full flex flex-col overflow-hidden">
        <div class="flex gap-2 flex-shrink-0">
            <button
                v-for="button in filterRowsButtons"
                v-text="button.title"
                :class="activeFilterRowButtonClasses(button.id)"
                class="px-4 py-4 text-sm"
            ></button>
        </div>
        <div class="flex-1 overflow-auto px-2 relative">
            <DataTable
                ref="table"
                :options="tableOptions"
                :data="incidents"
                :columns="tableColumns"
                class="border-collapse border border-solid border-grey-300 w-full h-full">
                <template #column-4="props">
                    <div class="flex items-center gap-2">
                        <div :class="['w-3 h-3 rounded-sm', getConditionColor(props.cellData)]"></div>
                        {{ getConditionLabel(props.cellData) }}
                    </div>

                </template>
            </DataTable >
        </div>
    </Block>
</template>

<style scoped>
:deep(.border-collapse) thead {
    position: sticky;
    top: 0;
    z-index: 10;
}
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
:deep(.dataTables_wrapper) {
    height: 100%;
    display: flex;
    flex-direction: column;
}

:deep(.dataTable) {
    width: 100% !important;
    margin-top: 0 !important;
}


:deep(.dataTable) {
    border-spacing: 0;
}
</style>
