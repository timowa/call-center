<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    align: { default: 'left' },
    width: { default: 'auto' }
});

const dropdownActive = ref(false);

const closeOnOutsideClick = (e) => {
    if (dropdownActive.value && !e.target.closest('.base-dropdown')) {
        dropdownActive.value = false;
    }
};

onMounted(() => window.addEventListener('click', closeOnOutsideClick));
onUnmounted(() => window.removeEventListener('click', closeOnOutsideClick));
</script>

<template>
    <div class="relative base-dropdown">
        <div @click="dropdownActive = !dropdownActive">
            <slot name="trigger" class="cursor-pointer"/>
        </div>

        <Transition name="slide-fade">
            <div
                v-show="dropdownActive"
                class="bg-white absolute z-50 flex flex-col py-4 shadow-lg border rounded-md"
                :class="[
                    align === 'left' ? 'left-0' : 'right-0',
                    width
                ]"
                @click="dropdownActive = false"
            >
                <slot name="content" />
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.slide-fade-enter-active, .slide-fade-leave-active {
    transition: all 0.2s ease-out;
}
.slide-fade-enter-from, .slide-fade-leave-to {
    transform: translateY(-10px);
    opacity: 0;
}
</style>
