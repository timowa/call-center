<script setup>
import {computed, inject, onMounted, ref, useAttrs} from 'vue';

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
const viewMode = inject('viewMode');
const disabled = computed(() => {
    if (props.readonly === true) {
        return true;
    }
    if (viewMode.value === true) {
        return true;
    }
})

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <textarea
        class="px-2 py-1 text-sm rounded-md border-gray-300 bg-grey-150 shadow-sm focus:outline-none focus:shadow-none focus:ring-0 focus-visible:ring-0 focus:inset-shadow-none focus:inset-ring-0 focus:border-gray-300 focus:border-b-4 disabled:bg-grey-220 disabled:cursor-not-allowed"
        :class="classes"
        v-model="model"
        :placeholder="placeholder"
        :disabled="disabled"
        ref="input"
        rows= "1"
    ></textarea>
</template>
