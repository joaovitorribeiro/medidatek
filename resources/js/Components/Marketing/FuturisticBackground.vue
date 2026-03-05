<script setup lang="ts">
import { computed } from 'vue';
import Particles from '@tsparticles/vue3';
import { loadSlim } from '@tsparticles/slim';
import type { Engine, IOptions, RecursivePartial } from '@tsparticles/engine';

const prefersReducedMotion =
    typeof window !== 'undefined' && 'matchMedia' in window
        ? window.matchMedia('(prefers-reduced-motion: reduce)').matches
        : false;

const particlesInit = async (engine: Engine) => {
    await loadSlim(engine);
};

const options = computed<RecursivePartial<IOptions>>(() => ({
    fullScreen: { enable: false },
    fpsLimit: prefersReducedMotion ? 30 : 60,
    detectRetina: true,
    background: { color: { value: 'transparent' } },
    particles: {
        number: {
            value: prefersReducedMotion ? 32 : 70,
            density: { enable: true, area: 900 },
        },
        color: { value: ['#A5B4FC', '#22D3EE', '#34D399', '#A78BFA'] },
        shape: { type: 'circle' },
        opacity: {
            value: { min: 0.08, max: 0.28 },
            animation: prefersReducedMotion
                ? { enable: false }
                : { enable: true, speed: 0.35, minimumValue: 0.06, sync: false },
        },
        size: {
            value: { min: 1, max: 2.6 },
            animation: prefersReducedMotion ? { enable: false } : { enable: true, speed: 1, minimumValue: 1, sync: false },
        },
        links: {
            enable: true,
            distance: 140,
            color: '#93C5FD',
            opacity: 0.13,
            width: 1,
        },
        move: {
            enable: true,
            speed: prefersReducedMotion ? 0.35 : 0.75,
            direction: 'none',
            random: false,
            straight: false,
            outModes: { default: 'out' },
        },
    },
    interactivity: {
        detectsOn: 'window',
        events: {
            onHover: { enable: !prefersReducedMotion, mode: 'grab' },
            resize: { enable: true },
        },
        modes: {
            grab: {
                distance: 160,
                links: { opacity: 0.22 },
            },
        },
    },
}));
</script>

<template>
    <div class="future-canvas pointer-events-none absolute inset-0">
        <Particles id="tsparticles" :particlesInit="particlesInit" :options="options" />
        <div class="future-glow"></div>
    </div>
</template>

<style scoped>
.future-canvas {
    transform: translateZ(0);
}

.future-glow {
    position: absolute;
    inset: -10%;
    background:
        radial-gradient(900px 420px at 18% 10%, rgba(34, 211, 238, 0.10) 0%, rgba(34, 211, 238, 0) 65%),
        radial-gradient(900px 460px at 78% 18%, rgba(167, 139, 250, 0.10) 0%, rgba(167, 139, 250, 0) 62%),
        radial-gradient(1100px 520px at 50% 75%, rgba(16, 185, 129, 0.07) 0%, rgba(16, 185, 129, 0) 60%);
    mix-blend-mode: screen;
    opacity: 0.9;
}
</style>
