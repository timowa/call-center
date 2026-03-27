import axios from "axios";
import {router} from "@inertiajs/vue3";

export function getGeo(area_id, district_id, street, house_number, corpus_number) {
    const getStreets = async (area_id, district_id, street) => {
        const query = {
            area_id: area_id,
            district_id: district_id,
            street: street
        };
        try {
            const response = await axios.post(
                route('suggestions.addresses'),
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

    const getCoordinates = async (area_id, district_id, street, house_number = 0, corpus_number = 0) => {
        const query = {
            area_id: area_id,
            district_id: district_id,
            street: street,
            house_number: house_number,
            corpus_number: corpus_number
        }

        try {
            const response = await axios.post(
                route('suggestions.coordinates'),
                {
                    query: query
                }
            );
            return response.data
        } catch (error) {
            console.error('Ошибка получения координат', error)
            return '';
        }

    }

    return {
        getStreets,
        getCoordinates
    }
}
