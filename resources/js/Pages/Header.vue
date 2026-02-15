<script setup>

import TimerWidget from "@/Components/TimerWidget.vue";
import NavLink from "@/Components/NavLink.vue";
</script>

<template>
    <header
        class="bg-primary shadow flex justify-between"
    >
        <div class="px-4 py-2 sm:px-6 lg:px-8 flex justify-between w-full">
            <timer-widget></timer-widget>
            <div class="px-4 py-2 flex align-center h-full">
                <nav-link href="/" :active="true" >Основной</nav-link>
                <nav-link href="/" :active="false" >Первый</nav-link>
                <nav-link href="/" :active="false" >Второй</nav-link>
            </div>
            <div class="px-4 py-2 flex align-center">
                <div class="flex flex-col justify-center mr-6">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" class="size-6">
                        <path d="M8.25 4.5a3.75 3.75 0 1 1 7.5 0v8.25a3.75 3.75 0 1 1-7.5 0V4.5Z" />
                        <path d="M6 10.5a.75.75 0 0 1 .75.75v1.5a5.25 5.25 0 1 0 10.5 0v-1.5a.75.75 0 0 1 1.5 0v1.5a6.751 6.751 0 0 1-6 6.709v2.291h3a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1 0-1.5h3v-2.291a6.751 6.751 0 0 1-6-6.709v-1.5A.75.75 0 0 1 6 10.5Z" />
                    </svg>
                </div>
                <div class="flex justify-center items-center text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" class="size-6 mr-2">
                        <path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" clip-rule="evenodd" />
                    </svg>
                    {{$page.props.auth.user.name}}
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped>

</style>
