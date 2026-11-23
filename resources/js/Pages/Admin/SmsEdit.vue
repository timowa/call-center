<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminSmsLayout from '@/Layouts/AdminSmsLayout.vue';
const props = defineProps({ adminLogin: String, scenario: Object });
const form = useForm({ name: props.scenario?.name ?? '', messages: props.scenario?.messages ?? [''] });
const deletion = useForm({});
function save() {
    if (props.scenario) form.put(route('admin.sms.update',props.scenario.id));
    else form.post(route('admin.sms.store'));
}
</script>
<template>
<Head title="Заготовка СМС — администрирование" />
<AdminSmsLayout :admin-login="adminLogin">
<Link :href="route('admin.sms.index')" class="text-primary underline">К списку заготовок</Link>
<h2 class="text-xl font-semibold my-4">{{ scenario ? `Учебный сценарий СМС №${scenario.id}` : 'Новая заготовка СМС' }}</h2>
<form @submit.prevent="save" class="bg-white border border-gray-300 p-4 max-w-4xl space-y-4">
<div><label for="sms-name" class="block mb-1">Тема обращения</label><input id="sms-name" v-model="form.name" required maxlength="255" class="w-full rounded border-gray-300 text-sm" /><p class="text-red-700" v-if="form.errors.name">{{ form.errors.name }}</p></div>
<fieldset :disabled="form.processing"><legend class="mb-2 font-semibold">Сообщения</legend>
<ol class="space-y-2"><li v-for="(_,index) in form.messages" :key="index" class="flex gap-2 items-start"><div class="flex-1"><label :for="`sms-${index}`" class="sr-only">СМС {{ index + 1 }}</label><input :id="`sms-${index}`" v-model="form.messages[index]" required maxlength="2000" class="w-full rounded border-gray-300 text-sm" /><p v-if="form.errors[`messages.${index}`]" class="text-red-700">{{ form.errors[`messages.${index}`] }}</p></div><button type="button" @click="form.messages.splice(index,1)" :aria-label="`Удалить СМС ${index + 1}`" title="Удалить СМС" class="text-red-700 p-2">✕</button></li></ol>
<p v-if="form.errors.messages" class="text-red-700">{{ form.errors.messages }}</p>
<button type="button" @click="form.messages.push('')" class="text-primary mt-3">+ Добавить СМС</button>
</fieldset>
<div class="flex gap-3"><button :disabled="form.processing || deletion.processing" class="bg-primary text-white rounded px-4 py-2 disabled:opacity-50">Сохранить</button><button v-if="scenario" type="button" @click="deletion.delete(route('admin.sms.destroy',scenario.id))" :disabled="deletion.processing || form.processing" class="text-red-700 px-4 py-2">Удалить заготовку</button></div>
</form>
</AdminSmsLayout>
</template>
