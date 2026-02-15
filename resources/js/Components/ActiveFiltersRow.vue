<script setup>
import {ref} from "vue";
import ActiveFilterButton from "@/Components/ActiveFilterButton.vue";

const activeFilters = ref([
    {key: 'district', label: 'Район: Сорск г.'},
    {key: 'period', label: 'Период: за 24 часа'}
])

const removeFilter = (key) => {
    activeFilters.value = activeFilters.value.filter(f => f.key !== key)
}
</script>

<template>
<div class="py-4 flex gap-4">
<ActiveFilterButton
    v-for="filter in activeFilters"
    :label="filter.label"
    :key="filter.key"
    @remove="removeFilter(filter.key)"
/>
</div>
</template>

<style scoped>

</style>
