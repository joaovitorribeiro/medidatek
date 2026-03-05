<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
        ? 'group relative flex w-full items-center gap-3 rounded-xl bg-[#6C5DD3] px-3 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 transition-all duration-200'
        : 'group relative flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-gray-400 transition-all duration-200 hover:bg-[#2D2D3A] hover:text-white',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
