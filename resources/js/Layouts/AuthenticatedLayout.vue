<script setup>


import Aside from "@/Pages/Aside.vue";
import Header from "@/Pages/Header.vue";
import {inject, onMounted, onUnmounted, provide, readonly, ref, watch} from "vue";
import {usePage} from "@inertiajs/vue3";
import Noty from 'noty';

defineProps({hideHangup: Boolean});
const emit = defineEmits(['hangup']);

const modes = [
    {
        id: 0,
        workTitle: 'В работе',
        activityTitle: 'Готов',
        actionTitle: 'Разблокироваться'
    },
    {
        id: 1,
        workTitle: 'Вызов к руководству',
        activityTitle: 'Заблокирован',
        actionTitle: 'Вызов к руководству'
    },
    {
        id: 2,
        workTitle: 'Технический перерыв',
        activityTitle: 'Заблокирован',
        actionTitle: 'Техническая неисправность'
    },
    {
        id: 3,
        workTitle: 'Обед',
        activityTitle: 'Заблокирован',
        actionTitle: 'Обед'
    },
    {
        id: 4,
        workTitle: 'Туалет',
        activityTitle: 'Заблокирован',
        actionTitle: 'Туалет'
    },
    {
        id: 5,
        workTitle: 'Обучение',
        activityTitle: 'Заблокирован',
        actionTitle: 'Обучение'
    },
    {
        id: 6,
        workTitle: 'Заблокирован',
        activityTitle: 'Заблокирован',
        actionTitle: 'Заблокироваться'
    }
];

const page = usePage();
const storageKey = 'operator-work-mode';
const sessionKey = `${page.props.auth.user.id}:${page.props.auth.work_session ?? 'session'}`;
let saved;
try { saved = JSON.parse(sessionStorage.getItem(storageKey)); } catch {}
if (saved?.session !== sessionKey || !modes.some(mode => mode.id === saved?.mode)) saved = null;
const activeMode = ref(saved?.mode ?? 0);
let startedAt = saved?.startedAt ?? Date.now();
const persistMode = () => {
    try { sessionStorage.setItem(storageKey, JSON.stringify({session: sessionKey, mode: activeMode.value, startedAt})); } catch {}
};
persistMode();
const lastActiveMode = ref(0);
const seconds = ref(Math.max(0, Math.floor((Date.now() - startedAt) / 1000)));
let timerInterval = null;
const getActiveMode = () => {
    return modes.filter((mode) => mode.id === activeMode.value)[0];
}
const setActiveMode = function(modeId) {
    lastActiveMode.value = activeMode.value;
    activeMode.value = modeId;
    startedAt = Date.now();
    seconds.value = 0;
    persistMode();
}

onMounted(() => {
    timerInterval = setInterval(() => {
        seconds.value = Math.max(0, Math.floor((Date.now() - startedAt) / 1000));
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
            <Aside :hide-hangup="hideHangup" @hangup="emit('hangup')" />

            <main class="flex-1 bg-grey-200 border-gray-300 border-2 rounded-md p-3 flex flex-col overflow-hidden">

                <div class="flex flex-col gap-3 flex-shrink-0">
                    <div class="flex justify-between">
                        <slot name="top-content" />
                        <div>
                            <span class="text-grey-350 ">{{$page.props.auth.user.name}}</span>
                        </div>
                    </div>
                    <div v-if="$slots['top-content-2']">
                         <slot name="top-content-2"/>
                    </div>
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
