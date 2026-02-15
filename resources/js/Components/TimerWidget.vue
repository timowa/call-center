<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const seconds = ref(0);
let timerInterval = null;

const formatTime = (totalSeconds) => {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const secs = totalSeconds % 60;

    return [hours, minutes, secs]
        .map(v => v < 10 ? "0" + v : v)
        .join(":");
};

onMounted(() => {
    timerInterval = setInterval(() => {
        seconds.value++;
    }, 1000);
});

onUnmounted(() => {
    clearInterval(timerInterval);
});
</script>

<template>
<div class="px-10 py-2 w-auto bg-primary-light text-white rounded-md flex">
    <span class="px-10 font-semibold text-lg flex flex-col justify-center">Заблокирован</span>
    <div class="font-mono font-bold underline flex flex-col justify-center">
        {{ formatTime(seconds) }}
    </div>
</div>
</template>

<style scoped>

</style>
