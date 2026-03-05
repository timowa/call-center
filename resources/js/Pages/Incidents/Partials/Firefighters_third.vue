<script setup>

import FormGroup from "@/Components/Form/FormGroup.vue";
import FormField from "@/Components/Form/FormField.vue";
import {Form} from "@inertiajs/vue3";
</script>

<template>
    <FormGroup :cols="4">
        <FormGroup title="Погибло">
            <FormField label="Детей" type="text" :vertical="true"/>
            <FormField label="Сотрудников" type="text" :vertical="true"/>
            <FormField label="Всего" type="text" :vertical="true"/>
        </FormGroup>
        <FormGroup title="Ранено">
            <FormField label="Детей" type="text" :vertical="true"/>
            <FormField label="Сотрудников" type="text" :vertical="true"/>
            <FormField label="Всего" type="text" :vertical="true"/>
        </FormGroup>
        <FormGroup title="Спасено">
            <FormField label="Детей" type="text" :vertical="true"/>
            <FormField label="Сотрудников" type="text" :vertical="true"/>
        </FormGroup>
        <FormGroup title="Нарушены у/ж">
            <FormField label="Детей" type="text" :vertical="true"/>
            <FormField label="Сотрудников" type="text" :vertical="true"/>
        </FormGroup>
    </FormGroup>
    <FormGroup>
        <FormGroup title="Материальные ценности">
            <FormField label="Ущерб" :vertical="true"/>
            <FormField label="Спасено" :vertical="true"/>
        </FormGroup>
        <FormGroup title="Информация">
            <FormField label="Площадь" :vertical="true"/>
            <FormField label="Расстояние" :vertical="true"/>
            <FormField label="Оперативный интерес" type="checkbox"/>
        </FormGroup>
    </FormGroup>
    <FormGroup title="Причина пожара">
        <FormField label="Причина" type="select" :vertical="true"></FormField>
        <FormField label="Уточнения" :vertical="true"></FormField>
    </FormGroup>
</template>

<style scoped>

</style>
