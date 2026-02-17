<script setup>
import {computed, onMounted, ref} from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);
const props = defineProps(['placeholder', 'wFull']);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

const classes = computed(() => ({
    'w-full': props.wFull,
    'w-3/5': !props.wFull
}));

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="p-0 rounded-md border-gray-300 bg-grey-150 shadow-sm focus:outline-none focus:shadow-none focus:ring-0 focus-visible:ring-0 focus:inset-shadow-none focus:inset-ring-0 focus:border-gray-300 focus:border-b-4"
        :class="classes"
        v-model="model"
        :placeholder="props.placeholder"
        ref="input"
    />
</template>
