<script setup>
import TextInput from "@/Components/Form/TextInput.vue";
import Select from "@/Components/Form/Select.vue";
import Checkbox from "@/Components/Form/Checkbox.vue";
import Radio from "@/Components/Form/Radio.vue";
import InputLabel from "@/Components/Form/InputLabel.vue";

const props = defineProps({
    label: String,
    type: {
        type: String,
        default: 'text'
    },
    labelFloat: {
        type: String,
        default: 'left'
    },
    name: {
        required: true,
        type: String
    },
    inputClass: String,
    disabled: {
        type: Boolean,
        default: false
    }
});
const inputs = {
    text: TextInput,
    select: Select,
    checkbox: Checkbox,
    radio: Radio,
}

const isRightLabelFloat = props.type === 'checkbox' || props.type === 'radio';
let inputClasses = [];

if (props.type === 'text' || props.type === 'select') {
    inputClasses.push('w-3/5')
}
if (props.inputClass) {
    inputClasses.push(props.inputClass)
}
inputClasses = inputClasses.join(' ');

</script>

<template>
    <div class="flex items-center " :class="isRightLabelFloat ? 'flex-row-reverse justify-end' : 'justify-between'">
        <InputLabel :value="label" :disabled="disabled"/>
        <component :is="inputs[type]" :name="name" :class="inputClasses" :disabled="disabled"></component>
    </div>

</template>

<style scoped>

</style>
