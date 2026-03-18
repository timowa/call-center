<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

export default {
    layout: AuthenticatedLayout
}
</script>
<script setup>
import {Head, router, useForm, usePage} from "@inertiajs/vue3";
import {computed, onUnmounted, provide, ref, watch, inject} from "vue";
import UKIO from "@/Pages/Incidents/Partials/UKIO.vue";
import EDDS from "@/Pages/Incidents/Partials/EDDS.vue";
import TabsHeader from "@/Components/TabsHeader.vue";
import TabHeaderButton from "@/Components/TabHeaderButton.vue";
import Firefighters from "@/Pages/Incidents/Partials/Firefighters.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {getFireReportDefaults} from "@/Utils/fireReportForm.js";
import {getCondition} from "@/Utils/conditions.js";

const props = defineProps(['incident', 'incidentTypes', 'services', 'areas', 'districts', 'callTypes', 'isCreator', 'fireReportData']);
const viewMode = ref(false);
const conditions = inject('conditions');
const page = usePage();
const user = computed(() => page.props.auth.user);
const canSeeAllAreas = computed(() => {
    return user.value.permissions.includes('incidents.view.all-area');
});
const canSeeAllCallTypes = computed(() => {
    return user.value.permissions.includes('incidents.view.all-call_type')
})

const filteredAreas = computed(() => {
    if (canSeeAllAreas.value || !user.value.area_id) {
        return props.areas;
    }
    return props.areas.filter(area => area.id === user.value.area_id);
});

const filteredDistricts = computed(() => {
    if (canSeeAllAreas.value || !user.value.area_id) {
        return props.districts;
    }
    return props.districts.filter(district => district.area_id === user.value.area_id);
});

const filteredCallTypes = computed(() => {
    if (canSeeAllCallTypes.value || !user.value.call_type_id) {
        return props.callTypes
    }
    return props.callTypes.filter(callType => callType.id === user.value.call_type_id)
})

provide('viewMode', viewMode)
provide('directories', {
    callTypes: filteredCallTypes.value,
    incidentTypes: props.incidentTypes,
    services: props.services,
    areas: filteredAreas.value,
    districts: filteredDistricts.value,
    isCreator: props.isCreator,
})
provide('fireReportDirectories', props.fireReportData)
const form = useForm({
    id: props.incident.id,
    created_at: {
        date: props.incident.dt.date,
        time: props.incident.dt.time
    },
    processing_time: props.incident.processingTime,
    incoming_number: props.incident.incoming_number,
    condition: props.incident.condition,
    creator: props.incident.user.name,
    call_type: user.value.call_type_id ?? (props.incident.call_type ? props.incident.call_type?.id : null),
    services: props.incident.services,
    incident_type: props.incident.type ? props.incident.type.id : null,
    source: props.incident.source,
    is_training: props.incident.is_training,
    is_important: props.incident.is_important,
    area_id: user.value.area_id ?? props.incident?.area_id ?? null,
    district_id: props.incident?.district_id,
    street: props.incident.street,
    house_number: props.incident.house_number,
    corpus_number: props.incident.corpus_number,
    apartment_number: props.incident.apartment_number,
    entrance_number: props.incident.entrance_number,
    entrance_code: props.incident.entrance_code,
    floor: props.incident.floor,
    number_of_floors: props.incident.number_of_floors,
    ownership: props.incident.ownership,
    building: props.incident.building,
    additional_street: props.incident.additional_street,
    district_of_city: props.incident.district_of_city,
    object: props.incident.object,
    coordinates: [props.incident.longitude, props.incident.latitude].some(item => item !== null) ?
        [props.incident.longitude, props.incident.latitude].join(',') : '',
    road: props.incident.road,
    metre: props.incident.metre,
    km: props.incident.km,
    is_nearby: props.incident.is_nearby,
    address_section: props.incident.address_section,
    additional_info: props.incident.additional_info,
    emergency_threat: props.incident.emergency_threat,
    threat_to_people: props.incident.threat_to_people,
    number_of_victims: props.incident.number_of_victims,
    emergency_type_id: props.incident.emergency_type_id,
    description: props.incident.description,
    applicant_info: props.incident.applicant_info || {
        lastname: '',
        firstname: '',
        surname: '',
        phone: '',
        status: '',
        birthday: '',
        age: '',
        district: '',
        area: '',
        street: '',
        house: '',
        corpus: '',
        apartment: '',
        coordinates: '',
        additional_info: '',
        language: ''
    },
    services_info: props.incident.services_info || {
        EDDS: {
            info: {
                type: '',
                company: '',
                instruction: '',
                message: '',
                additional_info: '',
                elimination_datetime: '',
                is_consultation: false,
            },
            results: {
                forces: '',
                departure_datetime: '',
                arrival_datetime: '',
                elimination_datetime: '',
                applicant_feedback_datetime: '',
                dispatcher: '',
                take_actions: ''
            },
            response: {

            }
        },
    },
    fireReport: getFireReportDefaults(props.incident.fire_report)
});
const tabs = computed(() => ({
    UKIO: {
        template: UKIO,
        title: 'УКИО',
        show:true,
        condition: getCondition(props.incident.condition)
    },
    EDDS: {
        template: EDDS,
        title: 'ЕДДС/ЖКХ',
        show: form.services.includes(8) || form.call_type === 8,
        service_id: 8
    },
    FIREFIGHTERS: {
        template: Firefighters,
        title: '01',
        service_id: 4,
        show: form.services.includes(4) || form.call_type === 4,
        condition: getCondition(props.incident.fire_report?.condition)
    }
}));
watch(() => tabs.value.EDDS.show, (isVisible) => {
    if (!isVisible && currentTab.value === 'EDDS') {
        currentTab.value = 'UKIO';
    }
});
const currentTab = ref('UKIO');

const submit = () => {
    console.log('Данные к отправке:', form.data());
    form.put(route('incidents.update', props.incident.id), {
        preserveScroll: true, // Чтобы страница не прыгала вверх после сохранения
        onSuccess: () => {
            // Можно добавить уведомление
            console.log('Данные успешно обновлены');
        },
        onError: (errors) => {
            // Вывод ошибок валидации в консоль для отладки
            console.error('Ошибки валидации:', errors);
        },
    });
}


onUnmounted(() => {
    if (form.call_type === null) {
        form.call_type = 1
        submit();
    }
})
</script>

<template>
    <Head title="Dashboard" />
    <div class="w-full h-full bg-grey-200 border-gray-300 border-2 rounded-md p-2">
        <TabsHeader>
            <TabHeaderButton
                v-for="(tab, id) in tabs"
                v-show="tab.show"
                :active="currentTab === id"
                @click="currentTab = id"
            >
                <div class="flex items-center gap-2">
                    <div v-if="tab.condition" :class="['w-3 h-3 rounded-sm', tab.condition.color]"></div>
                    {{tab.title}}
                </div>
            </TabHeaderButton>
        </TabsHeader>
        <form @submit.prevent="submit" >
            <keep-alive>
                <component :is="tabs[currentTab].template" :form="form"/>
            </keep-alive>
            <div class="text-right mt-6">
                <PrimaryButton v-if="viewMode === true" @click="viewMode = false" type="button">Редактировать</PrimaryButton>
                <div v-if="viewMode !== true" class="flex justify-end gap-2">
                    <PrimaryButton :disabled="form.processing" @click="form.call_type = 2; form.condition = 5">Детская шалость</PrimaryButton>
                    <PrimaryButton :disabled="form.processing" @click="form.call_type = 1; form.condition = 5">Ложный</PrimaryButton>
                    <PrimaryButton :disabled="form.processing || form.call_type === 0" @click="form.condition = 2">Передать без вызова</PrimaryButton>
                    <PrimaryButton :disabled="form.processing || form.call_type === 0" @click="form.condition = 2">Переать с вызовом</PrimaryButton>

                </div>
            </div>
        </form>

    </div>
</template>

<style scoped>

</style>
