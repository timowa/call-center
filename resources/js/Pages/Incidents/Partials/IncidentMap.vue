<script setup>
import { YandexMap, YandexMapDefaultFeaturesLayer, YandexMapDefaultSchemeLayer, YandexMapMarker } from 'vue-yandex-maps';
import { computed, inject } from 'vue';
const injected = inject('mapCoordinates', null);

const props = defineProps({
    zoom: { type: Number, default: 15 }
});

const longitude = computed(() => {
    const val = injected?.data?.value?.longitude;
    return val ? parseFloat(val) : null;
});

const latitude = computed(() => {
    const val = injected?.data?.value?.latitude;
    return val ? parseFloat(val) : null;
});
const mapSettings = computed(() => {
    return {
        location: {
            center: [longitude.value, latitude.value],
            zoom: 15,
        },
    };
});
console.log(mapSettings.value)
</script>

<template>
    <div class="w-full h-full border border-grey-300 rounded-md overflow-hidden bg-grey-100">
        <yandex-map
            v-if="latitude && longitude"
            :settings="mapSettings"
            width="100%"
            height="100%"
        >
            <yandex-map-default-scheme-layer />
            <yandex-map-default-features-layer />

            <yandex-map-marker :settings="{ coordinates: [longitude, latitude] }">
                <div class="marker-container">
                    <div class="w-6 h-6 bg-red-600 rounded-full border-2 border-white shadow-lg"></div>
                </div>
            </yandex-map-marker>
        </yandex-map>

        <div v-else class="flex items-center justify-center h-full text-grey-400">
            Выберите инцидент с координатами для просмотра карты
        </div>
    </div>
</template>

<style scoped>
.marker {
    position: relative;
    transform: translate(-50%, -50%);
}
</style>
