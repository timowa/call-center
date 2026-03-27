
<script setup>
import {provide, ref} from "vue";
import IncidentPreview from "@/Pages/Dashboard/Incidents/Partials/IncidentPreview.vue";
import TabPageButton from "@/Components/TabPageButton.vue";
import {Head} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import LinkButton from "@/Components/LinkButton.vue";
import ActiveFiltersRow from "@/Components/ActiveFiltersRow.vue";
import FilterButton from "@/Components/FilterButton.vue";
import ModeChanger from "@/Pages/Dashboard/Incidents/Partials/ModeChanger.vue";
import FilterModal from "@/Pages/Dashboard/Incidents/Partials/FilterModal.vue";
import IncidentsTable from "@/Pages/Dashboard/Incidents/Partials/IncidentsTable.vue";
const props = defineProps(['incidents']);
provide('incidents', props.incidents);

const selectedIncident = ref(null);

provide('selectedIncident', {
    data: selectedIncident,
    setSelected: (val) => {
        selectedIncident.value = val
    }
});

const currentTab = ref('incidents')
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
                        :href="route('incidents.create')"
                    >
                    <span class="mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                               <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                             </svg>
                        </span>
                        Создать карточку
                    </LinkButton>
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
            <ActiveFiltersRow/>
        </template>
        <template #main-tabs>
            <ModeChanger/>
        </template>

        <IncidentsTable/>
        <FilterModal :show="isFilterOpen" @close="isFilterOpen = false"/>

        <template #right-panel>
            <IncidentPreview></IncidentPreview>
        </template>

</AuthenticatedLayout>
</template>
