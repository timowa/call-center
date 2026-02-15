<script setup>
import Modal from "@/Components/Modal.vue";
import LinkButton from "@/Components/LinkButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import FormGroup from "@/Components/Form/FormGroup.vue";
import FormField from "@/Components/Form/FormField.vue";

const props = defineProps({
    show: Boolean
});
const emit = defineEmits(['close', 'submit']);

</script>

<template>
<Modal :show="show" @close="emit('close')">
<template #header>Фильтр</template>
    <div>
        <FormGroup :title="'Тип происшествия'">
                <FormField :label="'Служба'" name="sl"></FormField>
                <FormField :label="'Тип вызова'" name="ad"></FormField>
                <div class="col-span-2 flex gap-4">
                    <FormField   name="d" :label="'Учебная'" type="checkbox"/>
                    <FormField   name="s" :label="'Важная'" type="checkbox"/>
                    <FormField   name="q" :label="'Экстренный тип вызова'" type="checkbox"/>
                    <FormField   name="a" :label="'Угроза ЧС'" type="checkbox"/>
                </div>
        </FormGroup>
        <FormGroup :title="'Период'">
            <div class="col-span-2 flex gap-4">
                <FormField  name="e" :type="'radio'"  :label="'24 часа'"/>
                <FormField :disabled="true"  name="e" :type="'radio'"  :label="'За период'"/>
            </div>
        </FormGroup>
        <FormGroup :title="'Состояние карточки'" :cols="4">
            <FormField name="1" :label="'Запрос 112'" type="checkbox"/>
            <FormField name="1" :label="'Подключение'" type="checkbox"/>
            <FormField name="1" :label="'Реагирование'" type="checkbox"/>
            <FormField name="1" :label="'В работе'" type="checkbox"/>
            <FormField name="1" :label="'Отработана'" type="checkbox"/>
            <FormField name="1" :label="'Просмотр'" type="checkbox"/>
            <FormField name="1" :label="'Отказ'" type="checkbox"/>
            <FormField name="1" :label="'Не обработана'" type="checkbox"/>
        </FormGroup>
    </div>
    <template #footer>
        <div class="flex justify-between">
            <LinkButton
            :title="'Сбросить'"
            />
            <div>
                <SecondaryButton class="mr-4"> Закрыть</SecondaryButton>
                <PrimaryButton>Сохранить</PrimaryButton>
            </div>
        </div>
    </template>
</Modal>
</template>

<style scoped>

</style>
