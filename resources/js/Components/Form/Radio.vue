<script setup>
import {computed, inject, ref} from 'vue';

const emit = defineEmits(['update:checked']);
const isUkioForm = inject('isUkioForm', ref(false));
const {isCreator} = inject('directories', { isCreator: false })
const props = defineProps({
    checked: {
        type: [Array, Boolean],
        required: true,
    },
    value: {
        default: null,
    },
    disabled: Boolean,
    allowEditIfNotCreator: Boolean
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});
const bgColor = props.disabled ? 'bg-grey-200' : 'bg-white'
const viewMode = inject('viewMode');
const disabled = computed(() => {
    if (props.disabled === true) {
        return true;
    }
    if (viewMode.value === true) {
        return true;
    }
    if (isUkioForm.value && !isCreator) {
        if (props.allowEditIfNotCreator) {
            return false;
        }
        return true;
    }
})
</script>

<template>
    <input
        type="radio"
        :value="value"
        :disabled="disabled"
        v-model="proxyChecked"
        class="rounded-full border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mr-2 disabled:bg-grey-220 disabled:cursor-not-allowed"
        :class="bgColor"
    />
</template>
