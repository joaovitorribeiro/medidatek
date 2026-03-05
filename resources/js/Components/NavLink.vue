<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
        ? 'group relative flex w-full items-center gap-3 rounded-2xl bg-gradient-to-r from-indigo-600/20 to-indigo-600/5 px-4 py-3.5 text-sm font-semibold text-white shadow-[0_0_20px_rgba(79,70,229,0.15)] ring-1 ring-inset ring-indigo-500/30 transition-all duration-300 overflow-hidden'
        : 'group relative flex w-full items-center gap-3 rounded-2xl px-4 py-3.5 text-sm font-medium text-white/50 transition-all duration-300 hover:bg-white/5 hover:text-white hover:shadow-lg hover:shadow-indigo-500/5',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <div v-if="active" class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-transparent opacity-50"></div>
        <div v-if="active" class="absolute left-0 top-1/2 h-8 w-1 -translate-y-1/2 rounded-r-full bg-indigo-400 shadow-[0_0_10px_#818cf8]"></div>
        <slot />
    </Link>
</template>