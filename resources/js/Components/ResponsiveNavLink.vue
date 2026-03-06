<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
        ? 'block w-full rounded-xl bg-indigo-600 px-4 py-3 text-start text-base font-semibold text-white shadow-sm ring-1 ring-indigo-600/30 transition focus:outline-none'
        : 'block w-full rounded-xl px-4 py-3 text-start text-base font-medium text-slate-600 transition hover:bg-indigo-50 hover:text-slate-900 focus:outline-none',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
