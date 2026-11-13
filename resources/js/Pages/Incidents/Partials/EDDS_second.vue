<script setup>
import FormGroup from '@/Components/Form/FormGroup.vue';
import FormField from '@/Components/Form/FormField.vue';

defineProps(['form']);
</script>

<template>
    <FormGroup :cols="3">
        <h3 class="col-span-3 font-semibold text-sm">Информация</h3>
        <FormField label="Задействованные силы" type="textarea" :col-span="3" vertical v-model="form.results.forces" />
        <FormField label="Время убытия бригады" type="datetime" :col-span="1" vertical v-model="form.results.departed_at" />
        <FormField label="Время прибытия на место" type="datetime" :col-span="1" vertical v-model="form.results.arrived_at" />
        <FormField label="Время устранения" type="datetime" :col-span="1" vertical v-model="form.results.resolved_at" />
        <h3 class="col-span-3 font-semibold text-sm">Доведение информации до заявителя</h3>
        <FormField label="Дата и время" type="datetime" :col-span="1" vertical v-model="form.results.notified_at" />
        <FormField label="Диспетчер" :col-span="1" vertical v-model="form.results.dispatcher" />
        <FormField label="Принятые меры" type="textarea" :col-span="3" vertical v-model="form.results.measures" />
    </FormGroup>
</template>
