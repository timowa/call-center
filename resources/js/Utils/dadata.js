import axios from "axios";
import {router} from "@inertiajs/vue3";

export async function getStreets(area_id, district_id, street) {

    // Формируем строку запроса
    const query = {
        area_id: area_id,
        district_id: district_id,
        street: street
    };

    try {
        const response = await axios.post(
            route('suggestions.clean'),
            {
                query: query
            }
        );

        return response.data.map((data) => {
            return {
                id: data.data.street_with_type,
                name: data.data.street_with_type
            }
        }) || [];
    } catch (error) {
        console.error("Ошибка при получении улиц:", error);
        return [];
    }
}
