<script setup>
import {computed, inject, nextTick, ref} from 'vue';
import DateTimeEditor from './DateTimeEditor.vue';
import {formatDateTime} from '@/Utils/dateTime.js';
const model = defineModel();
const props = defineProps(['placeholder', 'readonly']);
const viewMode = inject('viewMode', ref(false));
const disabled = computed(() => props.readonly || viewMode.value);
const dialog = ref();
const opened = ref(false);
const open = async () => { if (disabled.value) return; opened.value = true; await nextTick(); dialog.value.showModal(); };
const close = () => { dialog.value.close(); opened.value = false; };
const save = (value) => { model.value = value; close(); };
</script>
<template>
    <div>
        <button type="button" :disabled="disabled" @click="open" class="w-full text-left px-2 py-1 text-sm rounded border border-gray-300 bg-grey-150 disabled:bg-grey-220 disabled:cursor-not-allowed">
            ▦ {{ model ? formatDateTime(model) : (placeholder || 'ДД.ММ.ГГГГ ЧЧ:ММ') }}
        </button>
        <dialog ref="dialog" class="rounded-lg p-4 w-[360px] max-w-[95vw] max-h-[90vh] overflow-auto backdrop:bg-black/30"
            @cancel.prevent="close" @keydown.esc.stop.prevent="close" @click="($event.target === dialog) && close()">
            <DateTimeEditor v-if="opened" :value="model || null" @cancel="close" @save="save" />
        </dialog>
    </div>
</template>
