import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import {getConditionLabel} from "@/Utils/conditions.js";

export function useIncidentFilters() {
    const intervalId = ref(null);
    const page = usePage();
    const isFilterOpen = ref(false);

    const defaultFilters = {
        service_id: null,
        call_type_id: null,
        is_training: false,
        is_important: false,
        emergency_threat: false,
        period_day: false,
        period_custom: false,
        period: [],
        conditions: [],
        area_id: null,
        district_id: null,
        source_id: null,
        creator: null,
        incoming_number: null,
        applicant_lastname: null,
        applicant_firstname: null,
        applicant_surname: null,
        applicant_phone: null,
    };

    const savedFilters = JSON.parse(localStorage.getItem('filter')) || {};

    const filterForm = useForm({
        ...defaultFilters,
        ...savedFilters
    });

    const getFilterLabel = (key) => {
        const labels = {
            service_id: 'Служба',
            call_type_id: 'Тип вызова',
            is_training: 'Учебная',
            is_important: 'Важная',
            emergency_threat: 'Экстремальный',
            period_day: 'Период: 24 часа',
            period_custom: 'Указанный период',
            conditions: 'Состояния',
            area_id: 'Район',
            district_id: 'Округ',
            source_id: 'Источник',
            creator: 'Создатель',
            incoming_number: 'Номер звонящего',
            applicant_lastname: 'Фамилия заявителя',
            applicant_firstname: 'Имя заявителя',
            applicant_surname: 'Отчество заявителя',
            applicant_phone: 'Номер телефона заявителя',
        };
        return labels[key] || key;
    };

    const formatFilterValue = (key) => {
        const value = filterForm[key];
        if (!value && typeof value !== 'boolean') return '';

        if (Array.isArray(value)) {
            if (key === 'conditions') {
                return value.map(c => getConditionLabel(c)).join(', ');
            }
            return value.join(', ');
        };

        if (key === 'service_id') {
            return Object.values(page.props.dictionaries.services).find(s => s.id === value)?.name || value;
        }
        if (key === 'call_type_id') {
            return Object.values(page.props.dictionaries.callTypes).find(s => s.id === value)?.name || value;
        }

        if (typeof value === 'boolean') return 'Да';
        return value;
    };

    const updateTable = () => {

        if (isFilterOpen.value) return;

        filterForm.post(route('incidents.dashboard'), {
            preserveState: true,
            preserveScroll: true,
            only: ['incidents'],
            onSuccess: () => { isFilterOpen.value = false; },
            onFinish: () => {
                startPolling();
            }
        });
    };

    const startPolling = () => {
        stopPolling();
        intervalId.value = setTimeout(updateTable, 5000);
    };

    const stopPolling = () => {
        if (intervalId.value) {
            clearTimeout(intervalId.value);
            intervalId.value = null;
        }
    };

    const applyFilters = () => {
        localStorage.setItem('filter', JSON.stringify(filterForm.data()));
        isFilterOpen.value = false;
        updateTable();
    };

    const clearSingleFilter = (key) => {
        if (Array.isArray(defaultFilters[key])) {
            filterForm[key] = [];
        } else if (typeof defaultFilters[key] === 'boolean') {
            filterForm[key] = false;
        } else {
            filterForm[key] = null;
        }
        applyFilters();
    };

    const resetFilters = () => {
        Object.assign(filterForm, defaultFilters);
        localStorage.removeItem('filter');
        applyFilters();
    };

    const activeFilters = computed(() => {
        const data = filterForm.data();
        return Object.keys(data).reduce((acc, key) => {
            const value = data[key];
            const isFilled = value !== null && value !== undefined && value !== '' &&
                !(Array.isArray(value) && value.length === 0) &&
                !(typeof value === 'boolean' && value === false);
            if (isFilled) acc[key] = value;
            return acc;
        }, {});
    });

    onMounted(() => {
        updateTable();
    });

    return {
        isFilterOpen,
        filterForm,
        activeFilters,
        getFilterLabel,
        formatFilterValue,
        applyFilters,
        resetFilters,
        clearSingleFilter,
        updateTable,
        startPolling,
        stopPolling
    };
}
