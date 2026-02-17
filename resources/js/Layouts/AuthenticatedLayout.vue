<script setup>


import Aside from "@/Pages/Aside.vue";
import Header from "@/Pages/Header.vue";
import {provide, readonly, ref} from "vue";

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
const setActiveMode = function(modeId) {
    lastActiveMode.value = activeMode.value;
    activeMode.value = modeId;
}
const getActiveMode = () => {
    return modes.filter((mode) => mode.id === activeMode.value)[0];
}

provide('workMode', {
    activeMode: readonly(activeMode),
    lastActiveMode: readonly(lastActiveMode),
    setActiveMode,
    getActiveMode
});
provide('allWorkModes', {
    modes: readonly(modes)
})
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
