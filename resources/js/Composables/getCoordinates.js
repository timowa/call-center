import { ref } from 'vue';
import {getGeo} from "@/Utils/dadata.js";
const geocode = getGeo();
export function fetchCoordinates(area_id, district_id, street, house_number, corpus_number) {
    const coordinates = ref([]);
    const fetch = async (area_id, district_id, street, house_number = 0, corpus_number = 0) => {
        if (!area_id || !district_id || !street || !house_number) {
            return;
        }
        try {
            const response = await geocode.getCoordinates(area_id, district_id, street, house_number, corpus_number);
            coordinates.value = response || null;
        } catch (error) {
            coordinates.value = null;
        }
    }


    return {
        coordinates,
        fetch
    };
}
