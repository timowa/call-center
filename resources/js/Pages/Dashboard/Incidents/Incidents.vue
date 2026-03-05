<script setup>

import FilterButton from "@/Components/FilterButton.vue";
import ActiveFiltersRow from "@/Components/ActiveFiltersRow.vue";
import {ref} from "vue";
import LinkButton from "@/Components/LinkButton.vue";
import FilterModal from "@/Pages/Dashboard/Incidents/Partials/FilterModal.vue";
import IncidentsTable from "@/Pages/Dashboard/Incidents/Partials/IncidentsTable.vue";

const isFilterOpen = ref(false);
const isColsOpen = ref(false);
</script>

<template>
    <div class="pt-4">
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
        <div>
            <IncidentsTable/>
        </div>
        <FilterModal :show="isFilterOpen" @close="isFilterOpen = false"/>
    </div>

</template>
<style scoped>

</style>
