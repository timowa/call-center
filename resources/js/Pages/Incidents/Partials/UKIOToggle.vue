<script setup>
defineProps({expanded: Boolean});
</script>

<template>
    <div class="flex justify-center pt-3">
        <button type="button" class="flex items-center gap-2 text-sm text-blue-500" :aria-expanded="expanded">
            <svg aria-hidden="true" viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2">
                <path :d="expanded ? 'm6 15 6-6 6 6' : 'm6 9 6 6 6-6'" />
            </svg>
            {{ expanded ? 'Свернуть карточку' : 'Показать полную карточку' }}
        </button>
    </div>
</template>
