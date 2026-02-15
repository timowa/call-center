<script setup>
import {computed} from "vue";

const props = defineProps({
    title: String,
    id: String,
    active: Boolean,
})
const classes = computed(() => {
    return props.active
        ? 'text-primary border-primary'
        : 'text-grey-300 border-grey-300';
});
</script>

<template>
    <button
        :key="title"
        class="uppercase pb-2 border-b border-solid tracking-wider"
        :class="classes" >
        <span>{{title}}</span>
    </button>
</template>

<style scoped>

</style>
