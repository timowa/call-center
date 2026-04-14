<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    align: { default: 'left' },
    width: { default: 'auto' }
});

const dropdownActive = ref(false);
const triggerRef = ref(null); // Ссылка на кнопку
const dropdownStyles = ref({ top: '0px', left: '0px' }); // Координаты

const calculatePosition = async () => {
    if (!dropdownActive.value || !triggerRef.value) return;

    await nextTick();
    if (!triggerRef.value) return;

    const rect = triggerRef.value.getBoundingClientRect();

    await nextTick();

    const scrollY = window.scrollY;
    const scrollX = window.scrollX;

    // Вычисляем позицию относительно всего документа
    dropdownStyles.value = {
        top: `${rect.bottom + scrollY}px`,
        left: props.align === 'left'
            ? `${rect.left + scrollX - 50}px`
            : `${rect.right + scrollX}px`,
        transform: props.align === 'right' ? 'translateX(-100%)' : 'none',
        position: 'absolute',
        width: props.width === 'auto' ? 'max-content' : props.width
    };
};

const toggle = async () => {
    if (!triggerRef.value) return;
    if (!dropdownActive.value) {
        const rect = triggerRef.value.getBoundingClientRect();
        const scrollY = window.scrollY;
        const scrollX = window.scrollX;

        dropdownStyles.value = {
            top: `${rect.bottom + scrollY}px`,
            left: props.align === 'left'
                ? `${rect.left + scrollX}px`
                : `${rect.right + scrollX}px`,
            transform: props.align === 'right' ? 'translateX(-100%)' : 'none',
            position: 'absolute',
            width: props.width === 'auto' ? 'max-content' : props.width,
            visibility: 'hidden' // Временно скрываем, чтобы избежать скачка
        };

        dropdownActive.value = true;

        await nextTick();
        dropdownStyles.value.visibility = 'visible';
    } else {
        dropdownActive.value = false;
    }
};

const closeOnOutsideClick = (e) => {
    if (dropdownActive.value && triggerRef.value && !triggerRef.value.contains(e.target)) {
        dropdownActive.value = false;
    }
};

onMounted(() => {
    window.addEventListener('click', closeOnOutsideClick);
    window.addEventListener('scroll', calculatePosition, true); // Пересчет при скролле
    window.addEventListener('resize', calculatePosition);
});

onUnmounted(() => {
    window.removeEventListener('click', closeOnOutsideClick);
    window.removeEventListener('scroll', calculatePosition);
    window.removeEventListener('resize', calculatePosition);
});
</script>

<template>
    <div class="inline-block base-dropdown">
        <div ref="triggerRef" @click="toggle" class="cursor-pointer">
            <slot name="trigger" />
        </div>

        <Teleport to="body">
            <Transition name="slide-fade">
                <div
                    v-if="dropdownActive"
                    class="bg-white z-[9999] flex flex-col py-2 shadow-xl border rounded-md"
                    :style="dropdownStyles"
                    @click="dropdownActive = false"
                >
                    <slot name="content" />
                </div>
            </Transition>
        </Teleport>
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
