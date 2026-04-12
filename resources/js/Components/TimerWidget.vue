<script setup>
import {ref, onMounted, onUnmounted, inject} from 'vue';
import Dropdown from "@/Components/Dropdown.vue";
const formatTime = (totalSeconds) => {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const secs = totalSeconds % 60;

    return [hours, minutes, secs]
        .map(v => v < 10 ? "0" + v : v)
        .join(":");
};



const dropdownActive = ref(false);
const {modes} = inject("allWorkModes");
const {getActiveMode, setActiveMode, seconds} = inject('workMode');
</script>

<template>
    <Dropdown align="left" width="w-64">
        <template #trigger>
            <button class="px-10 py-2 bg-primary-light text-white rounded-md flex">
                <span class="px-10 font-semibold text-lg">{{ getActiveMode().buttonTitle }}</span>
                <span class="font-mono font-bold underline">{{ formatTime(seconds) }}</span>
            </button>
        </template>

        <template #content>
            <button
                v-for="mode in modes"
                :key="mode.id"
                @click="setActiveMode(mode.id)"
                class="p-3 text-left text-grey-400 hover:bg-gray-100"
                v-html="mode.actionTitle"
            ></button>
        </template>
    </Dropdown>

</template>

<style scoped>
.slide-fade-enter-active {
    transition: all 0.2s ease-out;
}

.slide-fade-leave-active {
    transition: all 0.2s ease-in;
}

.slide-fade-enter-active,
.slide-fade-leave-active {
    transform-origin: top;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: scaleY(0);
    opacity: 0;
}
</style>
