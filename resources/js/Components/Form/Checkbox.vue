<script setup>
import {computed, inject} from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        required: true,
    },
    value: {
        default: null,
    },
    disabled: Boolean
});

const proxyChecked = computed({
    get() {
        if (Array.isArray(props.checked)) {
            return props.checked.some(item =>
                (item.id !== undefined ? item.id : item) === props.value
            );
        }
        return props.checked;
    },

    set(val) {
        if (Array.isArray(props.checked)) {
            const newList = [...props.checked];
            if (val) {
                newList.push(props.value);
            } else {
                const index = newList.findIndex(item =>
                    (item.id !== undefined ? item.id : item) === props.value
                );
                if (index !== -1) newList.splice(index, 1);
            }
            emit('update:checked', newList);
        } else {
            emit('update:checked', val);
        }
    },
});
const viewMode = inject('viewMode');
const disabled = computed(() => {
    if (props.disabled === true) {
        return true;
    }
    if (viewMode.value === true) {
        return true;
    }
    return false;
})
</script>

<template>
    <input
        type="checkbox"
        :value="value"
        v-model="proxyChecked"
        :disabled="disabled === true"
        class="mr-2 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 disabled:bg-grey-220 disabled:cursor-not-allowed"
    />
</template>
