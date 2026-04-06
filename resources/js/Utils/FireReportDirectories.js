import { reactive } from 'vue';
import axios from 'axios';

export const FireReportDirectories = reactive({
    fireService: null,
    isLoading: false,

    async fetchFireDirectories() {
        if (this.fireService || this.isLoading) return;

        this.isLoading = true;
        try {
            const response = await axios.get(route('fire.get-directories'));
            this.fireService = response.data;
        } catch (e) {
            console.error("Ошибка загрузки справочников ПЧ:", e);
        } finally {
            this.isLoading = false;
        }
    }
});
