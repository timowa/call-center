<script setup>
import {Link} from "@inertiajs/vue3";
</script>

<template>

            <Link v-bind="$attrs" class="text-primary-light flex">
                      <slot/>
            </Link>


</template>

<style scoped>

</style>
