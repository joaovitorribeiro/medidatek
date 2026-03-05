<template>
    <div class="inline-flex items-center gap-2 select-none group">
        <!-- Icon/Mark -->
        <div class="relative flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-500/10 ring-1 ring-inset ring-indigo-500/20 group-hover:bg-indigo-500/20 group-hover:ring-indigo-500/40 transition-all duration-300">
            <!-- Tech Pulse -->
            <div class="absolute inset-0 rounded-lg bg-indigo-500/20 blur opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            
            <!-- Logo Shape (Abstract M/T) -->
            <svg class="w-5 h-5 text-indigo-500 group-hover:text-indigo-400 transition-colors relative z-10" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 6V18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                <path d="M20 6V18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                <path d="M4 6L12 13L20 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="17" r="1.5" fill="currentColor" class="group-hover:animate-pulse"/>
            </svg>
        </div>

        <!-- Text -->
        <div class="flex flex-col leading-none" v-if="!iconOnly">
            <span class="text-sm font-bold tracking-tight text-white group-hover:text-indigo-100 transition-colors">
                Medida<span class="text-indigo-400">Tek</span>
            </span>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    iconOnly?: boolean;
}>();
</script>
