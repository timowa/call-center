<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
export default {
    layout: AuthenticatedLayout
}
</script>
<script setup>
import { Head } from '@inertiajs/vue3';
import {ref} from "vue";
import Incidents from "@/Pages/Dashboard/Incidents/Incidents.vue";
import Reports from "@/Pages/Dashboard/Reports/Reports.vue";
import TabPageButton from "@/Components/TabPageButton.vue";

const tabs = {
    indicates: {
        template: Incidents,
        title: 'Происшествия',

    },
    reports: {
        template: Reports,
        title: 'Отчеты'
    }
};
const currentTab = ref('indicates')
</script>

<template>
    <Head title="Dashboard" />

    <div class="w-full h-full bg-grey-200 border-gray-300 border-2 rounded-md p-2">
        <div class="flex flex-row gap-3">
            <TabPageButton
                v-for="(data, name) in tabs"
                :key="data.title"
                :title="data.title"
                @click="currentTab = name"
                :active="currentTab === name"
            />
        </div>
        <div>
            <keep-alive>
                <component :is="tabs[currentTab].template" v-bind="$attrs"/>
            </keep-alive>
        </div>
    </div>

</template>
