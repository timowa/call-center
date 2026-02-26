<script setup>

import FormField from "@/Components/Form/FormField.vue";
import FormGroup from "@/Components/Form/FormGroup.vue";
import InputLabel from "@/Components/Form/InputLabel.vue";
import TextInput from "@/Components/Form/TextInput.vue";
import {ref, watch} from "vue";
import Block from "@/Components/Block.vue";
const props = defineProps(['form', 'incidentTypes', 'services', 'areas', 'districts', 'callTypes']);
const checkedCallTypeServiceId = ref(null);
watch(
    () => props.form.call_type,
    (newCallType) => {
        if (!newCallType) return;

        const callTypeData = props.callTypes.filter((callType) => callType.id === newCallType)[0];

        if (callTypeData.service_id === null) return;

        const index = props.form.services.indexOf(callTypeData.service_id);
        checkedCallTypeServiceId.value = callTypeData.service_id;
        if (index !== -1) {
            props.form.services.splice(index, 1);
        }
    }
);
</script>

<template>
    <Block>
        <FormGroup title="Информация" :cols="6">
            <FormField label="№ карточки" v-model="form.id" :readonly="true" />
            <FormField label="Время обработки" v-model="form.proc_time" :readonly="true"  :text-align="'right'"/>
            <FormField label="Входящий номер" v-model="form.phone" :readonly="true"  :text-align="'right'" />
            <FormField label="Дата обращения" v-model="form.created_at.date" :readonly="true"  />
            <FormField label="Время регистрации" v-model="form.created_at.time" :readonly="true"  :text-align="'right'" />
            <FormField label="Создатель" v-model="form.creator" :readonly="true"  :text-align="'right'" />
            <FormField label="Тип происшествия" type="select" v-model="form.incident_type" :col-span="4" :grid-col="4" :options="incidentTypes"/>
            <FormField label="Источник" v-model="form.source"  :text-align="'right'"/>
            <FormField label="Тип вызова"
                       type="select"
                       v-model="form.call_type"
                       :options="callTypes"
                       :error="form.errors.call_type"
            />
            <div class="col-span-4 flex gap-6">
                <FormField label="Учебная" type="checkbox" v-model="form.is_training"/>
                <FormField label="Важная" type="checkbox" v-model="form.is_important"/>
            </div>
            <div class="col-span-full grid align-center text-13 grid-cols-6">
                <InputLabel label="Службы"/>
                <div class="col-span-5 flex gap-6">
                    <FormField v-for="type in services"
                               :key="type.id"
                               :value="type.id"
                               :label="type.name"
                               type="checkbox"
                               v-model:checked="form.services"
                               v-show="checkedCallTypeServiceId !== type.id"
                    />
                </div>
            </div>
        </FormGroup>
        <FormGroup title="Место происшествия" :cols="6">
            <FormField type="select" v-model="form.area_id" label="Район" :options="areas"/>
            <FormField type="select"
                       v-model="form.district_id"
                       label="Округ"
                       :text-align="'right'"
                       :options="districts"
            />
            <FormField v-model="form.street" label="Улица" :text-align="'right'"/>
            <InputLabel label="Дом"/>
            <div class="grid grid-cols-5 gap-2">
                <TextInput v-model="form.house_number" class="col-span-2"/>
                <FormField v-model="form.corpus_number" label="Корп." :col-span="3" :text-align="'right'"/>
            </div>
            <InputLabel label="Квартира" :text-align="'right'"/>
            <div class="grid grid-cols-5 gap-2">
                <TextInput v-model="form.apartment_number" class="col-span-2"/>
                <FormField v-model="form.entrance_number" label="Под." :col-span="3" :text-align="'right'"/>
            </div>
            <InputLabel label="Код" :text-align="'right'"/>
            <div class="grid grid-cols-5 gap-2">
                <TextInput v-model="form.entrance_code" class="col-span-1" :text-align="'right'"/>
                <FormField v-model="form.floor" label="Этаж" :col-span="2" :text-align="'right'"/>
                <FormField v-model="form.number_of_floors" label="Этажность" :col-span="2" :text-align="'right'"/>
            </div>
            <FormField v-model="form.ownership" label="Владение"/>
            <FormField v-model="form.building" label="Строение" :text-align="'right'"/>
            <FormField v-model="form.additional_street" label="Доп. улица" :text-align="'right'"/>
            <FormField v-model="form.district_of_city" type="select" label="Район города" />
            <FormField v-model="form.object" type="select" label="Объект" :text-align="'right'"/>
            <FormField v-model="form.coordinates" label="Координаты" :text-align="'right'"/>
            <FormField v-model="form.road" type="text" label="Дорога" />
            <InputLabel label="Метр" text-align="right"/>
            <div class="grid grid-cols-5 gap-2">
                <TextInput v-model="form.metre" class="col-span-2"/>
                <FormField v-model="form.km" label="Км/м" :col-span="3" :text-align="'right'"/>
            </div>
            <FormField v-model="form.is_nearby" label="Рядом" type="checkbox" :col-span="2"/>
            <FormField label="Адресный участок" v-model="form.address_section" :col-span="6" :grid-col="6" type="textarea"/>
            <FormField label="Доп. инфо" v-model="form.additional_info" :col-span="6" :grid-col="6" type="textarea"/>
        </FormGroup>

        <FormGroup label="Описание происшествия" :cols="6">
            <div class="col-span-2 grid grid-cols-2">
                <FormField v-model="form.emergency_threat" label="Угроза ЧС" type="checkbox"/>
                <FormField v-model="form.threat_to_people" label="Угроза людям" type="checkbox"/>
            </div>
            <FormField v-model="form.number_of_victims" label="Пострадавших" :text-align="'right'"/>
            <FormField v-model="form.emergency_type_id" label="Тип ЧС" type="select"  :text-align="'right'"/>
            <FormField label="Описание" v-model="form.description" :col-span="6" :grid-col="6" type="textarea"/>

        </FormGroup>
        <FormGroup title="Информация о заявителе" :cols="6">
            <FormField label="Фамилия" v-model="form.applicant_info.lastname"/>
            <FormField label="Имя" v-model="form.applicant_info.firstname" :text-align="'right'"/>
            <FormField label="Отчество" v-model="form.applicant_info.surname" :text-align="'right'"/>
            <FormField label="Телефон" v-model="form.applicant_info.phone"/>
            <FormField label="Статус" v-model="form.applicant_info.status" :text-align="'right'"/>
            <InputLabel label="Дата рождения" :text-align="'right'"/>
            <div class="grid grid-cols-5 gap-2">
                <TextInput v-model="form.applicant_info.birthday" class="col-span-2"/>
                <FormField v-model="form.applicant_info.age" label="Возраст" :col-span="3" :text-align="'right'"/>
            </div>
            <FormField type="select" v-model="form.applicant_info.district" label="Район"/>
            <FormField type="select" v-model="form.applicant_info.area" label="Округ" :text-align="'right'"/>
            <FormField type="select" v-model="form.applicant_info.street" label="Улица" :text-align="'right'"/>
            <InputLabel label="Дом"/>
            <div class="grid grid-cols-5 gap-2">
                <TextInput v-model="form.applicant_info.house" class="col-span-2"/>
                <FormField v-model="form.applicant_info.corpus" label="Корп." :col-span="3" :text-align="'right'"/>
            </div>
            <InputLabel label="Квартира" :text-align="'right'"/>
            <div class="grid grid-cols-5 gap-2">
                <TextInput v-model="form.applicant_info.apartment" class="col-span-2"/>
            </div>
            <FormField v-model="form.applicant_info.coordinates" label="Координаты" :text-align="'right'"/>
            <FormField label="Доп. инфо" v-model="form.applicant_info.additional_info" :col-span="6" :grid-col="6" type="textarea"/>
            <FormField label="Язык общения" v-model="form.applicant_info.language"/>
        </FormGroup>
    </Block>

</template>

<style scoped>

</style>
