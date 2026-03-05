<script setup>

import FormGroup from "@/Components/Form/FormGroup.vue";
import FormField from "@/Components/Form/FormField.vue";

const columns = [
    {key: 'department', label: 'Отделение'},
    {key: 'condition', label: 'Состояник'},
    {key: 'updated_at', label: 'Время изменения'},
    {key: 'operator', label: 'Оператор, привлекший отделение (PIN)'}
];
</script>

<template>
<FormGroup :cols="6">
    <FormField label="Отделения" :col-span="6" type="table" :columns="columns" :vertical="true"/>
    <FormField label="Локализация" type="text" :vertical="true"/>
    <FormField label="Ликвидация открытого огня" type="text" :vertical="true"/>
    <FormField label="Ликвидация" type="text" :vertical="true"/>
</FormGroup>
</template>

<style scoped>

</style>
