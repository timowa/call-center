import { ref } from 'vue';
import {getGeo} from "@/Utils/dadata.js";
const geocode = getGeo()
export function addressSearch() {
    const options = ref([]);
    let timeout = null;

    const fetchStreets = async (areaName, districtName, search = '') => {
        try {
            const data = await geocode.getStreets(areaName, districtName, search);
            options.value = data.map(item => ({
                id: item.id,
                name: item.name
            }));
        } catch (e) {
            console.error(e)
            options.value = [];
        }
    };

    const onSearch = (search, areaName, districtName) => {
        clearTimeout(timeout);
        if (!search) {
            fetchStreets(areaName, districtName);
            return;
        }
        if (search.length < 3) return;

        timeout = setTimeout(() => {
            fetchStreets(areaName, districtName, search);
        }, 450);
    };

    return {
        options,
        onSearch,
        loadInitial: fetchStreets
    };
}
