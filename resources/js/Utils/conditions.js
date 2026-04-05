import {ref} from "vue";

import { usePage } from '@inertiajs/vue3';

const defaultConditions = {
    1: { color: 'bg-red-500', name: 'Запрос в 112' },
    2: { color: 'bg-green-500', name: 'Подключение' },
    3: { color: 'bg-indigo-500', name: 'Реагирование' },
    4: { color: 'bg-yellow-500', name: 'В работе' },
    5: { color: 'bg-grey-370', name: 'Отработана' },
    6: { color: 'bg-grey-370', name: 'Просмотр' },
};

const getDictionary = () => {
    const page = usePage();
    return page.props.dictionaries?.conditions || defaultConditions;
};

export const getCondition = (id) => {
    const dict = getDictionary();
    return dict[id] || { color: 'bg-gray-400', name: 'Неизвестно' };
};

export const getConditionLabel = (id) => getCondition(id).name;

export const getConditionColor = (id) => getCondition(id).color;
