<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
        ? 'group flex w-full items-center rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-3 text-sm font-semibold text-white shadow-sm ring-1 ring-indigo-500/20 transition focus:outline-none focus:ring-2 focus:ring-indigo-500/30'
        : 'group flex w-full items-center rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-white/60 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
