<script setup>
import {computed, inject, provide, ref} from 'vue';
import {usePage} from '@inertiajs/vue3';
import Block from '@/Components/Block.vue';
import FormGroup from '@/Components/Form/FormGroup.vue';
import FormField from '@/Components/Form/FormField.vue';
const props = defineProps(['form']);
const page = usePage();
const parentView = inject('viewMode', ref(false));
const readOnly = computed(() => parentView.value || !page.props.auth.user.permissions.includes(props.form.gasReport.id ? 'gas.edit' : 'gas.create'));
provide('viewMode', readOnly);
</script>
<template>
<Block><FormGroup title="Газ" :cols="2">
<FormField label="Сообщение" type="textarea" :vertical="true" v-model="form.gasReport.message" />
<FormField label="Коммунальная служба" type="select" :vertical="true" :auto-select="false" :options="$page.props.directories.gasUtilities" v-model="form.gasReport.utility_id" />
<FormField label="Происшествия" type="select" :vertical="true" :auto-select="false" :options="$page.props.directories.gasEvents" v-model="form.gasReport.event_id" />
<FormField label="Инструкция" type="textarea" :vertical="true" v-model="form.gasReport.instruction" />
<FormField label="Выезд на место" type="datetime" :vertical="true" v-model="form.gasReport.departed_at" />
<FormField label="Прибытие на место" type="datetime" :vertical="true" v-model="form.gasReport.arrived_at" />
<FormField label="Принятые меры" type="textarea" :vertical="true" v-model="form.gasReport.measures" />
</FormGroup></Block>
</template>
