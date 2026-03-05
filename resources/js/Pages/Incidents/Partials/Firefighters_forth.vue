<script setup>

import FormGroup from "@/Components/Form/FormGroup.vue";
import FormField from "@/Components/Form/FormField.vue";

const columns = [
    {key: 'department', label: 'Отделение'},
    {key: 'condition', label: 'Состояник'},
    {key: 'updated_at', label: 'Время изменения'},
    {key: 'operator', label: 'Оператор, привлекший отделение (PIN)'}
];

defineProps(['form']);
</script>

<template>
<FormGroup :cols="6">
    <FormField label="Отделения" :col-span="6" type="table" :columns="columns" :vertical="true"/>
    <FormField label="Локализация" type="datetime" :vertical="true" v-model="form.localized_at"/>
    <FormField label="Ликвидация открытого огня" type="datetime" :vertical="true" v-model="form.fire_eliminated_at"/>
    <FormField label="Ликвидация" type="datetime" :vertical="true" v-model="form.elimination_at"/>
</FormGroup>
</template>

<style scoped>

</style>
