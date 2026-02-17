<script setup>
import {computed} from "vue";

const props = defineProps(['active']);
const classes = computed(() => ({
    'bg-white text-black' : props.active,
    'text-grey-350': !props.active
}))

</script>

<template>
<button class="px-4 py-2 rounded-t-md text-sm" :class="classes"><slot/></button>
</template>

<style scoped>

</style>
