<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
        ? 'group relative flex w-full items-center gap-3 rounded-xl bg-indigo-600 px-3 py-3 text-sm font-semibold text-white shadow-sm ring-1 ring-indigo-600/30 transition-all duration-200'
        : 'group relative flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-slate-600 transition-all duration-200 hover:bg-indigo-50 hover:text-slate-900',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
