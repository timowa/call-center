<script setup>

import FilterButton from "@/Components/FilterButton.vue";
import ActiveFiltersRow from "@/Components/ActiveFiltersRow.vue";
import {ref} from "vue";
import LinkButton from "@/Components/LinkButton.vue";
import FilterModal from "@/Pages/Dashboard/Incidents/Partials/FilterModal.vue";

const isFilterOpen = ref(false);
const isColsOpen = ref(false);
</script>

<template>
    <div class="pt-4">
        <div class="flex justify-between">
            <div class="flex gap-12">
                <span class="text-lg">Карточки происшествий</span>
                <LinkButton
                    :title="'Создать карточку'"
                    :icon="'plus'"
                />
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
        <FilterModal :show="isFilterOpen" @close="isFilterOpen = false"/>
    </div>

</template>
<style scoped>

</style>
