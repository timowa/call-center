<script setup>

import FormGroup from "@/Components/Form/FormGroup.vue";
import FormField from "@/Components/Form/FormField.vue";
import {inject, watch} from "vue";
import {FireReportDirectories} from "@/Utils/FireReportDirectories.js";

const props = defineProps(['form']);
const stvolColumns = [
    {key: 'type', label: 'Тип'},
    {key: 'count', label: 'Кол-во'},
    {key: 'time', label: 'Время подачи'},
];
const firefightAgents = [
    {key: 'type', label: 'Тип'},
    {key: 'count', label: 'Кол-во'},
    {key: 'time', label: 'Время подачи'},
]
const RTP = [
    {key: 'fio', label: 'Фамилия И.О.'},
    {key: 'post_id', label: 'Должность'},
    {key: 'datetime', label: 'Время прибытия'},
];
const GDZS = [
    {key: 'people', label: 'Люди'},
    {key: 'minutes', label: 'Минуты'},
    {key: 'datetime', label: 'Время включения'},
];
const personnel = [
    {key: 'fio', label: 'Фамилия И.О.'},
    {key: 'post_id', label: 'Должность'},
];

const dictionaries = FireReportDirectories.fireService;
</script>

<template>
<FormGroup :cols="4">
    <FormField label="Подавали стволы" :columns="stvolColumns" type="table" v-model="form.barrels">
        <template #rows="{row}">
            <td>{{dictionaries.barrel_types.find(t => t.id === row.type)?.name}}</td>
            <td>{{row.count}}</td>
            <td>{{row.time}}</td>
        </template>
        <template #modal-fields="{form}">
            <div class="grid grid-cols-6 gap-6">
                <FormField :vertical="true"
                           label="Тип"
                           type="select"
                           v-model="form.type"
                           :options="dictionaries.barrel_types"
                />
                <FormField :vertical="true" label="Кол-во" v-model="form.count"/>
                <FormField :vertical="true" label="Время подачи" v-model="form.time" type="datetime"/>
            </div>

        </template>
    </FormField>
    <FormField label="Огнетушащие средства" :columns="firefightAgents"  type="table" v-model="form.fire_extinguishing_agents">
        <template #rows="{row}">
            <td>{{dictionaries.fire_extinguishing_agents.find(t => t.id === row.type)?.name}}</td>
            <td>{{row.count}}</td>
            <td>{{row.time}}</td>
    </template>
        <template #modal-fields="{form}">
            <div class="grid grid-cols-6 gap-6">
                <FormField :vertical="true"
                           label="Тип"
                           type="select"
                           v-model="form.type"
                           :options="dictionaries.fire_extinguishing_agents"
                />
                <FormField :vertical="true" label="Кол-во" v-model="form.count"/>
                <FormField :vertical="true" label="Время подачи" v-model="form.time" type="datetime"/>
            </div>

        </template>
    </FormField>
    <FormField label="РТП" :columns="RTP"  type="table" v-model="form.rtp">
        <template #rows="{row}">
                <td>{{row.fio}}</td>
                <td>{{dictionaries.posts.find(p => p.id === row.post_id)?.name}}</td>
                <td>{{row.datetime}}</td>
        </template>
        <template #modal-fields="{form}">
            <div class="grid grid-cols-6 gap-6">
                <FormField :vertical="true" label="Фамилия И.О." v-model="form.fio"/>
                <FormField :vertical="true" label="Должность" :options="dictionaries.posts" v-model="form.post_id" type="select"/>
                <FormField :vertical="true" label="Время прибытия" v-model="form.datetime" type="datetime"/>
            </div>

        </template>
    </FormField>
    <FormField label="Звенья ГДЗС" :columns="GDZS" type="table" v-model="form.gdzs">
        <template #rows="{row}">
                <td>{{row.people}}</td>
                <td>{{row.minutes}}</td>
                <td>{{row.datetime}}</td>
        </template>
        <template #modal-fields="{form}">
            <div class="grid grid-cols-6 gap-6">
                <FormField :vertical="true" label="Количество людей" v-model="form.people"/>
                <FormField :vertical="true" label="Количество минут" v-model="form.minutes"/>
                <FormField :vertical="true" label="Время включения" v-model="form.datetime" type="datetime"/>
            </div>

        </template>
    </FormField>
    <FormField label="Личный состав" :columns="personnel" type="table" v-model="form.personnel">
        <template #rows="{row}">
                <td>{{row.fio}}</td>
                <td>{{dictionaries.posts.find(p => p.id === row.post_id)?.name}}</td>
        </template>
        <template #modal-fields="{form}">
            <div class="grid grid-cols-4 gap-6">
                <FormField :vertical="true" label="Фамилия И.О." v-model="form.fio"/>
                <FormField :vertical="true" label="Должность" :options="dictionaries.posts" v-model="form.post_id" type="select"/>

            </div>
           </template>
    </FormField>
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
