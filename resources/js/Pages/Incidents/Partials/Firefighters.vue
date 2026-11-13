<script setup>

import Block from "@/Components/Block.vue";
import TabHeaderButton from "@/Components/TabHeaderButton.vue";
import TabsHeader from "@/Components/TabsHeader.vue";
import {computed, onMounted, ref, inject, provide} from "vue";
import Firefighters_first from "@/Pages/Incidents/Partials/Firefighters_first.vue";
import Firefighters_second from "@/Pages/Incidents/Partials/Firefighters_second.vue";
import Firefighters_third from "@/Pages/Incidents/Partials/Firefighters_third.vue";
import Firefighters_forth from "@/Pages/Incidents/Partials/Firefighters_forth.vue";
import {FireReportDirectories} from "@/Utils/FireReportDirectories.js";
import {usePage} from "@inertiajs/vue3";

const page = usePage();
const user = computed(() => page.props.auth.user);
const props = defineProps(['form']);
const parentViewMode = inject('viewMode', ref(false));
const readOnly = computed(() => parentViewMode.value || !user.value.permissions.includes(props.form.fireReport.id ? '01.edit' : '01.create'));
provide('viewMode', readOnly);
const tabs = {
    1: {
        title: 'Первичная информация',
        template: Firefighters_first
    },
    2: {
        title: 'Тушение пожара',
        template: Firefighters_second
    },
    3: {
        title:'Результаты выезда',
        template: Firefighters_third
    },
    4: {
        title:'Реагирование ПЧ',
        template: Firefighters_forth
    }
};
const currentTabFire = ref(1);
onMounted(function() {
    FireReportDirectories.fetchFireDirectories();
    const fireReportId = props.form.fireReport.id;
    if (fireReportId === null) {
        return;
    }
})
</script>

<template>
    <TabsHeader>
        <TabHeaderButton
            v-for="(tab, id) in tabs"
            :active="currentTabFire === Number(id)"
            @click="currentTabFire = Number(id)"
        >{{tab.title}}</TabHeaderButton>
    </TabsHeader>
    <Block>
        <p v-if="FireReportDirectories.isLoading" class="p-4">Загрузка справочников…</p>
        <p v-else-if="FireReportDirectories.error" role="alert" class="p-4">{{ FireReportDirectories.error }} <button type="button" @click="FireReportDirectories.fetchFireDirectories()">Повторить</button></p>
        <keep-alive v-else-if="FireReportDirectories.fireService">
            <component :is="tabs[currentTabFire].template" :form="form.fireReport" v-bind="$attrs"/>
        </keep-alive>

    </Block>
</template>

<style scoped>

</style>
