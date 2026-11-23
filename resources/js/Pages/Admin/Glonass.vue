<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminSmsLayout from '@/Layouts/AdminSmsLayout.vue';
defineProps({ adminLogin: String, scenarios: Array });
</script>
<template>
<Head title="ГЛОНАСС — администрирование" />
<AdminSmsLayout :admin-login="adminLogin" section="glonass">
<div class="flex justify-between items-center mb-4"><h2 class="text-xl font-semibold">Учебные заготовки ГЛОНАСС</h2><Link :href="route('admin.glonass.create')" class="bg-primary text-white px-4 py-2 rounded">Добавить</Link></div>
<table class="w-full bg-white border border-gray-300 text-left"><thead><tr class="border-b"><th class="p-3">ID</th><th class="p-3">Название</th></tr></thead>
<tbody><tr v-for="message in scenarios" :key="message.id" @click="$inertia.visit(route('admin.glonass.edit',message.id))" class="border-b hover:bg-gray-50 cursor-pointer"><td class="p-3">{{ message.id }}</td><td class="p-3"><Link :href="route('admin.glonass.edit',message.id)" class="text-primary underline">{{ message.name }}</Link></td></tr><tr v-if="!scenarios.length"><td colspan="2" class="p-3 text-gray-500">Заготовок пока нет.</td></tr></tbody></table>
</AdminSmsLayout>
</template>
