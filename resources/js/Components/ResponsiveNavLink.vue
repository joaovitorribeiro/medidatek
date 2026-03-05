<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
        ? 'block w-full rounded-xl bg-[#6C5DD3] px-4 py-3 text-start text-base font-semibold text-white shadow-lg shadow-indigo-500/20 transition focus:outline-none'
        : 'block w-full rounded-xl px-4 py-3 text-start text-base font-medium text-gray-400 transition hover:bg-[#2D2D3A] hover:text-white focus:outline-none',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
