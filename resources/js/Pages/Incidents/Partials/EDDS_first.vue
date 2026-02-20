<script setup>

import FormGroup from "@/Components/Form/FormGroup.vue";
import FormField from "@/Components/Form/FormField.vue";
defineProps(['form']);
</script>

<template>
<FormGroup>
    <FormField label="Вид обращения" :col-span="1" :vertical="true" v-model="form.info.type"/>
    <FormField label="Управляющая компания" :col-span="1"  :vertical="true" v-model="form.info.company"/>
    <FormField label="Инструкция" type="textarea" :col-span="2"  :vertical="true" v-model="form.info.instruction"/>
    <FormField label="Сообщение" type="textarea" :col-span="2"  :vertical="true" v-model="form.info.message"/>
    <FormField label="Дополнительная информация по объекту" type="textarea" :col-span="2"  :vertical="true" v-model="form.info.additional_infol"/>
    <div class="grid grid-cols-3 gap-6 items-end" >
        <FormField label="Контрольное время устранения" :vertical="true" :col-span="2" v-model="form.info.elimination_datetime"/>
        <FormField label="Консультация" type="checkbox" :col-span="1" v-model="form.info.is_consultation"/>
    </div>
</FormGroup>
</template>

<style scoped>

</style>
