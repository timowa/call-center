
<script setup>
import {Head, Link, router, useForm, usePage} from "@inertiajs/vue3";
import {computed, onUnmounted, provide, ref, watch, inject} from "vue";
import UKIOSummary from '@/Pages/Incidents/Partials/UKIOSummary.vue';
import UKIOToggle from '@/Pages/Incidents/Partials/UKIOToggle.vue';
import axios from 'axios';
import TrainingPanel from '@/Pages/Incidents/Partials/TrainingPanel.vue';
import Gas from '@/Pages/Incidents/Partials/Gas.vue';
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

const props = defineProps(['incident', 'fireReportData', 'flash', 'mode', 'canUpdatePhone', 'canUpdate', 'editUkio']);
const createMode = computed(() => props.mode === 'create');
const viewMode = computed(() => props.mode === 'view');
const editMode = computed(() => props.mode === 'edit');

const page = usePage();
const training = ref(props.incident.training_session ?? null);
provide('trainingCall', training);
const user = computed(() => page.props.auth.user);
const isCov = computed(() => {
    return user.value.roles.includes('cov_112');
});


// Layout priority is independent of permissions: ЦОВ → 01 → ЕДДС.
const layout = computed(() => isCov.value ? 'cov' : user.value.roles.includes('op_01') ? '01' : user.value.roles.includes('edds') ? 'edds' : user.value.roles.includes('gas') ? 'gas' : 'cov');
const vertical = computed(() => ['01', 'gas'].includes(layout.value));
const expanded = ref(createMode.value || !vertical.value);
const ukioEditing = ref(createMode.value || vertical.value || props.editUkio);
const ukioView = computed(() => viewMode.value || !ukioEditing.value);
provide('ukioView', ukioView);
const canUpdatePhone = computed(() => !ukioView.value && (createMode.value ? userHasPermissionTo('ukio.create') : props.canUpdatePhone));

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
provide('directories', {
    callTypes: filteredCallTypes.value,
    incidentTypes: page.props.directories.incidentTypes,
    services: page.props.dictionaries.services,
    sources: page.props.dictionaries.sources,
    areas: filteredAreas.value,
    districts: filteredDistricts.value,
    urban_objects: page.props.directories.urban_objects,
    emergency_types: page.props.directories.emergency_types,
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
        date: props.incident.created_at_dt?.date ?? '',
        time: props.incident.created_at_dt?.time ?? ''
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
    gasReport: {id: null, message: null, utility_id: null, event_id: null, instruction: null, departed_at: null, arrived_at: null, measures: null, ...props.incident.gas_report},
    training_session_id: training.value?.id ?? null,
    complete_service: null,
    actionType: ''
});
const tabs = computed(() => ({
    UKIO: {
        template: UKIO,
        title: 'УКИО',
        show: userHasPermissionTo('ukio.view'),
        condition: createMode.value ? null : getCondition(form.condition)
    },
    GAS: {template: Gas, title: 'Газ', show: form.services.includes(page.props.constants.services.GAS) && userHasPermissionTo('gas.view'), condition: getCondition(form.gasReport.condition)},
    EDDS: {
        template: EDDS,
        title: 'ЕДДС/ЖКХ',
        show: form.services.includes(page.props.constants.services.EDDS) && userHasPermissionTo('edds.view'),
        service_id: page.props.constants.services.EDDS,
        condition: getCondition(form.eddsReport.condition)
    },
    FIREFIGHTERS: {
        template: Firefighters,
        title: '01',
        service_id: page.props.constants.services.FIREFIGHTERS,
        show: form.services.includes(page.props.constants.services.FIREFIGHTERS) && userHasPermissionTo('01.view'),
        condition: getCondition(form.fireReport.condition)
    }
}));

const rightTabs =  computed(() => ({
    map: {
        template: IncidentMap,
        title: 'Карта'
    },
    ...(training.value?.kind === 'glonass' ? {glonass: {template: '', title: 'ГЛОНАСС'}} : {}),
    history: {
        template: '',
        title: 'История'
    },
    calls: {
        template: '',
        title: 'Звонки и СМС'
    }
}))
const currentTab = ref('UKIO');
watch(() => tabs.value[currentTab.value]?.show, (isVisible) => {
    if (!isVisible && currentTab.value !== 'UKIO') {
        currentTab.value = 'UKIO';
    }
});
const currentRightTab = ref(training.value?.kind === 'glonass' ? 'glonass' : training.value ? 'calls' : 'map');
const mayBeCompleted = computed(() => {
    return [page.props.constants.callTypes.HELP, page.props.constants.callTypes.FALSE, page.props.constants.callTypes.CHILD].includes(form.call_type_id)
});
const transitionPending = ref(false);
provide('departmentTransition', async (row, stage) => {
    if (createMode.value) return false;
    if (transitionPending.value || form.processing) throw new Error('Дождитесь завершения запроса.');
    transitionPending.value = true;
    try {
        const {data} = await axios.post(route('incidents.department-transition', props.incident.id), {
            report_id: form.fireReport.id, department_id: row.department_id,
            expected_condition: row.condition_id, condition_id: stage.id,
        });
        // Earlier edited timestamps remain a draft; only the newly recorded stage is merged.
        row.condition_id = data.row.condition_id;
        row[stage.field] = data.row[stage.field];
        row.transitions = [];
        form.condition = data.condition;
        form.fireReport.condition = data.report_condition;
        return true;
    } finally { transitionPending.value = false; }
});
const finishService = service => {
    if (form.processing || transitionPending.value) return;
    form.complete_service = service;
    submit();
};
provide('finishService', finishService);
const ownService = computed(() => layout.value === '01' ? '01' : layout.value === 'gas' ? 'gas' : null);
const ownReport = computed(() => layout.value === '01' ? form.fireReport : form.gasReport);
const submit = (actionType = null) => {
    if (viewMode.value || form.processing || transitionPending.value) return;
    form.transform(data => {
        const payload = {...data};
        for (const [key, permission] of [['fireReport', '01'], ['eddsReport', 'edds'], ['gasReport', 'gas']]) {
            const action = data[key]?.id ? 'edit' : 'create';
            if (!userHasPermissionTo(`${permission}.${action}`)) delete payload[key];
        }
        if (data.complete_service === 'edds') return {id: data.id, call_type_id: props.incident.call_type_id, complete_service: 'edds', eddsReport: payload.eddsReport};
        return payload;
    });
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
                    v-show="tab.show && (!vertical || id === 'UKIO')"
                    :active="currentTab === id"
                    :class="createMode && id === 'UKIO' ? 'text-red-700 bg-red-100' : ''"
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

        <form @submit.prevent="submit()">
            <div v-if="Object.keys(form.errors).length" role="alert" class="text-red-700 px-6"><p v-for="error in form.errors">{{ error }}</p></div>
            <template v-if="vertical">
                <template v-if="tabs.UKIO.show">
                    <UKIOSummary v-show="!expanded" :form="form" :can-edit-phone="canUpdatePhone">
                        <UKIOToggle :expanded="false" @click="expanded = true" />
                    </UKIOSummary>
                    <div v-show="expanded">
                        <UKIO :form="form" :create-mode="createMode">
                            <UKIOToggle :expanded="true" @click="expanded = false" />
                        </UKIO>
                    </div>
                </template>
                <Gas v-if="tabs.GAS.show" :form="form" class="mt-5" />
                <div v-if="tabs.FIREFIGHTERS.show" class="mt-5">
                    <Firefighters :form="form" />
                </div>
            </template>
            <template v-else>
                <UKIOSummary v-if="layout === 'edds' && currentTab === 'EDDS' && tabs.EDDS.show"
                             :form="form" :can-edit-phone="canUpdatePhone" class="mb-5" />
                <keep-alive>
                    <component v-if="tabs[currentTab]?.show" :key="currentTab" :is="tabs[currentTab].template" :form="form" :create-mode="createMode"/>
                </keep-alive>
            </template>
            <div class="mt-6 px-6">
                <div class="flex justify-between">
                    <LinkButton v-if="createMode || vertical || ukioEditing" :href="route('incidents.dashboard')">Отмена</LinkButton>
                    <div class="flex gap-2 ml-auto flex-wrap">
                        <template v-if="!createMode">
                            <PrimaryButton v-if="!vertical && !ukioEditing && props.canUpdate" @click="viewMode ? router.get(route('incidents.edit', incident.id), {editUkio: 1}) : ukioEditing = true">Редактировать</PrimaryButton>
                            <template v-else-if="!viewMode">
                                <PrimaryButton v-if="vertical && ownReport?.condition !== 5 && userHasPermissionTo(ownService + '.edit')" :disabled="form.processing || transitionPending" @click="finishService(ownService)">Завершить работу с карточкой</PrimaryButton>
                                <PrimaryButton :disabled="form.processing || transitionPending" @click="submit()">Сохранить</PrimaryButton>
                            </template>
                        </template>
                        <template v-else>
                            <template v-if="form.source_id === page.props.constants.sources.CALL">
                                <PrimaryButton :disabled="form.processing" @click="submit('help')">Справочный</PrimaryButton>
                                <PrimaryButton :disabled="form.processing" @click="submit('false')">Ложный</PrimaryButton>
                                <PrimaryButton :disabled="form.processing" @click="submit('child')">Детская шалость</PrimaryButton>
                            </template>
                            <PrimaryButton v-if="!form.call_type_id || mayBeCompleted" :disabled="form.processing || !mayBeCompleted" @click="submit()">Сохранить</PrimaryButton>
                            <template v-else>
                                <PrimaryButton v-if="form.source_id === page.props.constants.sources.CALL && !training?.hung_up" :disabled="form.processing" @click="submit('transfer')">Передать с вызовом</PrimaryButton>
                                <PrimaryButton v-if="form.source_id === page.props.constants.sources.CALL && !training?.hung_up" :disabled="form.processing" title="Учебная имитация">Конференция</PrimaryButton>
                                <PrimaryButton :disabled="form.processing" @click="submit('transfer')">Передать без вызова</PrimaryButton>
                            </template>
                        </template>
                    </div>
                </div>
            </div>

        </form>

        <template #right-panel>
            <Block>
                <TrainingPanel v-if="training && ((currentRightTab === 'calls' && training.kind !== 'glonass') || (currentRightTab === 'glonass' && training.kind === 'glonass'))" :session="training" :autoplay="createMode" @updated="training = $event" />
                <keep-alive>
                    <component :is="rightTabs[currentRightTab].template" />
                </keep-alive>
            </Block>
        </template>

    </AuthenticatedLayout>

</template>

<style scoped>

</style>
