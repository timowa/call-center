<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import LoginNotification from "@/Components/LoginNotification.vue";
import { computed } from 'vue';
const page = usePage();

const hasErrors = computed(() => {
    return Object.keys(page.props.errors).length > 0;
});
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-grey-100 pt-6 sm:justify-center sm:pt-0"
    >

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg bg-[#efebe9]"
        >
            <slot />
        </div>
        <div class="mt-4 w-full sm:max-w-md">

            <LoginNotification
                message="Установлено соединение"
                type="success"
            />

            <LoginNotification
                v-if="hasErrors"
                v-for="(error, key) in $page.props.errors"
                :key="key"
                :message="error"
                type="error"
            />

        </div>
    </div>
</template>
