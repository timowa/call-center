<script setup>

import FormGroup from "@/Components/Form/FormGroup.vue";
import FormField from "@/Components/Form/FormField.vue";
import {inject} from "vue";
import {FireReportDirectories} from "@/Utils/FireReportDirectories.js";

defineProps(['form']);
const stvolColumns = [
    {key: 'type', label: 'Тип', type: 'select', options: FireReportDirectories.fireService.barrel_types},
    {key: 'count', label: 'Кол-во', type: 'text'},
    {key: 'time', label: 'Время подачи', type: 'text'},
];
const firefightAgents = [
    {key: 'type', label: 'Тип', type: 'text'},
    {key: 'count', label: 'Кол-во', type: 'text'},
    {key: 'time', label: 'Время подачи', type: 'text'},
]
const RTP = [
    {key: 'FIO', label: 'Фамилия И.О.', type: 'text'},
    {key: 'post', label: 'Должность', type: 'text'},
    {key: 'time', label: 'Время прибытия', type: 'text'},
];
const GDZS = [
    {key: 'people', label: 'Люди', type: 'text'},
    {key: 'minutes', label: 'Минуты', type: 'text'},
    {key: 'time', label: 'Время включения', type: 'text'},
];
const personnel = [
    {key: 'FIO', label: 'Фамилия И.О.', type: 'text'},
    {key: 'post', label: 'Должность', type: 'text'},
];

</script>

<template>
<FormGroup :cols="4">
    <FormField label="Подавали стволы" :columns="stvolColumns" :vertical="true" type="table" v-model="form.barrels"></FormField>
    <FormField label="Огнетушащие средства" :columns="firefightAgents" :vertical="true" type="table" v-model="form.fire_extinguishing_agents"></FormField>
    <FormField label="РТП" :columns="RTP" :vertical="true" type="table" v-model="form.rtp"></FormField>
    <FormField label="Звенья ГДЗС" :columns="GDZS" :vertical="true" type="table" v-model="form.gdzs"></FormField>
    <FormField label="Личный состав" :columns="personnel" :vertical="true" type="table" v-model="form.personnel"></FormField>
</FormGroup>
    <FormGroup :cols="6">
        <FormField label="Расход воды (л)" :vertical="true" v-model="form.water_consumption"/>
        <FormField label="Время подачи первого ствола (мин)" :vertical="true" v-model="form.first_water_barrel_minutes"/>
        <FormField label="Водоисточник" :vertical="true" type="select" v-model="form.water_source_id"
                   :options="FireReportDirectories.fireService.water_sources"/>
    </FormGroup>
</template>

<style scoped>

</style>
