<script setup>
import {inject, ref} from "vue";
import ModeChangerButton from "@/Pages/Dashboard/Incidents/Components/ModeChangerButton.vue";
import IncidentsTable from "@/Pages/Dashboard/Incidents/Partials/IncidentsTable.vue";

const {setActiveMode, lastActiveMode} = inject('workMode');
const activeBtn = ref('oper');

const buttons = [
    {
        title: 'Оперативный режим',
        id: 'oper',
        click: ()  => {
            setActiveMode(lastActiveMode.value);
            activeBtn.value = 'oper';
        }
    },
    {
        title: 'Режим статистики',
        id: 'stat',
        click: ()  => {
            setActiveMode(6);
            activeBtn.value = 'stat';
        }
    }
];
</script>

<template>
<div class="w-fit bg-grey-220 rounded-sm">
    <ModeChangerButton
        v-for="button in buttons"
        :active="activeBtn === button.id"
        @click="button.click"
    >{{ button.title }}</ModeChangerButton>
</div>
</template>

<style scoped>

</style>
