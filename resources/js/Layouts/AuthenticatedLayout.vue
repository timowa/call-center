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

const page = usePage();
watch(
    () => page.props.flash,
    (flash) => {
        console.log(flash)
        if (flash && flash.message) {
            new Noty({
                text: flash.message,
                type: flash.type || 'info', // success, error, warning, info
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
        // Проходимся по всем ошибкам и выводим каждую в отдельном Noty
        Object.values(errors).forEach(error => {
            new Noty({
                text: error,
                type: 'error',
                timeout: 5000, // Ошибки лучше показывать дольше
                layout: 'topRight',
                theme: 'metroui'
            }).show();
        });
    }
}, { deep: true });
</script>

<template>
    <div>
        <div class="min-h-screen bg-grey-100 flex flex-col">

            <Header></Header>
            <div class="flex flex-1 overflow-hidden p-2 bg-grey-100 gap-3">
                <Aside></Aside>
                <main class="flex-1 w-4/5">
                    <slot />
                </main>
            </div>

        </div>
    </div>
</template>
