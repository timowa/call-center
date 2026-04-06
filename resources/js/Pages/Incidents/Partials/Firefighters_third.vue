<script setup>

import FormGroup from "@/Components/Form/FormGroup.vue";
import FormField from "@/Components/Form/FormField.vue";
import {inject} from "vue";
import {FireReportDirectories} from "@/Utils/FireReportDirectories.js";

defineProps(['form']);

</script>

<template>
    <FormGroup :cols="4">
        <FormGroup title="Погибло">
            <FormField label="Детей" type="text" :vertical="true" v-model="form.dead_children"/>
            <FormField label="Сотрудников" type="text" :vertical="true" v-model="form.dead_staff"/>
            <FormField label="Всего" type="text" :vertical="true" v-model="form.dead_total"/>
        </FormGroup>
        <FormGroup title="Ранено">
            <FormField label="Детей" type="text" :vertical="true" v-model="form.injured_children"/>
            <FormField label="Сотрудников" type="text" :vertical="true" v-model="form.injured_staff"/>
            <FormField label="Всего" type="text" :vertical="true" v-model="form.injured_total"/>
        </FormGroup>
        <FormGroup title="Спасено">
            <FormField label="Детей" type="text" :vertical="true" v-model="form.rescued_children"/>
            <FormField label="Сотрудников" type="text" :vertical="true" v-model="form.rescued_staff"/>
        </FormGroup>
        <FormGroup title="Нарушены у/ж">
            <FormField label="Детей" type="text" :vertical="true" v-model="form.violated_children"/>
            <FormField label="Сотрудников" type="text" :vertical="true" v-model="form.violated_staff"/>
        </FormGroup>
    </FormGroup>
    <FormGroup>
        <FormGroup title="Материальные ценности">
            <FormField label="Ущерб" :vertical="true" v-model="form.info.damage"/>
            <FormField label="Спасено" :vertical="true" v-model="form.info.rescued"/>
        </FormGroup>
        <FormGroup title="Информация">
            <FormField label="Площадь" :vertical="true" v-model="form.info.square"/>
            <FormField label="Расстояние" :vertical="true" v-model="form.info.distance"/>
            <FormField label="Оперативный интерес" type="checkbox" v-model="form.info.operational_interest"/>
        </FormGroup>
    </FormGroup>
    <FormGroup title="Причина пожара">
        <FormField label="Причина" type="select" :vertical="true" v-model="form.cause_id" :options="FireReportDirectories.fireService.causes"></FormField>
        <FormField label="Уточнения" :vertical="true" v-model="form.info.corrections"></FormField>
    </FormGroup>
</template>

<style scoped>

</style>
