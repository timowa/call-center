import { reactive } from 'vue';
import axios from 'axios';

export const FireReportDirectories = reactive({
    fireService: null,
    isLoading: false,
    error: null,

    async fetchFireDirectories() {
        if (this.fireService || this.isLoading) return;

        this.isLoading = true;
        this.error = null;
        try {
            const response = await axios.get(route('fire.get-directories'));
            this.fireService = response.data;
        } catch (e) {
            this.error = 'Не удалось загрузить справочники ПЧ.';
        } finally {
            this.isLoading = false;
        }
    }
});
