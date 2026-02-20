<script setup>
import {ref, onMounted, onUnmounted, inject} from 'vue';
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
    <div class="relative">
        <button class="px-10 py-2 w-auto bg-primary-light text-white rounded-md flex"
        @click="dropdownActive = !dropdownActive"
        >
            <span class="px-10 font-semibold text-lg flex flex-col justify-center">{{ getActiveMode().title }}</span>
            <span class="font-mono font-bold underline flex flex-col justify-center">
        {{ formatTime(seconds) }}
    </span>
        </button>
        <Transition name="slide-fade">
        <div class="bg-white absolute flex flex-col py-4 will-change-transform"
             v-show="dropdownActive">
            <button v-for="mode in modes"
                    @click="setActiveMode(mode.id); dropdownActive = false"
                    v-html="mode.actionTitle"
                    class="p-3 text-left text-grey-400 hover:bg-gray-100"
            ></button>
        </div>
        </Transition>
    </div>

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
