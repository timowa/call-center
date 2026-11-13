<script setup>
import {inject, ref, computed} from "vue";

import axios from 'axios';
import {usePage} from '@inertiajs/vue3';
const page = usePage();
const phone = ref('');
const training = inject('trainingCall', ref(null));
const ringing = inject('ringingCall', null);
const activeCall = computed(() => ringing?.active.value || (training.value?.kind === 'call' && page.props.mode === 'create'));
const pending = ref(false); const error = ref('');
const hangup = async () => {
 if (ringing?.active.value) { ringing.cancel(); return; }
 if (pending.value) return; pending.value=true;
 try { const {data}=await axios.post(route('training.hangup',training.value.id)); training.value=data; }
 catch { error.value='Не удалось завершить учебный звонок.'; }
 finally { pending.value=false; }
};
import TextInput from "@/Components/Form/TextInput.vue";

const {getActiveMode} = inject('workMode');
</script>

<template>
<aside class="w-1/10 bg-white pb-20 flex flex-col">
<div class="bg-primary rounded-t-lg py-2 px-6">
    <span class="text-md text-white">Управление активностями</span>
</div>
    <div class="text-xl text-center py-4">
        <span>{{ getActiveMode().activityTitle }}</span>
    </div>
    <div class="px-2">
        <TextInput :placeholder="'Введите номер'" class="w-full" v-model="phone"></TextInput>
    </div>
    <div class="mt-auto px-2 pt-6 grid grid-cols-4 justify-items-center gap-2 max-w-[224px] mx-auto">
        <button v-if="!activeCall" type="button" title="Учебная имитация вызова" aria-label="Вызов" class="col-span-4 rounded-full bg-green-500 text-white w-12 h-12 text-xl">☎</button>
        <template v-else>
            <button v-for="item in [{icon:'?',title:'Справка'},{icon:'♟+',title:'Добавить участника'},{icon:'↪',title:'Перевод'},{icon:'✓',title:'Подтвердить'}]" type="button" :title="item.title + ' (имитация)'" class="rounded-full bg-blue-500 text-white w-10 h-10">{{ item.icon }}</button>
            <button v-if="!training?.hung_up" type="button" @click="hangup" :disabled="pending" title="Отбить" aria-label="Отбить" class="rounded-full bg-red-600 text-white w-10 h-10">☎</button>
            <button type="button" title="Микрофон (имитация)" class="rounded-full bg-gray-700 text-white w-10 h-10">♩</button>
            <button type="button" title="Клавиатура (имитация)" class="rounded-full bg-blue-600 text-white w-10 h-10">▦</button>
        </template>
        <p v-if="error" role="alert" class="text-red-700 text-xs">{{ error }}</p>
    </div>
</aside>
</template>

<style scoped>

</style>
