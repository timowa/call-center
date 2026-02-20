<script setup>
import {inject, ref} from "vue";
import TabsHeader from "@/Components/TabsHeader.vue";
import TabHeaderButton from "@/Components/TabHeaderButton.vue";

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
    <TabsHeader>
        <TabHeaderButton  v-for="button in buttons"
                          :active="activeBtn === button.id"
                          @click="button.click">
            {{ button.title }}
        </TabHeaderButton>
    </TabsHeader>
</template>

<style scoped>

</style>
