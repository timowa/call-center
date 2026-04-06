
<script setup>
import {computed, onMounted, onUnmounted, provide, ref, watch} from "vue";
import IncidentPreview from "@/Pages/Dashboard/Incidents/Partials/IncidentPreview.vue";
import TabPageButton from "@/Components/TabPageButton.vue";
import {Head, router, useForm, usePage} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import LinkButton from "@/Components/LinkButton.vue";
import ActiveFiltersRow from "@/Components/ActiveFiltersRow.vue";
import FilterButton from "@/Components/FilterButton.vue";
import ModeChanger from "@/Pages/Dashboard/Incidents/Partials/ModeChanger.vue";
import FilterModal from "@/Pages/Dashboard/Incidents/Partials/FilterModal.vue";
import IncidentsTable from "@/Pages/Dashboard/Incidents/Partials/IncidentsTable.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import ActiveFilterButton from "@/Components/ActiveFilterButton.vue";
import {getConditionLabel} from "@/Utils/conditions.js";
const props = defineProps(['incidents']);
provide('incidents', computed(() => props.incidents));

const selectedIncident = ref(null);
const page = usePage();
provide('selectedIncident', {
    data: selectedIncident,
    setSelected: (val) => {
        selectedIncident.value = val
    }
});

const currentTab = ref('incidents')


const showAlarmOverlay = ref(false);
const audio = ref(null);

const activateAlarm = () => {
    showAlarmOverlay.value = true;

    if (!audio.value) {
        audio.value = new Audio('/alert.mp3');
        audio.value.loop = true;
    }
    audio.value.play().catch(e => console.log("Нужно взаимодействие с юзером для звука"));
};

const acceptCall = () => {
    if (audio.value) {
        audio.value.pause();
        audio.value.currentTime = 0;
    }

    showAlarmOverlay.value = false;

    router.post(route('incidents.create-from-call'), );
};

const isFilterOpen = ref(false);
const savedFilters = JSON.parse(localStorage.getItem('filter')) || {};
const filterForm = useForm({
    ...{
        service_id: null,
        call_type_id: null,
        is_training: false,
        is_important: false,
        emergency_threat: false,
        period_day: false,
        period_custom: false,
        period: [],
        conditions: [],
        area_id: null,
        district_id: null,
        source_id: null,
        creator: null,
        incoming_number: null,
        applicant_lastname: null,
        applicant_firstname: null,
        applicant_surname: null,
        applicant_phone: null,
    },
    ...savedFilters

});
const getFilterLabel = (key) => {
    const labels = {
        service_id: 'Служба',
        call_type_id: 'Тип вызова',
        is_training: 'Учебная',
        is_important: 'Важная',
        emergency_threat: 'Экстримальный',
        period_day: 'Период: 24 часа',
        period_custom: 'Указанный период',
        conditions: 'Сосотяния',
        area_id: 'Район',
        district_id: 'Округ',
        source_id: 'Источник',
        creator: 'Создатель',
        incoming_number: 'Номер звонящего',
        applicant_lastname: 'Фамилия заявителя',
        applicant_firstname: 'Имя заявителя',
        applicant_surname: 'Отчество заявителя',
        applicant_phone: 'Номер телефона заявителя',
    };
    return labels[key] || key;
};
const formatFilterValue = (key) => {
    const value = filterForm[key];

    if (Array.isArray(value)) {
        if (key === 'conditions') {
            return Object.values(value).map((condition) => {
                return getConditionLabel(condition)
            }).join(', ')
        }
    };
    if (typeof value === 'boolean') return 'Да';
    return value;
};
const clearSingleFilter = (key) => {
    filterForm[key] = Array.isArray(filterForm[key]) ? [] : null;
    if (typeof filterForm[key] === 'boolean') filterForm[key] = false;
    applyFilters();
};
const applyFilters = () => {
    localStorage.setItem('filter', JSON.stringify(filterForm.data()));
    isFilterOpen.value = false;
    updateTable();
};

const updateTable = () => {
    if (isFilterOpen.value === true) {
        return;
    }
    filterForm.post(route('incidents.dashboard'), {
        preserveState: true,
        preserveScroll: true,
        only: ['incidents'],
        onSuccess: () => {
            isFilterOpen.value = false;
        }
    });
}

let interval = null;

onUnmounted(() => {
    clearInterval(interval);
});

onMounted(() => {
    const saved = localStorage.getItem('filter');
    if (saved) {
        filterForm.defaults(JSON.parse(saved));
        filterForm.reset();
    }
    updateTable();
    interval = setInterval(updateTable, 5000);
});

const activeFilters = computed(() => {
    const data = filterForm.data();

    return Object.keys(data).reduce((acc, key) => {
        const value = data[key];
        const isFilled =
            value !== null &&
            value !== undefined &&
            value !== '' &&
            !(Array.isArray(value) && value.length === 0) &&
            !(typeof value === 'boolean' && value === false);

        if (isFilled) {
            acc[key] = value;
        }

        return acc;
    }, {});
});

</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>

        <template #top-content>
            <div class="flex gap-6">
                <TabPageButton
                    v-for="(data, name) in tabs"
                    :key="data.title"
                    :title="data.title"
                    @click="currentTab = name"
                    :active="currentTab === name"
                />
            </div>
            <div class="flex justify-between">
                <div class="flex gap-12">
                    <span class="text-lg">Карточки происшествий</span>
                    <LinkButton
                        :href="route('incidents.create-instant')"
                    >
                    <span class="mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                               <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                             </svg>
                        </span>
                        Создать карточку
                    </LinkButton>
                    <SecondaryButton @click="activateAlarm()" >Принять вызов</SecondaryButton>
                </div>
                <div class="flex gap-6">
                    <FilterButton
                        :title="'Фильтры'"
                        :icon="'filter'"
                        @click="isFilterOpen = true"
                    />
                    <FilterButton
                        :title="'Столбцы для отображения'"
                        :icon="'cols'"
                        @click="showModal('cols')"
                    />
                </div>
            </div>
            <div class="py-4 flex gap-4">
                <ActiveFilterButton
                    v-for="(value, key) in activeFilters"
                    :key="key"
                    @remove="clearSingleFilter(key)"
                >
                    {{getFilterLabel(key)}}: {{formatFilterValue(key)}}
                </ActiveFilterButton>
            </div>
        </template>
        <template #main-tabs>
            <ModeChanger/>
        </template>

        <IncidentsTable/>
        <FilterModal :show="isFilterOpen" @close="isFilterOpen = false" :form="filterForm" @submit="applyFilters" @reset="applyFilters"/>

        <template #right-panel>
            <IncidentPreview></IncidentPreview>
        </template>

        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showAlarmOverlay"
                     class="fixed inset-0 z-[9999] bg-black/90 flex flex-col items-center justify-center backdrop-blur-sm"
                >
                    <div class="relative">
                        <div class="absolute inset-0 bg-red-600 rounded-full animate-ping opacity-25"></div>

                        <button
                            @click="acceptCall"
                            class="relative w-64 h-64 bg-red-600 hover:bg-red-500 text-white rounded-full
                               shadow-[0_0_50px_rgba(220,38,38,0.5)] border-8 border-white/20
                               flex items-center justify-center text-3xl font-black uppercase tracking-tighter
                               transition-transform active:scale-95"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                 class="size-24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                            </svg>

                        </button>
                    </div>

                    <p class="mt-12 text-white animate-pulse font-mono text-xl tracking-widest uppercase">
                        Принять
                    </p>
                </div>
            </Transition>
        </Teleport>

</AuthenticatedLayout>
</template>
