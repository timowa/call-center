<script setup>

import FormGroup from "@/Components/Form/FormGroup.vue";
import FormField from "@/Components/Form/FormField.vue";
import {FireReportDirectories} from "@/Utils/FireReportDirectories.js";
import TextInput from "@/Components/Form/TextInput.vue";
import {computed, inject, ref} from "vue";
import {Link, usePage} from "@inertiajs/vue3";
import Dropdown from "@/Components/Dropdown.vue";

const page = usePage();
const props = defineProps(['form']);
const viewMode = inject('viewMode', ref(false));

const columns = [
    {key: 'department', label: 'Отделение'},
    {key: 'condition', label: 'Состояник'},
    {key: 'updated_at', label: 'Время изменения'},
    {key: 'operator', label: 'Оператор, привлекший отделение (PIN)'}
];

const searchValue = ref('');
const dictionaries = FireReportDirectories.fireService;
const conditionsArray = computed(() => Object.values(dictionaries.department_conditions));
const filteredDepartments = computed(() => {
    const query = searchValue.value.toLowerCase().trim();

    if (!query) {
        return dictionaries.departments;
    }

    return dictionaries.departments.filter(dept =>
        dept.name.toLowerCase().includes(query)
    );
});
const handleSave = (modalForm) => {
    const selectedIds = Object.keys(modalForm).filter(id => modalForm[id] === true);

    if (selectedIds.length === 0) return;

    const newEntries = selectedIds.map(deptId => {
        const id = Number(deptId);
        const dept = dictionaries.departments.find(d => d.id === id);

        return {
            department_id: id,
            department_name: dept?.name || 'Н/Д',
            condition_id: dictionaries.department_conditions.CREATED.id,
            created_at: new Date().toLocaleString(),
            operator_name: page.props.auth.user.name + ' (' + page.props.auth.user.uid + ')'
        };
    });

    props.form.departments = [...props.form.departments, ...newEntries];

}

const updateCondition = (row, newConditionId) => {
    row.condition_id = Number(newConditionId);
    row.updated_at = new Date().toLocaleString();
};
</script>

<template>
<FormGroup :cols="6">
    <FormField
        label="Отделения"
        :col-span="6"
        type="table"
        :columns="columns"
        :vertical="true"
        @custom-save="handleSave"
        v-model="form.departments"
        :with-action-buttons="false"
    >
        <template #rows="{row}">
            <td>{{row.department_name}}</td>
            <td>
                <Dropdown :row="row">
                    <template #trigger>
                        <button type="button" class="text-primary disabled:cursor-not-allowed" :disabled="viewMode">
                            {{conditionsArray.find(c => c.id === row.condition_id)?.name}}
                        </button>
                    </template>
                    <template #content>
                        <button
                            v-for="condition in dictionaries.department_conditions"
                            type="button"
                            @click="updateCondition(row, condition.id)"
                            :disabled="Number(condition.id) !== Number(row.condition_id) + 1"
                            class="text-primary text-start px-2 disabled:text-grey-370">{{condition.name}}</button>
                    </template>
                </Dropdown>

            </td>
            <td>{{row.created_at}}</td>
            <td>{{row.operator_name}}</td>
        </template>
        <template #modal-fields="{form}">
            <TextInput v-model="searchValue" class="w-full" placeholder="Поиск"/>

            <div class="max-h-96 overflow-auto">
                <FormField v-for="department in filteredDepartments"
                           type="checkbox"
                           :label="department.name"
                           :key="department.id"
                           :value="department.id"
                           v-model="form[department.id]"
                           class="my-2 text-lg"

                />
            </div>

        </template>
    </FormField>
    <FormField label="Локализация" type="datetime" :vertical="true" v-model="form.localized_at"/>
    <FormField label="Ликвидация открытого огня" type="datetime" :vertical="true" v-model="form.fire_eliminated_at"/>
    <FormField label="Ликвидация" type="datetime" :vertical="true" v-model="form.elimination_at"/>
</FormGroup>
</template>

<style scoped>

</style>
