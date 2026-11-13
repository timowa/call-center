<script setup>
import {inject, ref, computed} from "vue";
import {usePage} from "@inertiajs/vue3";
import TextInput from "@/Components/Form/TextInput.vue";

defineProps({hideHangup: Boolean});
const emit = defineEmits(['hangup']);
const page = usePage();
const phone = ref('');
const training = inject('trainingCall', ref(null));
const ringing = inject('ringingCall', null);
const activeCall = computed(() => ringing?.value || (training.value?.kind === 'call' && page.props.mode === 'create'));

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
        <template v-if="activeCall">
            <button v-for="item in [{icon:'?',title:'Справка'},{icon:'♟+',title:'Добавить участника'},{icon:'↪',title:'Перевод'},{icon:'✓',title:'Подтвердить'}]" type="button" :title="item.title + ' (имитация)'" class="rounded-full bg-blue-500 text-white w-10 h-10">{{ item.icon }}</button>
            <button type="button" title="Микрофон (имитация)" class="rounded-full bg-gray-700 text-white w-10 h-10">♩</button>
            <button type="button" title="Клавиатура (имитация)" class="rounded-full bg-blue-600 text-white w-10 h-10">▦</button>
        </template>
        <div class="col-span-4 flex gap-2">
            <button v-for="action in (hideHangup ? ['call'] : ['hangup', 'call'])" :key="action" type="button"
                    :title="action === 'hangup' ? 'Отбить' : 'Учебная имитация вызова'"
                    :aria-label="action === 'hangup' ? 'Отбить' : 'Вызов'"
                    class="grid place-items-center rounded-full text-white w-12 h-12"
                    :class="action === 'hangup' ? 'bg-red-600' : 'bg-green-500'"
                    @click="action === 'hangup' && emit('hangup')">
                <svg aria-hidden="true" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.61 21 3 13.39 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02z"/>
                </svg>
            </button>
        </div>
    </div>
</aside>
</template>

<style scoped>

</style>
