<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminSmsLayout from '@/Layouts/AdminSmsLayout.vue';
const props = defineProps({ adminLogin: String, scenario: Object });
const defaultLabels = ["Адресная информация о местоположении ТС", "Государственный регистрационный номер ТС", "Цвет кузова ТС", "Марка и/или модель ТС", "Тип ТС", "Тип активации", "ID карточки вызова", "Краткий ссылочный ID (КСИ) карточки вызова", "ID карточки вызова в системе-112", "Язык общения с заявителем", "Состояние голосового канала между заявителем и оператором", "Количество пострадавших", "Контактный номер заявителя", "Телефонный номер абонентского терминала", "Тип вызова", "Идентификатор ТС по ISO 3779", "Тип энергоносителя ТС", "Время события или создания карточки вызова (с временной зоной)", "Широта местоположения ТС", "Долгота местоположения ТС", "Достоверность определения местоположения ТС", "Момент определения местоположения", "Количество пассажиров", "Направление движения ТС", "Оценка тяжести ДТП", "Удар спереди", "Удар слева", "Удар справа", "Удар сбоку", "Удар сзади", "Переворот", "Другой тип происшествия"];
const form = useForm({ name: props.scenario?.name ?? '', fields: props.scenario?.fields ?? defaultLabels.map(label => ({label, value: ''})) });
const deletion = useForm({});
function save() {
    if (props.scenario) form.put(route('admin.glonass.update', props.scenario.id));
    else form.post(route('admin.glonass.store'));
}
function remove() {
    if (window.confirm('Удалить шаблон ГЛОНАСС?')) deletion.delete(route('admin.glonass.destroy', props.scenario.id));
}
</script>
<template>
<Head title="Шаблон ГЛОНАСС — администрирование" />
<AdminSmsLayout :admin-login="adminLogin" section="glonass">
<Link :href="route('admin.glonass.index')" class="text-primary underline">К списку шаблонов</Link>
<h2 class="text-xl font-semibold my-4">{{ scenario ? `Шаблон ГЛОНАСС №${scenario.id}` : 'Новый шаблон ГЛОНАСС' }}</h2>
<form @submit.prevent="save" class="bg-white border border-gray-300 p-4 max-w-5xl space-y-4">
<fieldset :disabled="form.processing || deletion.processing" class="space-y-4">
<div><label for="glonass-name" class="block mb-1">Название шаблона</label><input id="glonass-name" v-model="form.name" required maxlength="255" class="w-full rounded border-gray-300 text-sm" /><p v-if="form.errors.name" role="alert" class="text-red-700">{{ form.errors.name }}</p></div>
<div v-for="(field,index) in form.fields" :key="index" class="flex gap-2 items-start">
<div class="flex-1"><label :for="`label-${index}`" class="block mb-1">Поле {{ index + 1 }}</label><input :id="`label-${index}`" v-model="field.label" required maxlength="255" class="w-full rounded border-gray-300 text-sm" /><p v-if="form.errors[`fields.${index}.label`]" role="alert" class="text-red-700">{{ form.errors[`fields.${index}.label`] }}</p></div>
<div class="flex-1"><label :for="`value-${index}`" class="block mb-1">Значение</label><textarea :id="`value-${index}`" v-model="field.value" rows="2" maxlength="2000" class="w-full rounded border-gray-300 text-sm" /><p v-if="form.errors[`fields.${index}.value`]" role="alert" class="text-red-700">{{ form.errors[`fields.${index}.value`] }}</p></div>
<button type="button" @click="form.fields.splice(index,1)" :aria-label="`Удалить поле ${index + 1}`" class="text-red-700 p-2 mt-5">✕</button>
</div>
<p v-if="form.errors.fields" role="alert" class="text-red-700">{{ form.errors.fields }}</p>
<button type="button" @click="form.fields.push({label:'',value:''})" class="text-primary">+ Добавить поле</button>
<div class="flex gap-3"><button class="bg-primary text-white rounded px-4 py-2">Сохранить</button><button v-if="scenario" type="button" @click="remove" class="text-red-700 px-4 py-2">Удалить шаблон</button></div>
</fieldset>
</form>
</AdminSmsLayout>
</template>
