<script setup>
import {useAttrs} from "vue";

defineProps(['class'])
</script>

<template>
<div class="bg-white px-[20px] py-[10px] rounded-md h-full" :class="class"><slot/></div>
</template>

<style scoped>

</style>
