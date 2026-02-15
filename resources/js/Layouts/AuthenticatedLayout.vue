<script setup>


import Aside from "@/Pages/Aside.vue";
import Header from "@/Pages/Header.vue";
</script>

<template>
    <div>
        <div class="min-h-screen bg-grey-100 flex flex-col">

            <Header></Header>
            <div class="flex flex-1 overflow-hidden p-2 bg-grey-100 gap-3">
                <Aside></Aside>
                <main class="flex-1">
                    <slot />
                </main>
            </div>

        </div>
    </div>
</template>
