<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ?
        'bg-white text-primary border-primary font-semibold'
        :
        'text-grey-200 border-grey-250 text-grey-250',
);
</script>

<template>
    <Link :href="href" :class="classes" class="text-xl p-2 mr-2 rounded-md border border-solid h-auto">
        <slot />
    </Link>
</template>
