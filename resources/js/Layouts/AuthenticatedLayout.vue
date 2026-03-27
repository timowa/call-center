<script setup>


import Aside from "@/Pages/Aside.vue";
import Header from "@/Pages/Header.vue";
import {inject, onMounted, onUnmounted, provide, readonly, ref, watch} from "vue";
import {usePage} from "@inertiajs/vue3";
import Noty from 'noty';

const modes = [
    {
        id: 0,
        title: 'Разблокирован',
        actionTitle: 'Разблокироваться'
    },
    {
        id: 1,
        title: 'Вызов к руководству',
        actionTitle: 'Вызов к руководству'
    },
    {
        id: 2,
        title: 'Технический перерыв',
        actionTitle: 'Техническая неисправность'
    },
    {
        id: 3,
        title: 'Обед',
        actionTitle: 'Обед'
    },
    {
        id: 4,
        title: 'Туалет',
        actionTitle: 'Туалет'
    },
    {
        id: 5,
        title: 'Обучение',
        actionTitle: 'Обучение'
    },
    {
        id: 6,
        title: 'Заблокирован',
        actionTitle: 'Заблокироваться'
    }
];

const conditions = ref({
    1: {color: 'bg-red-500', name: 'Запрос в 112'},
    2: {color: 'bg-green-500', name: 'Подключение'},
    3: {color: 'bg-indigo-500', name: 'Реагирование'},
    4: {color: 'bg-yellow-500', name: 'В работе'},
    5: {color: 'bg-grey-370', name: 'Отработана'},
    6: {color: 'bg-grey-370', name: 'Просмотр'},
});
const activeMode = ref(6);
const lastActiveMode = ref(0);
const seconds = ref(0);
let timerInterval = null;
const getActiveMode = () => {
    return modes.filter((mode) => mode.id === activeMode.value)[0];
}
const setActiveMode = function(modeId) {
    lastActiveMode.value = activeMode.value;
    activeMode.value = modeId;
    seconds.value = 0;
}

onMounted(() => {
    timerInterval = setInterval(() => {
        seconds.value++;
    }, 1000);
});

onUnmounted(() => {
    clearInterval(timerInterval);
});

provide('workMode', {
    activeMode: readonly(activeMode),
    lastActiveMode: readonly(lastActiveMode),
    setActiveMode,
    getActiveMode,
    seconds: seconds
});
provide('allWorkModes', {
    modes: readonly(modes)
})
provide('conditions', readonly(conditions.value));

const page = usePage();
watch(
    () => page.props.flash,
    (flash) => {
        if (flash && flash.message) {
            new Noty({
                text: flash.message,
                type: flash.type || 'info',
                timeout: 3000,
                layout: 'topRight',
                theme: 'metroui'
            }).show();
        }
    },
    { deep: true }
);
watch(() => page.props.errors, (errors) => {
    if (Object.keys(errors).length > 0) {
        Object.values(errors).forEach(error => {
            new Noty({
                text: error,
                type: 'error',
                timeout: 5000,
                layout: 'topRight',
                theme: 'metroui'
            }).show();
        });
    }
}, { deep: true });
</script>

<template>
    <div class="min-h-screen bg-grey-100 flex flex-col h-screen overflow-hidden">
        <Header />

        <div class="flex flex-1 overflow-hidden p-2 gap-3">
            <Aside />

            <main class="flex-1 bg-grey-200 border-gray-300 border-2 rounded-md p-3 flex flex-col overflow-hidden">

                <div v-if="$slots['top-content']" class="flex flex-col gap-3 flex-shrink-0">
                    <slot name="top-content" />
                </div>

                <div class="grid grid-rows-[auto_1fr] flex-1 overflow-hidden gap-x-6"
                     :class="[ $slots['right-panel'] ? 'grid-cols-[1fr_384px]' : 'grid-cols-1' ]">

                    <template v-if="$slots['main-tabs'] || $slots['right-panel-tabs']">
                        <div class=" min-h-[42px] flex items-end">
                            <slot name="main-tabs" />
                        </div>

                        <div v-if="$slots['right-panel']"
                             class="min-h-[42px] flex items-end">
                            <slot name="right-panel-tabs" />
                        </div>
                    </template>

                    <div class="overflow-y-auto border-r border-gray-300 relative custom-scrollbar bg-white/50">
                        <slot />
                    </div>

                    <aside v-if="$slots['right-panel']" class="overflow-y-auto bg-white relative custom-scrollbar">
                        <slot name="right-panel" />
                    </aside>
                </div>

            </main>
        </div>
    </div>
</template>
