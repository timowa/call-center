<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

export default {
    layout: AuthenticatedLayout
}
</script>
<script setup>
import {Head, useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import UKIO from "@/Pages/Incidents/Partials/UKIO.vue";
import EDDS from "@/Pages/Incidents/Partials/EDDS.vue";
import TabsHeader from "@/Components/TabsHeader.vue";
import TabHeaderButton from "@/Components/TabHeaderButton.vue";
const props = defineProps(['incident']);
const tabs = {
    UKIO: {
        template: UKIO,
        title: 'УКИО',
        show:true
    },
    EDDS: {
        template: EDDS,
        title: 'ЕДДС/ЖКХ',
        show: true,
    }
};
const currentTab = ref('UKIO');
const form = useForm({
    id: props.incident.id,
    created_at: {
        date: props.incident.dt.date,
        time: props.incident.dt.time
    },
    creator: props.incident.user.name,
});
</script>

<template>
    <Head title="Dashboard" />
    <div class="w-full h-full bg-grey-200 border-gray-300 border-2 rounded-md p-2">
        <TabsHeader>
            <TabHeaderButton
                v-for="(tab, id) in tabs"
                v-show="tab.show"
                :active="currentTab === id"
            >{{tab.title}}</TabHeaderButton>
        </TabsHeader>
        <div class="bg-white px-[20px] py-[10px]">
            <form action="" >
            <keep-alive>
                <component :is="tabs[currentTab].template" :form="form" v-bind="$attrs"/>
            </keep-alive>
            </form>
        </div>
    </div>
</template>

<style scoped>

</style>
