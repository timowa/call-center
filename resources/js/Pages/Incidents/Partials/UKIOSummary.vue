<script setup>
import {computed, ref, watch} from 'vue';
import Block from '@/Components/Block.vue';
import TextInput from '@/Components/Form/TextInput.vue';

const props = defineProps({form: {type: Object, required: true}, canEditPhone: Boolean});
const name = computed(() => ['lastname', 'firstname', 'surname']
    .map(key => String(props.form.applicant_info?.[key] ?? '').trim()).filter(Boolean).join(' ') || 'Не указано');
const editing = ref(false);
const draft = ref('');
function editPhone() {
    if (!props.canEditPhone) return;
    if (editing.value) {
        props.form.applicant_info ??= {};
        props.form.applicant_info.phone = draft.value;
        editing.value = false;
    } else {
        draft.value = props.form.applicant_info?.phone ?? '';
        editing.value = true;
    }
}
watch(() => props.canEditPhone, allowed => { if (!allowed) editing.value = false; });
</script>

<template>
    <Block>
        <h4 class="text-sm font-bold text-grey-400 tracking-wide mb-3">Информация</h4>
        <dl class="flex flex-wrap gap-x-6 gap-y-4 text-13">
            <div><dt class="text-grey-350">№ карточки</dt><dd>{{ form.id ?? 0 }}</dd></div>
            <div class="min-w-0 max-w-full"><dt class="text-grey-350">ФИО заявителя</dt><dd class="break-words whitespace-pre-wrap">{{ name }}</dd></div>
            <div class="min-w-0 max-w-full">
                <dt class="text-grey-350">Телефон заявителя</dt>
                <dd class="flex items-center gap-2">
                    <TextInput v-if="editing && canEditPhone" v-model="draft" aria-label="Телефон заявителя"
                               class="min-w-0 w-44" @keydown.enter.prevent.stop @keydown.escape.prevent="editing = false" />
                    <span v-else class="break-all">{{ form.applicant_info?.phone || 'Не указано' }}</span>
                    <button v-if="canEditPhone" type="button" class="text-blue-500 p-1"
                            :aria-label="editing ? 'Подтвердить телефон' : 'Изменить телефон'" @click="editPhone">
                        <svg aria-hidden="true" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path v-if="editing" d="m5 12 4 4L19 6" />
                            <path v-else d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 3.1 5.2 2 2 0 0 1 5.1 3h3l2 5-2 2a16 16 0 0 0 6 6l2-2z" />
                        </svg>
                    </button>
                </dd>
            </div>
            <div class="w-full min-w-0"><dt class="text-grey-350">Описание</dt><dd class="whitespace-pre-wrap break-words">{{ form.description ?? '' }}</dd></div>
        </dl>
        <slot />
    </Block>
</template>
