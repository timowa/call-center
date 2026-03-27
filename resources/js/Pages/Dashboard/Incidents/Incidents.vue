<script setup>

import {provide, ref} from "vue";
import FilterModal from "@/Pages/Dashboard/Incidents/Partials/FilterModal.vue";
import IncidentsTable from "@/Pages/Dashboard/Incidents/Partials/IncidentsTable.vue";

const isFilterOpen = ref(false);
const isColsOpen = ref(false);
</script>

<template>
    <div class="w-full">
        <IncidentsTable/>
        <FilterModal :show="isFilterOpen" @close="isFilterOpen = false"/>
    </div>

</template>
<style scoped>

</style>
