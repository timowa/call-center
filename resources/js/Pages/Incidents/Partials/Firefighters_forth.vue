<script setup>
import FormGroup from '@/Components/Form/FormGroup.vue';
import FormField from '@/Components/Form/FormField.vue';
import {FireReportDirectories} from '@/Utils/FireReportDirectories.js';
import {computed, inject, nextTick, ref} from 'vue';
import {usePage} from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DateTimeEditor from '@/Components/Form/DateTimeEditor.vue';
import {nowInTimezone, formatDateTime} from '@/Utils/dateTime.js';
const page = usePage();
const transition = inject('departmentTransition', null);
const pending = ref(false);
const transitionError = ref('');
const props = defineProps({form: Object, edds: Boolean});
const viewMode = inject('viewMode', ref(false));
const canEdit = computed(() => !viewMode.value && (props.edds || page.props.auth.user.permissions.includes('01.edit')));
const rows = computed(() => props.edds ? props.form.responses : props.form.departments);
const departmentName = ref('');
const stages = [
    {id: 1, name: 'Заявка создана', field: 'requested_at'},
    {id: 2, name: 'Заявка принята', field: 'accepted_at'},
    {id: 3, name: 'Прибытие', field: 'arrived_at'},
    {id: 4, name: 'Заявка отработана', field: 'completed_at'},
];
const search = ref('');
const selected = ref([]);
const expanded = ref({});
const addDialog = ref();
const timeDialog = ref();
const editing = ref(null);
const filtered = computed(() => (FireReportDirectories.fireService?.departments ?? []).filter(d => d.name.toLowerCase().includes(search.value.trim().toLowerCase())));
const attached = id => rows.value.some(d => Number(d.department_id) === Number(id));
const nameExists = computed(() => rows.value.some(row => row.department_name?.toLowerCase() === departmentName.value.trim().toLowerCase()));
const openAdd = () => {
    if (!canEdit.value) return;
    search.value = ''; selected.value = [];
    departmentName.value = '';
    if (!props.edds) FireReportDirectories.fetchFireDirectories();
    addDialog.value.showModal();
};
const saveDepartments = () => {
    if (!canEdit.value) return;
    if (props.edds) {
        const name = departmentName.value.trim();
        if (!name || nameExists.value) return;
        rows.value.push({
            department_name: name, condition_id: 1,
            requested_at: nowInTimezone(page.props.timezone),
            operator_name: `${page.props.auth.user.name} (${page.props.auth.user.uid})`,
        });
        addDialog.value.close();
        return;
    }
    const departments = FireReportDirectories.fireService?.departments ?? [];
    for (const id of selected.value) {
        if (attached(id)) continue;
        const department = departments.find(d => d.id === id);
        if (!department) continue;
        props.form.departments.push({
            department_id: id, department_name: department.name, condition_id: 1,
            requested_at: nowInTimezone(page.props.timezone), accepted_at: null, arrived_at: null, completed_at: null,
            transitions: [], operator_name: `${page.props.auth.user.name} (${page.props.auth.user.uid})`,
        });
    }
    addDialog.value.close();
};
const updateCondition = async (row, stage) => {
    if (!canEdit.value || pending.value || stage.id !== Number(row.condition_id) + 1) return;
    if (transition && !props.edds) {
        pending.value = true; transitionError.value = '';
        try { if (await transition(row, stage)) return; }
        catch (error) { transitionError.value = error.response?.data?.message || 'Переход не подтверждён. Обновите карточку перед повторной попыткой.'; return; }
        finally { pending.value = false; }
    }
    row.condition_id = stage.id;
    row[stage.field] = nowInTimezone(page.props.timezone);
    if (!props.edds) (row.transitions ??= []).push(stage.id);
};
const editTime = async (row, stage) => {
    if (!canEdit.value || stage.id > row.condition_id) return;
    editing.value = {row, stage};
    await nextTick(); timeDialog.value.showModal();
};
const closeTime = () => { timeDialog.value.close(); editing.value = null; };
const saveTime = value => { if (canEdit.value) editing.value.row[editing.value.stage.field] = value; closeTime(); };
</script>
<template>
    <p v-if="transitionError" role="alert" class="text-red-700 p-3">{{ transitionError }}</p>
    <FormGroup :cols="6">
        <div class="col-span-6 bg-white rounded border border-gray-200">
            <div class="flex items-center gap-3 p-3 font-semibold text-sm">Отделения
                <button type="button" :disabled="!canEdit" @click="openAdd" aria-label="Добавить отделения" class="rounded-full bg-blue-50 text-primary w-6 h-6 disabled:opacity-40">+</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-gray-500"><tr><th>Отделение</th><th>Состояние</th><th>Время изменения</th><th>Оператор, привлекший отделение (PIN)</th><th></th></tr></thead>
                    <tbody>
                        <tr v-if="!rows.length"><td colspan="5" class="text-center text-gray-400">Нет данных</td></tr>
                        <template v-for="(row, index) in rows" :key="row.department_id ?? index">
                            <tr>
                                <td>{{ row.department_name }}</td>
                                <td><Dropdown>
                                    <template #trigger><button type="button" :disabled="!canEdit" class="text-primary disabled:text-gray-500">{{ stages[row.condition_id - 1]?.name }}</button></template>
                                    <template #content><button v-for="stage in stages" type="button" @click="updateCondition(row, stage)" :disabled="!canEdit || pending || stage.id !== Number(row.condition_id) + 1" class="text-left px-2 py-1 text-primary disabled:text-gray-400">{{ stage.name }}</button></template>
                                </Dropdown></td>
                                <td>{{ formatDateTime(row[stages[row.condition_id - 1]?.field]) }}</td>
                                <td>{{ row.operator_name }}</td>
                                <td><button type="button" :aria-expanded="!!expanded[index]" aria-label="Хронология отделения" @click="expanded[index] = !expanded[index]">{{ expanded[index] ? '⌃' : '⌄' }}</button></td>
                            </tr>
                            <tr v-if="expanded[index]">
                                <td colspan="5">
                                    <ol class="flex py-3 min-w-[650px]">
                                        <li v-for="stage in stages" class="flex-1 relative">
                                            <div class="absolute top-2 left-2 right-0 border-t border-gray-200" v-if="stage.id < 4"></div>
                                            <div class="relative rounded-full w-4 h-4 mb-3" :class="stage.id === Number(row.condition_id) ? 'bg-blue-500 ring-4 ring-blue-100' : 'bg-gray-200'"></div>
                                            <template v-if="stage.id <= row.condition_id">
                                                <div class="font-semibold">{{ stage.name }} <button v-if="canEdit" type="button" :aria-label="`Изменить время: ${stage.name}`" class="ml-2 text-primary" @click="editTime(row, stage)">✎</button></div>
                                                <div class="mt-1 text-xs">{{ formatDateTime(row[stage.field]) }}</div>
                                            </template>
                                        </li>
                                    </ol>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
        <template v-if="!edds">
        <FormField label="Локализация" type="datetime" :vertical="true" v-model="form.localized_at" />
        <FormField label="Ликвидация открытого огня" type="datetime" :vertical="true" v-model="form.fire_eliminated_at" />
        <FormField label="Ликвидация" type="datetime" :vertical="true" v-model="form.elimination_at" />
        </template>
    </FormGroup>
    <dialog ref="addDialog" class="rounded-lg p-5 w-96 max-w-[95vw] max-h-[90vh] overflow-auto backdrop:bg-black/40" @keydown.esc.stop>
        <h3 class="font-semibold mb-4">Добавление отделений</h3>
        <label v-if="edds" class="block text-sm">Название отделения<input v-model="departmentName" maxlength="255" class="block w-full rounded border-gray-300" /></label>
        <label v-else class="flex items-center gap-2 bg-gray-100 rounded px-2">⌕<input v-model="search" aria-label="Поиск" placeholder="Поиск" class="w-full bg-transparent border-0 text-sm" /></label>
        <div v-if="!edds" class="min-h-48 max-h-80 overflow-auto px-1 py-3">
            <p v-if="FireReportDirectories.isLoading">Загрузка…</p>
            <div v-else-if="FireReportDirectories.error" role="alert">{{ FireReportDirectories.error }} <button type="button" class="text-primary" @click="FireReportDirectories.fetchFireDirectories()">Повторить</button></div>
            <template v-else>
                <p v-if="!filtered.length" class="text-gray-500">Ничего не найдено</p>
                <label v-for="department in filtered" :key="department.id" class="flex items-center gap-2 py-2 text-sm" :class="attached(department.id) ? 'text-gray-400' : ''">
                    <input type="checkbox" v-model="selected" :value="department.id" :disabled="attached(department.id)" />{{ department.name }}<span v-if="attached(department.id)">(добавлено)</span>
                </label>
            </template>
        </div>
        <div class="flex justify-end gap-3 mt-4"><button type="button" @click="addDialog.close()" class="text-primary">Отмена</button><button type="button" @click="saveDepartments" :disabled="edds ? !departmentName.trim() || nameExists : !selected.length" class="bg-primary text-white rounded px-3 py-2 disabled:opacity-40">Сохранить</button></div>
    </dialog>
    <dialog ref="timeDialog" class="rounded-lg p-5 w-[400px] max-w-[95vw] max-h-[90vh] overflow-auto backdrop:bg-black/40" @cancel.prevent="closeTime" @keydown.esc.stop.prevent="closeTime">
        <template v-if="editing">
            <h3 class="font-semibold mb-4">Изменить время</h3>
            <div class="text-gray-500 text-sm">Статус</div><div class="font-semibold mb-4">{{ editing.stage.name }}</div>
            <DateTimeEditor :value="editing.row[editing.stage.field]" required @save="saveTime" @cancel="closeTime" />
        </template>
    </dialog>
</template>
<style scoped>
th, td { padding: 8px 12px; border-bottom: 1px solid #e5e7eb; }
th { border-right: 1px solid #e5e7eb; font-weight: normal; }
</style>
