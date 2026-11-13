<script setup>
import {ref, computed, inject, provide} from "vue";
import TabHeaderButton from "@/Components/TabHeaderButton.vue";
import TabsHeader from "@/Components/TabsHeader.vue";
import EDDS_first from "@/Pages/Incidents/Partials/EDDS_first.vue";
import EDDS_second from "@/Pages/Incidents/Partials/EDDS_second.vue";
import EDDS_third from "@/Pages/Incidents/Partials/EDDS_third.vue";
import Block from "@/Components/Block.vue";
import {userHasPermissionTo} from '@/Utils/permissions.js';
import PrimaryButton from '@/Components/PrimaryButton.vue';
const props = defineProps(['form']);
const parentView = inject('viewMode', ref(false));
const readOnly = computed(() => parentView.value || !userHasPermissionTo(props.form.eddsReport.id ? 'edds.edit' : 'edds.create'));
provide('viewMode', readOnly);
const finish = inject('finishService');
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
            <button type="button" disabled class="text-gray-400 mb-3">Отделения</button>
            <keep-alive>
                <component :is="tabs[currentTabEDDS].template" :form="form.eddsReport" v-bind="$attrs"/>
            </keep-alive>

            <div class="flex justify-end mt-4"><PrimaryButton v-if="form.eddsReport.id && form.eddsReport.condition !== 5 && !readOnly" :disabled="form.processing" @click="finish('edds')">Завершить карточку ЕДДС/ЖКХ</PrimaryButton></div>
        </Block>
    </div>

</template>

<style scoped>

</style>
