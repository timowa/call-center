<script setup>
import {computed, ref} from 'vue';
import {usePage} from '@inertiajs/vue3';
import {nowInTimezone, parseDateTime} from '@/Utils/dateTime.js';
const props = defineProps({value: String, required: Boolean});
const emit = defineEmits(['save', 'cancel']);
const today = nowInTimezone(usePage().props.timezone).slice(0, 10);
const initial = props.value?.replace('T', ' ');
const date = ref(initial ? initial.slice(0, 10).split('-').reverse().join('.') : '');
const initialTime = initial?.slice(11, 19) ?? '';
const time = ref(initialTime.length === 5 ? initialTime + ':00' : initialTime);
const year = ref(Number((initial || today).slice(0, 4)));
const month = ref(Number((initial || today).slice(5, 7)) - 1);
const months = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
const days = computed(() => {
    const offset = (new Date(year.value, month.value, 1).getDay() + 6) % 7;
    return Array.from({length: 42}, (_, i) => {
        const d = new Date(year.value, month.value, i - offset + 1);
        const iso = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
        return {iso, label: d.getDate(), currentMonth: d.getMonth() === month.value};
    });
});
const attempted = ref(false);
const empty = computed(() => !date.value && !time.value);
const value = computed(() => parseDateTime(date.value, time.value));
const invalid = computed(() => !value.value && !(empty.value && !props.required));
const shift = (delta) => {
    const d = new Date(year.value, month.value + delta, 1);
    year.value = d.getFullYear(); month.value = d.getMonth();
};
const choose = (day) => { date.value = day.iso.split('-').reverse().join('.'); };
const save = () => { attempted.value = true; if (!invalid.value) emit('save', value.value); };
</script>

<template>
    <div class="date-editor" @keydown.enter.prevent.stop="save">
        <div class="flex gap-3 mb-3">
            <label class="flex-1 min-w-0">Дата ▦
                <input aria-label="Дата" v-model="date" placeholder="ДД.ММ.ГГГГ" :aria-invalid="attempted && invalid" class="w-full bg-gray-100 rounded border-gray-300 text-sm" />
            </label>
            <label class="flex-1 min-w-0">Время ◷
                <input aria-label="Время" v-model="time" type="time" step="1" :aria-invalid="attempted && invalid" class="w-full bg-gray-100 rounded border-gray-300 text-sm" />
            </label>
        </div>
        <div class="flex items-center justify-between gap-1 mb-2">
            <button type="button" aria-label="Предыдущий месяц" @click="shift(-1)">‹</button>
            <select aria-label="Месяц" v-model="month" class="border-0 text-sm"><option v-for="(name,i) in months" :value="i">{{ name }}</option></select>
            <input aria-label="Год" type="number" min="1000" max="9999" v-model.number="year" class="w-24 border-0 text-sm" />
            <button type="button" aria-label="Следующий месяц" @click="shift(1)">›</button>
        </div>
        <div class="grid grid-cols-7 text-center gap-1">
            <span v-for="day in ['Пн','Вт','Ср','Чт','Пт','Сб','Вс']" class="text-gray-500 text-xs py-1">{{ day }}</span>
            <button v-for="day in days" :key="day.iso" type="button" @click="choose(day)" :aria-label="day.iso"
                class="relative rounded py-2 text-sm hover:bg-blue-100"
                :class="[day.iso.split('-').reverse().join('.') === date ? 'bg-blue-500 text-white' : '', !day.currentMonth ? 'text-gray-400' : '']">
                {{ day.label }}<span v-if="day.iso === today" class="absolute top-1 right-1 w-1 h-1 bg-green-500 rounded-full" />
            </button>
        </div>
        <p v-if="attempted && invalid" role="alert" class="text-red-600 text-sm mt-2">Укажите корректные дату и время.</p>
        <div class="flex justify-end gap-3 mt-4 border-t pt-3">
            <button type="button" class="mr-auto text-gray-500" aria-label="Стереть" @click="date = ''; time = ''; attempted = true">⌫</button>
            <button type="button" class="text-primary" @click="emit('cancel')">Отмена</button>
            <button type="button" class="bg-primary text-white rounded px-3 py-2" @click="save">Сохранить</button>
        </div>
    </div>
</template>

<style scoped>
input[aria-invalid="true"] { border-color: #dc2626; }
</style>
