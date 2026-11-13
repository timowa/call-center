<script setup>
import DepartmentResponse from '@/Pages/Incidents/Partials/Firefighters_forth.vue';

defineProps(['form']);
</script>

<template>
    <DepartmentResponse :form="form" edds />
</template>
