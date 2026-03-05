<script setup>
import TextInput from "@/Components/Form/TextInput.vue";
import Select from "@/Components/Form/Select.vue";
import Checkbox from "@/Components/Form/Checkbox.vue";
import Radio from "@/Components/Form/Radio.vue";
import InputLabel from "@/Components/Form/InputLabel.vue";
import {computed, inject} from "vue";
import Textarea from "@/Components/Form/Textarea.vue";
import Table from "@/Components/Form/Table.vue";
import {usePage} from "@inertiajs/vue3";

const page = usePage();
const user = computed(() => page.props.auth.user);
const {isCreator} = inject('directories')
const props = defineProps({
    type: {
        type: String,
        default: 'text'
    },
    gridCol: {
        type: Number,
        default: 2
    },
    colSpan: {
        type: Number,
        default: 2
    },
    vertical: {
        type: Boolean,
        default: false
    }
});
const inputs = {
    text: TextInput,
    select: Select,
    checkbox: Checkbox,
    radio: Radio,
    textarea: Textarea,
    table: Table
}
const gridCols = {
    2: 'grid-cols-2',
    3: 'grid-cols-3',
    4: 'grid-cols-4',
    6: 'grid-cols-6',
}
const colEnd = {
    2: '',
    3: 'col-end-4',
    4: 'col-end-5',
    5: 'col-end-6',
    6: 'col-end-7',
}
const colSpans = {
    1: '',
    2: 'col-span-2',
    3: 'col-span-3',
    4: 'col-span-4',
    5: 'col-span-5',
    6: 'col-span-6',
}
const isCheckbox = props.type === 'checkbox' || props.type === 'radio';
const isTable = props.type === 'table';
const inputClasses = computed(()=>{
    if (isCheckbox || props.vertical) return {};
    return {
        'col-start-2': true,
        [colEnd[props.colSpan]]: true
    }
});
const display = computed(()=>{
    if (isCheckbox) return {
        'flex': true
    };

    return {
        'grid gap-6': !props.vertical,
        'grid grid-rows-2': props.vertical && !isTable,
        [gridCols[props.gridCol]]: !props.vertical && props.gridCol > 1,
        [colSpans[props.colSpan]]: props.colSpan > 0,
        'items-center': !props.vertical
    }
});


</script>

<template>
    <div class="align-center text-13 " :class="display">
        <InputLabel v-bind="$attrs" v-if="$attrs.label && !isCheckbox && !isTable"/>
        <component v-bind="$attrs" :is="inputs[type]" class="flex-shrink-0" :class="inputClasses"></component>
        <InputLabel v-bind="$attrs" v-if="$attrs.label && isCheckbox && !isTable"/>

    </div>

</template>

<style scoped>

</style>
