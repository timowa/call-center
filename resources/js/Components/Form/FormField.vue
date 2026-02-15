<script setup>
import TextInput from "@/Components/Form/TextInput.vue";
import Select from "@/Components/Form/Select.vue";
import Checkbox from "@/Components/Form/Checkbox.vue";
import Radio from "@/Components/Form/Radio.vue";
import InputLabel from "@/Components/Form/InputLabel.vue";
import {computed} from "vue";

const props = defineProps({
    type: {
        type: String,
        default: 'text'
    }
});
const inputs = {
    text: TextInput,
    select: Select,
    checkbox: Checkbox,
    radio: Radio,
}

const isRightLabelFloat = props.type === 'checkbox' || props.type === 'radio';
const floatClasses = computed(()=>({
    'flex-row-reverse justify-end whitespace-nowrap': isRightLabelFloat,
    'justify-between': !isRightLabelFloat,

}));


</script>

<template>
    <div class=" flex items-center text-13" :class="floatClasses">
        <InputLabel v-bind="$attrs" />
        <component v-bind="$attrs" :is="inputs[type]" class="flex-shrink-0"></component>
    </div>

</template>

<style scoped>

</style>
