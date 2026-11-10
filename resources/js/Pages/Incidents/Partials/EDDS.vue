<script setup>
import {ref} from "vue";
import TabHeaderButton from "@/Components/TabHeaderButton.vue";
import TabsHeader from "@/Components/TabsHeader.vue";
import EDDS_first from "@/Pages/Incidents/Partials/EDDS_first.vue";
import EDDS_second from "@/Pages/Incidents/Partials/EDDS_second.vue";
import EDDS_third from "@/Pages/Incidents/Partials/EDDS_third.vue";
import Block from "@/Components/Block.vue";
const props = defineProps(['form']);
const tabs = {
    1: {
        title: 'Первичная информация',
        template: EDDS_first
    },
    2: {
        title: 'Результаты выезда',
        template: EDDS_second
    },
    3: {
        title:'Реагирование',
        template: EDDS_third
    }
};

const currentTabEDDS = ref(1);
</script>

<template>
    <div>
        <TabsHeader>
            <TabHeaderButton
                v-for="(tab, id) in tabs"
                :active="currentTabEDDS === Number(id)"
                @click="currentTabEDDS = Number(id)"
            >{{tab.title}}</TabHeaderButton>
        </TabsHeader>
        <Block>
            <keep-alive>
                <component :is="tabs[currentTabEDDS].template" :form="form.eddsReport" v-bind="$attrs"/>
            </keep-alive>

        </Block>
    </div>

</template>

<style scoped>

</style>
