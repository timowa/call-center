<script setup>

import FormGroup from "@/Components/Form/FormGroup.vue";
import FormField from "@/Components/Form/FormField.vue";
</script>

<template>
    <FormGroup :cols="6">
        <FormField type="select" :col-span="5" label="Вид выезда" :vertical="true"></FormField>
        <FormField type="checkbox" label="Ошибочная карточка"></FormField>
        <FormField type="select" label="Вид пожарной охраны" :vertical="true"></FormField>
        <FormField type="select" label="Объект" :vertical="true"></FormField>
        <FormField type="select" label="Тип объекта" :vertical="true"></FormField>
        <FormField label="Наблюдаемые последствия пожара" :vertical="true"></FormField>
        <FormField label="Оценка времени развития пожара" :vertical="true"></FormField>
        <FormField label="Характеристика подъездных путей" :vertical="true"></FormField>
        <FormField label="Характеристика условий работы" :col-span="6"  type="textarea" :vertical="true"></FormField>
        <FormField label="Оценка возможности эвакуации" :col-span="6"  type="textarea" :vertical="true"></FormField>
        <FormField type="checkbox" label="Наличие людей в зоне пожара"></FormField>
        <FormField type="checkbox" label="Объект газифицирован"></FormField>
        <FormField label="Хозяин объекта" :col-span="6"  :vertical="true"></FormField>
        <FormField label="Форма собственности" :col-span="6"  :vertical="true"></FormField>
        <FormField label="Дополнительная информация" :col-span="6"  :vertical="true"></FormField>
        <FormField label="План реагирования" :col-span="6"  type="select" :vertical="true"></FormField>
        <FormField label="Ранг" type="select" :vertical="true"></FormField>
        <FormField label="№ карточки УТП" :vertical="true"></FormField>
        <FormField label="Смена" :vertical="true"></FormField>
        <FormField label="Сообщение" :col-span="6"  :vertical="true"></FormField>
    </FormGroup>
</template>

<style scoped>

</style>
