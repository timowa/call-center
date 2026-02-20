<script setup>
import {computed, onMounted, ref, useAttrs} from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);
const props = defineProps(['placeholder', 'readonly']);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});
const classes = computed(() => ({
    'bg-gray-200 cursor-not-allowed opacity-70': props.readonly
}));


defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <textarea
        class="p-0 rounded-md border-gray-300 bg-grey-150 shadow-sm focus:outline-none focus:shadow-none focus:ring-0 focus-visible:ring-0 focus:inset-shadow-none focus:inset-ring-0 focus:border-gray-300 focus:border-b-4"
        :class="classes"
        v-model="model"
        :placeholder="placeholder"
        :readonly="readonly"
        ref="input"
        rows= "1"
    ></textarea>
</template>
