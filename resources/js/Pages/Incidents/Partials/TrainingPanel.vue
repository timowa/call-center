<script setup>
import {computed, ref, onMounted} from 'vue';
import axios from 'axios';
const props = defineProps(['session','autoplay']);
const emit = defineEmits(['updated']);
const player = ref(); const text = ref(''); const pending = ref(false); const error = ref('');
let requestKey = null;
const table = computed(() => { try { return JSON.parse(props.session.content); } catch { return {}; } });
onMounted(() => { if (props.autoplay && player.value) player.value.play().catch(() => { error.value = 'Нажмите воспроизведение для учебной записи.'; }); });
const send = async () => {
 if (pending.value || !text.value.trim()) return;
 pending.value = true; error.value = ''; requestKey ??= crypto.randomUUID();
 try { const {data} = await axios.post(route('training.reply',props.session.id),{text:text.value,request_key:requestKey}); emit('updated',data); text.value=''; requestKey=null; }
 catch (e) { error.value=e.response?.data?.message || 'Ответ не подтверждён. Повторите запрос.'; }
 finally { pending.value=false; }
};
</script>
<template>
<div class="text-sm space-y-3">
<p class="text-gray-500">{{ session.name }}</p>
<audio v-if="session.kind === 'call'" ref="player" :src="session.content" controls class="w-full" />
<template v-if="session.kind === 'sms'">
 <ol class="space-y-2"><li v-for="(message,index) in session.messages" :key="index" class="rounded p-2" :class="message.direction === 'outgoing' ? 'bg-blue-100' : 'bg-gray-100'"><span class="text-xs text-gray-500">{{ message.direction === 'outgoing' ? 'Оператор' : 'Заявитель' }} · {{ message.at }}</span><p class="whitespace-pre-wrap">{{ message.text }}</p></li></ol>
 <form v-if="session.user_id === $page.props.auth.user.id" @submit.prevent="send"><textarea v-model="text" :disabled="pending" rows="3" class="w-full text-sm rounded" aria-label="Ответ на СМС" /><button :disabled="pending || !text.trim()" class="text-primary disabled:opacity-40">Отправить</button></form>
</template>
<table v-if="session.kind === 'glonass'" class="w-full"><tbody><tr v-for="(value,key) in table" :key="key" class="border-b"><th class="text-left font-normal text-gray-500 p-2">{{ key }}</th><td class="p-2 break-all">{{ value }}</td></tr></tbody></table>
<p v-if="error" role="alert" class="text-red-700">{{ error }}</p>
</div>
</template>
