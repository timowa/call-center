<script setup>
import Modal from "@/Components/Modal.vue";
import LinkButton from "@/Components/LinkButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import FormGroup from "@/Components/Form/FormGroup.vue";
import FormField from "@/Components/Form/FormField.vue";
import {usePage} from "@inertiajs/vue3";
const page = usePage();
const directories = page.props.directories;
const dictionaries = page.props.dictionaries;
const props = defineProps({
    show: Boolean,
    form: {
        type: Object,
        required: true
    }
});
const emit = defineEmits(['close', 'submit']);

</script>

<template>
<Modal :show="show" @close="emit('close')">
<template #header>Фильтр</template>
    <form id="filter-form">
        <div>
            <FormGroup title="Тип происшествия">
                    <FormField label="Служба" name="sl" :col-span="1" type="select" :options="directories.services" v-model="form.service_id"></FormField>
                    <FormField label="Тип вызова" name="ad" :col-span="1" type="select" :options="directories.callTypes" v-model="form.call_type_id"></FormField>
                    <div class="col-span-2 flex gap-4">
                        <FormField name="d" label="Учебная" type="checkbox" v-model:checked="form.is_training"/>
                        <FormField name="s" label="Важная" type="checkbox" v-model:checked="form.is_important"/>
                        <FormField name="q" label="Экстренный тип вызова" type="checkbox"/>
                        <FormField name="a" label="Угроза ЧС" type="checkbox" v-model:checked="form.emergency_threat"/>
                    </div>
            </FormGroup>
            <FormGroup title="Период">
                <div class="col-span-2 flex gap-4">
                    <FormField name="e" type="radio"  label="24 часа" v-model="form.period_day"/>
                    <FormField name="e" type="radio"  label="За период" v-model="form.period_custom"/>
                </div>
            </FormGroup>
            <FormGroup title="Состояние карточки" :cols="4">
                <FormField v-for="condition in dictionaries.conditions"
                           :label="condition.name"
                           :key="condition.id"
                           :value="condition.id"
                           type="checkbox"
                           v-model:checked="form.conditions"/>
            </FormGroup>
            <FormGroup title="Место происшествия">
                <FormField name="9"  label="Район" :col-span="1" type="select" :options="directories.areas" v-model="form.area_id"/>
                <FormField name="10" label="Район города" :col-span="1" type="select"/>
                <FormField name="11" label="Округ" :col-span="1" type="select" :options="directories.districts" v-model="form.district_id"/>
            </FormGroup>
            <FormGroup title="Источник">
                <FormField name="12" type="select" :col-span="1" label="Источник" :options="dictionaries.sources" v-model="form.source_id"/>
                <FormField name="13" :col-span="1" label="Номер звонящего" :text-align="'right'" v-model="form.incoming_number"/>
                <FormField name="14" label="Создатель" :col-span="1" v-model="form.creator"/>
            </FormGroup>
            <FormGroup title="Информация о заявителе">
                <FormField name="15" :col-span="1" label="Фамилия" v-model="form.applicant_lastname"/>
                <FormField name="16" :col-span="1" label="Имя" :text-align="'right'" v-model="form.applicant_firstname"/>
                <FormField name="17" :col-span="1" label="Отчество" v-model="form.applicant_surname"/>
                <FormField name="18" :col-span="1" label="Телефон" :text-align="'right'" v-model="form.applicant_phone"/>
            </FormGroup>
            <FormGroup title="Исключить">
                <FormField label="Созданные «КСОМБ»" name="19" type="checkbox"/>
            </FormGroup>
        </div>
    </form>
    <template #footer>
        <div class="flex justify-between">
            <LinkButton @click="form.reset()">Сбросить</LinkButton>
            <div>
                <SecondaryButton class="mr-4" @click="emit('close')"> Закрыть</SecondaryButton>
                <PrimaryButton @click="emit('submit')">Сохранить</PrimaryButton>
            </div>
        </div>
    </template>
</Modal>
</template>

<style scoped>

</style>
