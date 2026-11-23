<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ adminLogin: String, section: { type: String, default: 'sms' } });
</script>
<template>
<div class="min-h-screen bg-gray-100 text-gray-800 text-sm">
<header class="bg-primary text-white flex items-center justify-between gap-4 px-5 py-3"><h1 class="font-semibold text-lg">Администрирование справочников</h1><div class="flex gap-4"><span>{{ adminLogin }}</span><Link :href="route('admin.logout')" method="post" as="button" class="underline">Выйти</Link></div></header>
<div class="flex min-h-[calc(100vh-52px)]">
<nav aria-label="Разделы" class="flex flex-col w-28 shrink-0 bg-white border-r border-gray-300">
<Link v-for="tab in [{key:'sms',label:'СМС'}, {key:'glonass',label:'ГЛОНАСС'}]" :key="tab.key" :href="route(`admin.${tab.key}.index`)" :aria-current="section === tab.key ? 'page' : undefined" class="px-3 py-3 border-l-2 font-semibold text-left" :class="section === tab.key ? 'border-primary text-primary' : 'border-transparent'">{{ tab.label }}</Link>
<Link v-for="tab in [{key:'ukio',label:'УКИО',directory:'incident-types'}, {key:'01',label:'01',directory:'report-types'}, {key:'gas',label:'Газ',directory:'gas-utilities'}]" :key="tab.key" :href="route('admin.directories.index', {directory:tab.directory,group:tab.key})" class="text-left px-3 py-3 font-semibold border-l-2 border-transparent">{{ tab.label }}</Link>
</nav>
<aside aria-label="Справочники" class="w-64 shrink-0 bg-white border-r border-gray-300"></aside>
<main class="p-5 flex-1 min-w-0"><slot /></main>
</div>
</div>
</template>
