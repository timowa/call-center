import {ref} from "vue";

export const conditions = {
    1: {color: 'bg-red-500', name: 'Запрос в 112'},
    2: {color: 'bg-green-500', name: 'Подключение'},
    3: {color: 'bg-indigo-500', name: 'Реагирование'},
    4: {color: 'bg-yellow-500', name: 'В работе'},
    5: {color: 'bg-grey-370', name: 'Отработана'},
    6: {color: 'bg-grey-370', name: 'Просмотр'},
};
export const getCondition = (id) => {
    return conditions[id] || { color: 'bg-gray-400', name: 'Неизвестно' };
};

/**
 * Получить только имя (твой исходный запрос)
 */
export const getConditionLabel = (id) => getCondition(id).name;

/**
 * Получить только цвет
 */
export const getConditionColor = (id) => getCondition(id).color;
