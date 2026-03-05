<script setup>

import FormGroup from "@/Components/Form/FormGroup.vue";
import FormField from "@/Components/Form/FormField.vue";
import {inject} from 'vue';
const props = defineProps(['form']);
const {report_types, types_of_fire_protection, objects, object_types, plans, ranks} = inject('fireReportDirectories');
</script>

<template>
    <FormGroup :cols="6">
        <input type="hidden" v-model="form.id">
        <FormField label="Вид выезда" v-model="form.report_type_id" :col-span="5"  :vertical="true" type="select" :options="report_types"/>
        <FormField label="Ошибочная карточка" v-model:checked="form.is_error_card" type="checkbox"/>
        <FormField label="Вид пожарной охраны" v-model="form.type_of_fire_protection" :vertical="true" type="select" :options="types_of_fire_protection"/>
        <FormField label="Объект" v-model="form.object_id" :vertical="true" type="select" :options="objects"/>
        <FormField label="Тип объекта" v-model="form.object_type_id" :vertical="true" type="select" :options="object_types"/>
        <FormField label="Наблюдаемые последствия пожара" v-model="form.info.observed_consequences" :vertical="true"/>
        <FormField label="Оценка времени развития пожара" v-model="form.info.time_estimation" :vertical="true"/>
        <FormField label="Характеристика подъездных путей" v-model="form.info.characteristics_of_roads " :vertical="true"/>
        <FormField label="Характеристика условий работы" v-model="form.info.characteristics_of_conditions" :col-span="6"  type="textarea" :vertical="true"/>
        <FormField label="Оценка возможности эвакуации" v-model="form.info.evacuation_possibility" :col-span="6"  type="textarea" :vertical="true"/>
        <FormField label="Наличие людей в зоне пожара" type="checkbox" v-model="form.info.has_paople"/>
        <FormField label="Объект газифицирован" type="checkbox" v-model="form.info.has_gas"/>
        <FormField label="Хозяин объекта" :col-span="6"  :vertical="true" v-model="form.info.object_owner"/>
        <FormField label="Форма собственности" :col-span="6"  :vertical="true" v-model="form.info.ownership"/>
        <FormField label="Дополнительная информация" :col-span="6"  :vertical="true" v-model="form.info.additional_info"/>
        <FormField label="План реагирования" :col-span="6"  type="select" :vertical="true" v-model="form.plan" :options="plans"/>
        <FormField label="Ранг" type="select" :vertical="true" v-model="form.rank" :options="ranks"/>
        <FormField label="№ карточки УТП" :vertical="true" v-model="form.card_number"/>
        <FormField label="Смена" :vertical="true" v-model="form.shift"/>
        <FormField label="Сообщение" :col-span="6"  :vertical="true" v-model="form.info.message"/>
    </FormGroup>
</template>

<style scoped>

</style>
