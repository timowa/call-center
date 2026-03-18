<script setup>
import {computed, inject, ref} from "vue";
import {getConditionColor, getConditionLabel} from "@/Utils/conditions.js";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {router} from "@inertiajs/vue3";
import Block from "@/Components/Block.vue";

const injected = inject('selectedIncident', null);

const selectedIncident = computed(() => {
    return injected?.data?.value || null;
});
const previewData = computed(() => {
    if (selectedIncident.value === null) {
        return [];
    }
    return [
        {'title': 'Дата', 'ref': selectedIncident.value.datetime},
        {'title': 'Создатель', 'ref': selectedIncident.value.creator},
        {'title': 'ФИО оператора', 'ref': selectedIncident.value.operator},
        {'title': 'УКИО', 'ref': selectedIncident.value.id},
        {'title': 'Состояние', 'ref': selectedIncident.value.condition, key: 'condition'},
        {'title': 'Источник', 'ref': selectedIncident.value.source},
        {'title': '01', 'ref': selectedIncident.value.fireReport?.condition, key: 'condition'},
        {'title': '02', 'ref': ''},
        {'title': '03', 'ref': ''},
        {'title': '04', 'ref': ''},
        {'title': 'АТ', 'ref': ''},
        {'title': 'ЕЖС', 'ref': ''},
        {'title': 'Тип вызова', 'ref': selectedIncident.value.call_type},
        {'title': 'Номер звонящего', 'ref': selectedIncident.value.incoming_number},
        {'title': 'Набранный номер', 'ref': selectedIncident.value.dialed_number},
        {'title': 'Район', 'ref': ''},
        {'title': 'Округ', 'ref': ''},
        {'title': 'Улица происшествия', 'ref': ''},
        {'title': 'Район города', 'ref': ''},
        {'title': 'Фамилия звонившего', 'ref': selectedIncident.value.applicant_info?.lastname},
        {'title': 'Контакный телефон', 'ref': selectedIncident.value.applicant_info?.phone},
        {'title': 'Дата рождения', 'ref': selectedIncident.value.applicant_info?.birthday},
        {'title': 'Описание происшествия', 'ref': selectedIncident.value.description},
    ]
});
const openWindow = function(url) {
    window.open(url, '_blank')
}
</script>

<template>
    <Block>
        <div>
            <span class="text-lg">Предпросмотр карточки</span>
        </div>
        <div class="pt-6">
            <div
                v-for="data in previewData"
                class="grid grid-cols-2">
                <div class="text-grey-350 text-sm">{{ data.title }}</div>
                <div v-if="data.key === 'condition'">
                    <div class="flex items-center gap-2 text-sm">
                        <div :class="['w-2 h-2 rounded-sm', getConditionColor(data.ref)]"></div>
                        {{ getConditionLabel(data.ref) }}
                    </div>
                </div>
                <div class="text-sm" v-else>{{ data.ref }}</div>
            </div>
            <div v-if="selectedIncident !== null" class="mt-6">
                <div class="grid grid-cols-4 gap-3">
                    <SecondaryButton class="col-span-3"
                                     @click="openWindow(route('incidents.edit', selectedIncident.id))"
                    >
                        <span class="text-nowrap overflow-hidden">Открыть в отдельной вкладке</span>
                    </SecondaryButton>
                    <PrimaryButton class="text-nowrap overflow-hidden"
                                   @click="router.get(route('incidents.edit', selectedIncident.id))"><span>Открыть</span></PrimaryButton>
                </div>
            </div>
        </div>
    </Block>
</template>

<style scoped>

</style>
