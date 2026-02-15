<script setup>
const props = defineProps(['label', 'id'])
const emit = defineEmits(['remove']);

const removeClick = () => {
    emit('remove');
};
</script>

<template>
<div class="flex text-grey-350 items-center bg-white p-2 rounded-md">
    <span>{{label}}</span>
    <button class="ml-2" @click.prevent="removeClick">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
    </button>
</div>
</template>

<style scoped>

</style>
