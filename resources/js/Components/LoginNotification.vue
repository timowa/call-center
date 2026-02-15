<script setup>
import { computed } from 'vue';

const props = defineProps({
    message: String,
    type: {
        type: String,
        default: 'success'
    }
});

const notificationClasses = computed(() => {
    return props.type === 'error'
        ? 'bg-red-600 text-white'
        : 'bg-primary-light text-white';
});
</script>

<template>
<div>
    <div v-if="message" :class="[notificationClasses, 'flex items-center p-4 rounded-md shadow-md mb-4 transition-all duration-300']">
        <div class="mr-3">
            <svg v-if="type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>

        <span class="text-lg text-center font-semibold w-full">{{ message }}</span>
    </div>
</div>
</template>

<style scoped>

</style>
