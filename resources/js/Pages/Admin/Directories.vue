<script setup>
import { computed, onUnmounted, ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';

DataTable.use(DataTablesCore);

const props = defineProps({
    adminLogin: String, directory: String, definition: Object, navigation: Array,
    group: String, search: String, rows: Object, areas: Array, notice: String,
});
const groups = [{ key: 'ukio', label: 'УКИО' }, { key: '01', label: '01' }, { key: 'gas', label: 'Газ' }];
const items = computed(() => props.navigation.filter(item => item.groups.includes(props.group)));
const url = (key, group = props.group) => route('admin.directories.index', { directory: key, group });
const groupUrl = group => url(props.navigation.find(item => item.groups.includes(group)).key, group);
const editing = ref(null);
const showForm = ref(false);
const deleting = ref(null);
const form = useForm({ name: '', area_id: '' });
const deletion = useForm({});
const areaName = id => props.areas.find(area => area.id === id)?.name ?? '—';
const tableData = computed(() => props.rows.data.map(row => ({ ...row, area_name: areaName(row.area_id) })));
const tableColumns = computed(() => [
    { data: 'id', title: 'ID' },
    { data: 'name', title: 'Название', render: DataTablesCore.render.text() },
    ...(props.definition.area ? [{ data: 'area_name', title: 'Район', render: DataTablesCore.render.text() }] : []),
    { data: null, title: 'Действия', orderable: false, searchable: false, render: '#actions' },
]);
const tableOptions = {
    paging: true, searching: true, ordering: true, pageLength: 25,
    lengthMenu: [10, 25, 50, 100], order: [[0, 'desc']],
    language: {
        search: 'Поиск:', lengthMenu: 'Показывать _MENU_ записей',
        info: 'Записи _START_–_END_ из _TOTAL_', infoEmpty: 'Записей нет',
        infoFiltered: '(всего _MAX_)', zeroRecords: 'Ничего не найдено',
        emptyTable: 'Записей пока нет. Добавьте первую запись.',
        paginate: { first: 'Первая', last: 'Последняя', next: 'Далее', previous: 'Назад' },
        aria: { orderable: 'Сортировать по столбцу', orderableReverse: 'Изменить порядок сортировки', orderableRemove: 'Отменить сортировку' },
    },
};
const pendingRequests = ref(0);
const loading = computed(() => pendingRequests.value > 0);
const stopStartListener = router.on('start', () => { pendingRequests.value++; });
// Inertia also emits finish after errors and cancelled visits.
const stopFinishListener = router.on('finish', () => {
    pendingRequests.value = Math.max(0, pendingRequests.value - 1);
});
onUnmounted(() => {
    stopStartListener();
    stopFinishListener();
});

function close() {
    showForm.value = false;
    editing.value = null;
    form.reset();
    form.clearErrors();
}
function edit(row = null) {
    form.clearErrors();
    editing.value = row?.id ?? null;
    form.name = row?.name ?? '';
    form.area_id = row?.area_id ?? '';
    showForm.value = true;
}
function save() {
    const options = { preserveScroll: true, onSuccess: close };
    if (editing.value) form.put(route('admin.directories.update', { directory: props.directory, id: editing.value }), options);
    else form.post(route('admin.directories.store', props.directory), options);
}
function remove() {
    deletion.delete(route('admin.directories.destroy', { directory: props.directory, id: deleting.value.id }), {
        preserveScroll: true, onSuccess: () => { deleting.value = null; },
    });
}
watch(() => props.directory, () => { close(); deleting.value = null; deletion.clearErrors(); });
</script>

<template>
    <Head :title="`${definition.label} — администрирование`" />
    <div class="min-h-screen bg-gray-100 text-gray-800 text-sm">
        <header class="bg-primary text-white flex items-center justify-between gap-4 px-5 py-3">
            <h1 class="font-semibold text-lg">Администрирование справочников</h1>
            <div class="flex items-center gap-4"><span>{{ adminLogin }}</span><Link :href="route('admin.logout')" method="post" as="button" class="underline">Выйти</Link></div>
        </header>
        <div class="flex flex-col md:flex-row min-h-[calc(100vh-52px)]">
            <aside class="md:w-64 shrink-0 bg-white border-r border-gray-300">
                <nav aria-label="Разделы" class="flex border-b border-gray-300">
                    <Link v-for="tab in groups" :key="tab.key" :href="groupUrl(tab.key)" :aria-current="group === tab.key ? 'page' : undefined"
                          class="flex-1 text-center px-3 py-3 font-semibold border-b-2" :class="group === tab.key ? 'border-primary text-primary bg-gray-50' : 'border-transparent hover:bg-gray-50'">{{ tab.label }}</Link>
                </nav>
                <nav aria-label="Справочники" class="py-2">
                    <Link v-for="item in items" :key="item.key" :href="url(item.key)" :aria-current="directory === item.key ? 'page' : undefined"
                          class="block px-5 py-2.5 border-l-4" :class="directory === item.key ? 'border-primary bg-gray-100 text-primary font-semibold' : 'border-transparent hover:bg-gray-50'">{{ item.label }}</Link>
                </nav>
            </aside>
            <main class="relative flex-1 min-w-0 p-5" :aria-busy="loading">
                <div v-if="loading" role="status" aria-live="polite" class="absolute inset-0 z-20 flex items-start justify-center bg-white/75 pt-24 cursor-wait">
                    <div class="flex items-center gap-3 rounded border border-gray-300 bg-white px-5 py-3 shadow-sm">
                        <span aria-hidden="true" class="h-5 w-5 animate-spin rounded-full border-2 border-gray-300 border-t-primary motion-reduce:animate-none"></span>
                        <span>Обработка запроса…</span>
                    </div>
                </div>
                <div :inert="loading">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <div><h2 class="font-semibold text-xl">{{ definition.label }}</h2><p class="text-gray-500 mt-1">Записей: {{ rows.total }}<span v-if="directory === 'objects'"> · Общий справочник УКИО и 01</span></p></div>
                    <button @click="edit()" class="bg-primary text-white px-4 py-2 rounded">Добавить</button>
                </div>
                <p v-if="notice" role="status" class="border border-green-300 bg-green-50 text-green-800 p-3 mb-4">{{ notice }}</p>
                <form v-if="showForm" @submit.prevent="save" class="bg-white border border-gray-300 p-4 mb-4 space-y-3">
                    <h3 class="font-semibold">{{ editing ? 'Редактирование записи' : 'Новая запись' }}</h3>
                    <div><label for="name" class="block mb-1">Название</label><input id="name" v-model="form.name" required maxlength="255" class="w-full border-gray-300 rounded text-sm" /><p v-if="form.errors.name" role="alert" class="text-red-700 mt-1">{{ form.errors.name }}</p></div>
                    <div v-if="definition.area"><label for="area" class="block mb-1">Район</label><select id="area" v-model="form.area_id" required class="w-full border-gray-300 rounded text-sm"><option disabled value="">Выберите район</option><option v-for="area in areas" :key="area.id" :value="area.id">{{ area.name }}</option></select><p v-if="form.errors.area_id" role="alert" class="text-red-700 mt-1">{{ form.errors.area_id }}</p></div>
                    <div class="flex gap-3"><button :disabled="form.processing" class="bg-primary text-white px-4 py-2 rounded disabled:opacity-50">Сохранить</button><button type="button" @click="close" class="border border-gray-300 px-4 py-2 rounded">Отмена</button></div>
                </form>
                <div v-if="deleting" role="alert" class="border border-red-300 bg-red-50 p-4 mb-4">
                    <p>Удалить «{{ deleting.name }}»?</p>
                    <p v-if="deletion.errors.delete" class="text-red-700 mt-2">{{ deletion.errors.delete }}</p>
                    <div class="flex gap-3 mt-3"><button @click="remove" :disabled="deletion.processing" class="bg-red-700 text-white px-4 py-2 rounded disabled:opacity-50">Удалить</button><button @click="deleting = null; deletion.clearErrors()" class="border border-gray-300 bg-white px-4 py-2 rounded">Отмена</button></div>
                </div>
                <div class="admin-table overflow-x-auto border border-gray-300 bg-white p-3">
                    <DataTable :key="directory" :data="tableData" :columns="tableColumns" :options="tableOptions" class="w-full text-left" style="width: 100%">
                        <template #actions="{ rowData }">
                            <button @click="edit(rowData)" class="text-primary underline mr-4">Изменить</button>
                            <button @click="deleting = rowData; deletion.clearErrors()" class="text-red-700 underline">Удалить</button>
                        </template>
                    </DataTable>
                </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.admin-table :deep(.dt-container) { width: 100%; }
.admin-table :deep(.dt-layout-full) { flex: 1 1 100%; min-width: 0; width: 100%; }
.admin-table :deep(table.dataTable) { width: 100% !important; }
.admin-table :deep(.dt-layout-row) { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; margin: .75rem 0; }
.admin-table :deep(.dt-input) { border: 1px solid #d1d5db; border-radius: .25rem; margin: 0 .5rem; padding: .35rem .5rem; }
.admin-table :deep(select.dt-input) { min-width: 4.5rem; padding-right: 2rem; background-position: right .5rem center; }
.admin-table :deep(th), .admin-table :deep(td) { padding: .5rem .75rem; border-bottom: 1px solid #e5e7eb; }
.admin-table :deep(th) { background: #f3f4f6; }
.admin-table :deep(th.dt-orderable-asc) { cursor: pointer; }
.admin-table :deep(th.dt-orderable-asc .dt-column-title::after) { content: ' ↕'; color: #6b7280; }
.admin-table :deep(th.dt-ordering-asc .dt-column-title::after) { content: ' ↑'; }
.admin-table :deep(th.dt-ordering-desc .dt-column-title::after) { content: ' ↓'; }
.admin-table :deep(.dt-paging-button) { padding: .35rem .65rem; border: 1px solid #d1d5db; border-radius: .25rem; margin: .15rem; }
.admin-table :deep(.dt-paging-button.current) { background: #e5e7eb; font-weight: 600; }
.admin-table :deep(.dt-paging-button.disabled) { opacity: .4; cursor: default; }
</style>
