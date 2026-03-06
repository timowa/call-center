<script setup>

import Block from "@/Components/Block.vue";
import TabHeaderButton from "@/Components/TabHeaderButton.vue";
import TabsHeader from "@/Components/TabsHeader.vue";
import {computed, onMounted, ref} from "vue";
import Firefighters_first from "@/Pages/Incidents/Partials/Firefighters_first.vue";
import Firefighters_second from "@/Pages/Incidents/Partials/Firefighters_second.vue";
import Firefighters_third from "@/Pages/Incidents/Partials/Firefighters_third.vue";
import Firefighters_forth from "@/Pages/Incidents/Partials/Firefighters_forth.vue";
import axios  from "axios";
import {usePage} from "@inertiajs/vue3";

const page = usePage();
const user = computed(() => page.props.auth.user);
const props = defineProps(['form']);
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
    const fireReportId = props.form.fireReport.id;
    if (fireReportId === null) {
        return;
    }
    if (user.value.roles.includes('op_01')) {
        axios.put(route('fireReport.set-status-connected', fireReportId));
        return;
    }
})
</script>

<template>
    <TabsHeader>
        <TabHeaderButton
            v-for="(tab, id) in tabs"
            :active="currentTabFire == id"
            @click="currentTabFire = id"
        >{{tab.title}}</TabHeaderButton>
    </TabsHeader>
    <Block>
        <keep-alive>
            <component :is="tabs[currentTabFire].template" :form="form.fireReport" v-bind="$attrs"/>
        </keep-alive>

    </Block>
</template>

<style scoped>

</style>
