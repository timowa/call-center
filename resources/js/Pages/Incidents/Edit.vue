
<script setup>
import {Head, Link, router, useForm, usePage} from "@inertiajs/vue3";
import {computed, onUnmounted, provide, ref, watch, inject} from "vue";
import UKIO from "@/Pages/Incidents/Partials/UKIO.vue";
import EDDS from "@/Pages/Incidents/Partials/EDDS.vue";
import IncidentMap from "@/Pages/Incidents/Partials/IncidentMap.vue";
import TabsHeader from "@/Components/TabsHeader.vue";
import TabHeaderButton from "@/Components/TabHeaderButton.vue";
import Firefighters from "@/Pages/Incidents/Partials/Firefighters.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {getFireReportDefaults} from "@/Utils/fireReportForm.js";
import {getCondition} from "@/Utils/conditions.js";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Block from "@/Components/Block.vue";
import {userHasPermissionTo} from "@/Utils/permissions.js";
import LinkButton from "@/Components/LinkButton.vue";
import {getEDDSDefaults} from "@/Utils/EDDSForm.js";

const props = defineProps(['incident', 'fireReportData', 'flash', 'mode']);
const createMode = computed(() => props.mode === 'create');
const viewMode = computed(() => props.mode === 'view');
const editMode = computed(() => props.mode === 'edit');

const page = usePage();
const user = computed(() => page.props.auth.user);
const isCov = computed(() => {
    return user.value.roles.includes('cov_112');
});


const filteredAreas = computed(() => {
    if (isCov.value ) {
        return page.props.directories.areas;
    }
    return page.props.directories.areas.filter(area => area.id === user.value.area_id);
});

const filteredDistricts = computed(() => {
    if (isCov.value) {
        return page.props.directories.districts;
    }
    return page.props.directories.districts.filter(district => district.area_id === user.value.area_id);
});

const filteredCallTypes = computed(() => {
    if (isCov.value) {
        return page.props.dictionaries.callTypes
    }
    return page.props.dictionaries.callTypes.filter(callType => callType.id === user.value.call_type_id)
})

provide('viewMode', viewMode)
console.log(filteredCallTypes.value)
provide('directories', {
    callTypes: filteredCallTypes.value,
    incidentTypes: page.props.directories.incidentTypes,
    services: page.props.dictionaries.services,
    sources: page.props.dictionaries.sources,
    areas: filteredAreas.value,
    districts: filteredDistricts.value
})
const defaultCoordinates = {latitude: 53.722356, longitude: 91.443699}
const mapCoordinates = ref(defaultCoordinates);
provide('mapCoordinates', {
    data: mapCoordinates,
    setCoordinates: (val) => mapCoordinates.value = val
})
const form = useForm({
    id: props.incident.id ?? 0,
    created_at: {
        date: props.incident.created_at_dt?.date,
        time: props.incident.created_at_dt?.time
    },
    processing_time: props.incident.processingTime ?? 0,
    incoming_number: props.incident.incoming_number ?? 0,
    condition: props.incident.condition ?? 0,
    creator: props.incident.user?.name ?? user.value.name,
    call_type_id: props.incident.call_type_id ?? (user.value.call_type_id ?? null),
    services: props.incident.services ?? [],
    incident_type_id: props.incident.type ? props.incident.type.id : null,
    source_id: props.incident.source_id ?? 0,
    is_training: props.incident.is_training ?? false,
    is_important: props.incident.is_important ?? false,
    area_id: props.incident?.district_id ? (page.props.directories.districts.filter(district => district.id === props.incident.district_id)[0].area_id) : null,
    district_id: props.incident?.district_id ?? null,
    street: props.incident.street ?? null,
    house_number: props.incident.house_number ?? null,
    corpus_number: props.incident.corpus_number ?? null,
    apartment_number: props.incident.apartment_number ?? null,
    entrance_number: props.incident.entrance_number ?? null,
    entrance_code: props.incident.entrance_code ?? null,
    floor: props.incident.floor ?? null,
    number_of_floors: props.incident.number_of_floors ?? null,
    ownership: props.incident.ownership ?? null,
    building: props.incident.building ?? null,
    additional_street: props.incident.additional_street ?? null,
    district_of_city: props.incident.district_of_city ?? null,
    object: props.incident.object ?? null,
    coordinates: [props.incident.longitude, props.incident.latitude].some(item => item !== null) ?
        [props.incident.longitude, props.incident.latitude].join(',') : '',
    road: props.incident.road ?? null,
    metre: props.incident.metre ?? null,
    km: props.incident.km ?? null,
    is_nearby: props.incident.is_nearby ?? false,
    address_section: props.incident.address_section ?? '',
    additional_info: props.incident.additional_info ?? '',
    emergency_threat: props.incident.emergency_threat ?? false,
    threat_to_people: props.incident.threat_to_people ?? false,
    number_of_victims: props.incident?.number_of_victims ?? 0,
    emergency_type_id: props.incident.emergency_type_id ?? null,
    description: props.incident?.description ?? '',
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
    eddsReport: getEDDSDefaults(props.incident.edds_report),
    fireReport: getFireReportDefaults(props.incident.fire_report),
    actionType: ''
});
const tabs = computed(() => ({
    UKIO: {
        template: UKIO,
        title: 'УКИО',
        show: userHasPermissionTo('ukio.view'),
        condition: getCondition(props.incident.condition)
    },
    EDDS: {
        template: EDDS,
        title: 'ЕДДС/ЖКХ',
        show: form.services.includes(page.props.constants.services.EDDS) && userHasPermissionTo('edds.view'),
        service_id: page.props.constants.services.EDDS,
        condition: getCondition(props.incident.edds_report?.condition)
    },
    FIREFIGHTERS: {
        template: Firefighters,
        title: '01',
        service_id: page.props.constants.services.FIREFIGHTERS,
        show: form.services.includes(page.props.constants.services.FIREFIGHTERS) && userHasPermissionTo('01.view'),
        condition: getCondition(props.incident.fire_report?.condition)
    }
}));

const rightTabs =  computed(() => ({
    map: {
        template: IncidentMap,
        title: 'Карта'
    },
    history: {
        template: '',
        title: 'История'
    },
    calls: {
        template: '',
        title: 'Звонки и СМС'
    }
}))
watch(() => tabs.value.EDDS.show, (isVisible) => {
    if (!isVisible && currentTab.value === 'EDDS') {
        currentTab.value = 'UKIO';
    }
});
const currentTab = ref('UKIO');
const currentRightTab = ref('map');
const mayBeCompleted = computed(() => {
    return [page.props.constants.callTypes.HELP, page.props.constants.callTypes.FALSE, page.props.constants.callTypes.CHILD].includes(form.call_type_id)
});
const submit = (actionType) => {
    if (createMode.value) {
        form.actionType = actionType;
        if (actionType === 'help') {
            form.call_type_id = page.props.constants.callTypes.HELP
        } else if (actionType === 'false') {
            form.call_type_id = page.props.constants.callTypes.FALSE
        } else if (actionType === 'child') {
            form.call_type_id = page.props.constants.callTypes.CHILD
        }

        form.post(route('incidents.store'), {
            preserveScroll: true,
            onSuccess: () => {
                console.log('Данные успешно обновлены');
            },
            onError: (errors) => {
                console.error('Ошибки валидации:', errors);
            },
        })
    }
    if (editMode.value) {
        form.put(route('incidents.update', props.incident.id), {
            preserveScroll: true,
            onSuccess: () => {
                console.log('Данные успешно обновлены');
            },
            onError: (errors) => {
                console.error('Ошибки валидации:', errors);
            },
        });
    }

}


onUnmounted(() => {
    if (form.call_type === null) {
        form.call_type = 1
        submit();
    }
})

watch(() => form.data(), (newData, oldData) => {
    Object.keys(newData).forEach(key => {
        if (newData[key] !== oldData[key]) {
            form.clearErrors(key);
        }
    });
}, { deep: true });
watch(() => form.errors, (errors) => {
    setTimeout(() => {
        form.clearErrors()
    }, 3000)
})
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #top-content>
            <Link :href="route('dashboard')">
                <span class="flex">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="mi-outline mi-chevron-left" viewBox="0 0 24 24">
                        <path d="M14.71 6.71a.996.996 0 0 0-1.41 0L8.71 11.3a.996.996 0 0 0 0 1.41l4.59 4.59a.996.996 0 1 0 1.41-1.41L10.83 12l3.88-3.88c.39-.39.38-1.03 0-1.41"/>
                    </svg>
                    Обработка карточки
                </span>

            </Link>
        </template>
        <template #main-tabs>
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
        </template>
        <template #right-panel-tabs>
            <TabsHeader>
                <TabHeaderButton
                    v-for="(tab, id) in rightTabs"
                    :active="currentRightTab === id"
                    @click="currentRightTab = id"
                >
                    <div class="flex items-center gap-2">
                        {{tab.title}}
                    </div>
                </TabHeaderButton>
            </TabsHeader>
        </template>

        <form @submit.prevent="submit">
            <keep-alive>
                <component :is="tabs[currentTab].template" :form="form"/>
            </keep-alive>
            <div class="mt-6 px-6">
                <div class="flex justify-between">
                    <LinkButton :href="route('incidents.dashboard')">Отмена</LinkButton>

                    <PrimaryButton v-if="viewMode" @click="router.get(route('incidents.edit', incident.id));" type="button">Редактировать</PrimaryButton>

                    <div v-if="(createMode && form.source_id === page.props.constants.sources.INSTANT) || editMode" class="flex gap-2">
                        <PrimaryButton @click="submit">Сохранить</PrimaryButton>
                        <PrimaryButton @click="submit('complete')" v-if="mayBeCompleted">Завершить работу с карточкой</PrimaryButton>
                    </div>

                    <div v-if="createMode && form.source_id === page.props.constants.sources.CALL" class="flex gap-2">
                        <PrimaryButton :disabled="form.processing" @click="submit('help')">Справочный</PrimaryButton>
                        <PrimaryButton :disabled="form.processing" @click="submit('false')">Ложный</PrimaryButton>
                        <PrimaryButton :disabled="form.processing" @click="submit('child')">Детская шалость</PrimaryButton>
                        <PrimaryButton :disabled="form.processing || form.call_type === 0" @click="submit" >Передать без вызова</PrimaryButton>
                        <PrimaryButton :disabled="form.processing || form.call_type === 0" @click="submit" >Переать с вызовом</PrimaryButton>
                    </div>

                </div>
            </div>

        </form>

        <template #right-panel>
            <Block>
                <keep-alive>
                    <component :is="rightTabs[currentRightTab].template" />
                </keep-alive>
            </Block>
        </template>

    </AuthenticatedLayout>

</template>

<style scoped>

</style>
