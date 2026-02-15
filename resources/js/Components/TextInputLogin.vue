<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-none border-0 border-b-2 border-grey-400 focus:outline-none focus:shadow-none focus:ring-0 focus:inset-shadow-none focus:inset-ring-0 focus:border-grey-400 bg-[#efebe9]"
        v-model="model"
        ref="input"
    />
</template>
