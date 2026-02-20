<script setup>
import FormGroup from "@/Components/Form/FormGroup.vue";
import {computed, ref} from "vue";
import TabHeaderButton from "@/Components/TabHeaderButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TabsHeader from "@/Components/TabsHeader.vue";
import EDDS_first from "@/Pages/Incidents/Partials/EDDS_first.vue";
import EDDS_second from "@/Pages/Incidents/Partials/EDDS_second.vue";
import EDDS_third from "@/Pages/Incidents/Partials/EDDS_third.vue";
import Block from "@/Components/Block.vue";
const props = defineProps(['form']);
const FIO = computed(() => {
    const info = props.form.applicant_info;
    return [info.lastname, info.firstname, info.surname]
        .filter(Boolean)
        .join(' ')
})

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
    <Block>
<FormGroup title="Информация" :cols="1">
    <div class="grid grid-cols-3 md:grid-cols-6 gap-6">
        <div class="flex flex-col">
            <span>№ карточки</span>
            <span v-text="form.id"></span>
        </div>
        <div class="flex flex-col">
            <span>ФИО</span>
            <span>{{FIO}}</span>
        </div>
        <div class="flex flex-col">
            <span>Телефон</span>
            <span v-text="form.applicant_info.phone"></span>
        </div>
        <div class="col-span-full flex flex-col">
            <span>Описание</span>
            <span v-text="form.applicant_info.additional_info"></span>
        </div>
    </div>
</FormGroup>
    </Block>
    <div class="mt-10">
        <TabsHeader>
            <TabHeaderButton
                v-for="(tab, id) in tabs"
                :active="currentTabEDDS === Number(id)"
                @click="currentTabEDDS = id"
            >{{tab.title}}</TabHeaderButton>
        </TabsHeader>
        <Block>
            <keep-alive>
                <component :is="tabs[currentTabEDDS].template" :form="form.services_info.EDDS" v-bind="$attrs"/>
            </keep-alive>

        </Block>
    </div>

</template>

<style scoped>

</style>
