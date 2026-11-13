<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    adminLogin: String, directory: String, definition: Object, navigation: Array,
    group: String, search: String, rows: Object, areas: Array, notice: String,
});
const groups = [{ key: 'ukio', label: 'УКИО' }, { key: '01', label: '01' }, { key: 'gas', label: 'Газ' }];
const items = computed(() => props.navigation.filter(item => item.groups.includes(props.group)));
const url = (key, group = props.group) => route('admin.directories.index', { directory: key, group });
const groupUrl = group => url(props.navigation.find(item => item.groups.includes(group)).key, group);
const query = ref(props.search);
const editing = ref(null);
const showForm = ref(false);
const deleting = ref(null);
const form = useForm({ name: '', area_id: '' });
const deletion = useForm({});
const areaName = id => props.areas.find(area => area.id === id)?.name ?? '—';

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
function search() {
    router.get(route('admin.directories.index', props.directory), { group: props.group, q: query.value }, { preserveState: true, onSuccess: close });
}
watch(() => props.directory, () => { close(); deleting.value = null; deletion.clearErrors(); query.value = props.search; });
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
            <main class="flex-1 min-w-0 p-5">
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
                <form @submit.prevent="search" class="flex gap-2 mb-3"><input v-model="query" aria-label="Поиск по названию" placeholder="Поиск по названию" maxlength="255" class="w-full max-w-md border-gray-300 rounded text-sm" /><button class="bg-white border border-gray-300 px-4 rounded">Найти</button></form>
                <div class="overflow-x-auto border border-gray-300 bg-white">
                    <table class="w-full text-left"><thead class="bg-gray-100 border-b border-gray-300"><tr><th class="px-3 py-2 w-20">ID</th><th class="px-3 py-2">Название</th><th v-if="definition.area" class="px-3 py-2">Район</th><th class="px-3 py-2 w-52">Действия</th></tr></thead>
                        <tbody><tr v-for="row in rows.data" :key="row.id" class="border-b border-gray-200 hover:bg-gray-50"><td class="px-3 py-2 text-gray-500">{{ row.id }}</td><td class="px-3 py-2 break-words">{{ row.name }}</td><td v-if="definition.area" class="px-3 py-2">{{ areaName(row.area_id) }}</td><td class="px-3 py-2 whitespace-nowrap"><button @click="edit(row)" class="text-primary underline mr-4">Изменить</button><button @click="deleting = row; deletion.clearErrors()" class="text-red-700 underline">Удалить</button></td></tr>
                        <tr v-if="!rows.data.length"><td :colspan="definition.area ? 4 : 3" class="p-8 text-center text-gray-500">{{ search ? 'По вашему запросу ничего не найдено.' : 'Записей пока нет. Добавьте первую запись.' }}</td></tr></tbody>
                    </table>
                </div>
                <nav v-if="rows.last_page > 1" aria-label="Страницы" class="flex items-center gap-4 mt-4"><Link v-if="rows.prev_page_url" :href="rows.prev_page_url" class="text-primary underline">Назад</Link><span>Страница {{ rows.current_page }} из {{ rows.last_page }}</span><Link v-if="rows.next_page_url" :href="rows.next_page_url" class="text-primary underline">Далее</Link></nav>
            </main>
        </div>
    </div>
</template>
