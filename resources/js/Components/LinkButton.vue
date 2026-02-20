<script setup>
import {Link} from "@inertiajs/vue3";

const props = defineProps(['title', 'icon']);
const icons = {
    plus: '  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">\n' +
        '                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />\n' +
        '                        </svg>'
}
</script>

<template>
    <button>
        <Link v-bind="$attrs" class="text-primary-light flex">
                    <span class="mr-3" v-if="icon && icons[icon]" v-html="icons[icon]"></span>
            {{ title }}
        </Link>
    </button>
</template>

<style scoped>

</style>
