<script setup>
import {computed} from "vue";

const props = defineProps({
    title: String,
    cols: {
        type: Number,
        default: 2
    }
});
const gridCols = {
    2: 'grid-cols-2',
    3: 'grid-cols-3',
    4: 'grid-cols-4',
    6: 'grid-cols-6',
}

</script>

<template>
    <div class="mb-6">
        <h4 class="text-sm font-bold text-grey-400  tracking-wide mb-3">
            {{ title }}
        </h4>
        <div class="grid gap-x-6 gap-y-2" :class="gridCols[cols]">
            <slot />
        </div>
    </div>
</template>
