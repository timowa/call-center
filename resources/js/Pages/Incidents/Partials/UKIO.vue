<script setup>

import FormField from "@/Components/Form/FormField.vue";
import FormGroup from "@/Components/Form/FormGroup.vue";
import InputLabel from "@/Components/Form/InputLabel.vue";
import TextInput from "@/Components/Form/TextInput.vue";
import {computed, inject, provide, ref, watch} from "vue";
import Block from "@/Components/Block.vue";
import {addressSearch} from "@/Composables/addressSearch.js";
import {fetchCoordinates} from "@/Composables/getCoordinates.js";
import {userHasPermissionTo} from "@/Utils/permissions.js";
import {usePage} from "@inertiajs/vue3";
import {FireReportDirectories} from "@/Utils/FireReportDirectories.js";
const props = defineProps(['form']);
const page = usePage();
const {callTypes, incidentTypes, areas, districts, services, sources} = inject('directories');
const checkedCallTypeServiceId = ref(null);

watch(
    () => props.form.call_type_id,
    (newCallType) => {
        if (checkedCallTypeServiceId.value !== null) {
            const oldIndex = props.form.services.indexOf(checkedCallTypeServiceId.value);
            if (oldIndex !== -1) {
                props.form.services.splice(oldIndex, 1);
            }
            checkedCallTypeServiceId.value = null;
        }

        if (!newCallType) return;

        const callTypeData = callTypes.find((ct) => ct.id === newCallType);
        if (!callTypeData || !callTypeData.has_service) return;

        const serviceId = callTypeData.service_id;

        if (!props.form.services.includes(serviceId)) {
            props.form.services.push(serviceId);
        }

        if (serviceId === page.props.constants.services.FIREFIGHTERS) {
            FireReportDirectories.fetchFireDirectories()
        }

        checkedCallTypeServiceId.value = serviceId;

    },
    { immediate: true }
);

watch(
    () => props.form.services,
    (newServices) => {
        if (newServices.includes(page.props.constants.services.FIREFIGHTERS)) {
            FireReportDirectories.fetchFireDirectories()
        }
    },
    { deep: true }
);

const filteredDistricts = computed(() => {
    if (!props.form.area_id) {
        return districts;
    }
    return districts.filter(district => district.area_id === props.form.area_id);
});

watch(() => props.form.area_id, () => {
    props.form.district_id = null;
});

const streets = addressSearch();
watch(() => props.form.district_id, (newVal) => {
    props.form.street = null;
    if (newVal) {
        streets.loadInitial(props.form.area_id, props.form.district_id);
    }
});
const coordsService = fetchCoordinates();
const {mapCoordinates, setCoordinates} = inject('mapCoordinates');
watch(
    () => [props.form.area_id, props.form.district_id, props.form.street, props.form.house_number, props.form.corpus_number],
    async ([area, district, street, house, corpus]) => {
        await coordsService.fetch(area, district, street, house, corpus);
    }
);

watch(() => coordsService.coordinates.value, (newCoords) => {
    if (newCoords) {
        props.form.coordinates = `${newCoords.latitude}, ${newCoords.longitude}`;
        setCoordinates({longitude: newCoords.longitude, latitude: newCoords.latitude})
    } else {
        props.form.coordinates = '';
    }
});


</script>

<template>
    <Block>
        <FormGroup title="Информация" :cols="6">
            <FormField label="№ карточки" v-model="form.id" :readonly="true" />
            <FormField label="Время обработки" v-model="form.processing_time" :readonly="true"  :text-align="'right'"/>
            <FormField label="Входящий номер" v-model="form.incoming_number" :readonly="true"  :text-align="'right'" />
            <FormField label="Дата обращения" v-model="form.created_at.date" :readonly="true"  />
            <FormField label="Время регистрации" v-model="form.created_at.time" :readonly="true"  :text-align="'right'" />
            <FormField label="Создатель" v-model="form.creator" :readonly="true"  :text-align="'right'" />
            <FormField label="Тип происшествия" type="select" v-model="form.incident_type_id" :col-span="4" :grid-col="4" :options="incidentTypes"/>
            <FormField label="Источник" v-model="form.source_id" type="select" :options="sources"  :text-align="'right'" :readonly="true"/>
            <FormField label="Тип вызова"
                       type="select"
                       v-model="form.call_type_id"
                       :options="callTypes"
                       :error="form.errors.call_type_id"
            />
            <div class="col-span-4 flex gap-6">
                <FormField label="Учебная" type="checkbox" v-model:checked="form.is_training"/>
                <FormField label="Важная" type="checkbox" v-model:checked="form.is_important"/>
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
                       :options="filteredDistricts"
            />
            <FormField
                v-model="form.street"
                type="select"
                :options="streets.options.value"
                :filterable="false"
                @search="(query) => streets.onSearch(query, props.form.area_id, props.form.district_id)"
                label="Улица"
                :text-align="'right'"/>
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
            <FormField v-model="form.coordinates" label="Координаты" :text-align="'right'" :readonly="true"/>
            <FormField v-model="form.road" type="text" label="Дорога" />
            <InputLabel label="Метр" text-align="right"/>
            <div class="grid grid-cols-5 gap-2">
                <TextInput v-model="form.metre" class="col-span-2"/>
                <FormField v-model="form.km" label="Км/м" :col-span="3" :text-align="'right'"/>
            </div>
            <FormField v-model:checked="form.is_nearby" label="Рядом" type="checkbox" :col-span="2"/>
            <FormField label="Адресный участок" v-model="form.address_section" :col-span="6" :grid-col="6" type="textarea"/>
            <FormField label="Доп. инфо" v-model="form.additional_info" :col-span="6" :grid-col="6" type="textarea"
                       :can-edit="userHasPermissionTo('ukio.edit-description')"/>
        </FormGroup>

        <FormGroup label="Описание происшествия" :cols="6">
            <div class="col-span-2 grid grid-cols-2">
                <FormField v-model:checked="form.emergency_threat" label="Угроза ЧС" type="checkbox"/>
                <FormField v-model:checked="form.threat_to_people" label="Угроза людям" type="checkbox"/>
            </div>
            <FormField v-model="form.number_of_victims" label="Пострадавших" :text-align="'right'"/>
            <FormField v-model="form.emergency_type_id" label="Тип ЧС" type="select"  :text-align="'right'"/>
            <FormField label="Описание" v-model="form.description" :col-span="6" :grid-col="6" type="textarea" />

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
