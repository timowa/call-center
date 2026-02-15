<script setup>

import TextInput from "@/Components/Form/TextInput.vue";
</script>

<template>
<aside class="w-1/5 bg-white pb-20 flex flex-col">
<div class="bg-primary rounded-t-lg py-2 px-6">
    <span class="text-md text-white">Управление активностями</span>
</div>
    <div class="text-xl text-center py-4">
        <span>Заблокирован</span>
    </div>
    <div class="px-2">
        <TextInput model-value="" :placeholder="'Введите номер'"></TextInput>
    </div>
</aside>
</template>

<style scoped>

</style>
