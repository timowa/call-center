<script setup>
defineProps(['class'])
</script>

<template>
<div class="bg-white px-[20px] py-[10px] rounded-md" :class="class"><slot/></div>
</template>

<style scoped>

</style>
