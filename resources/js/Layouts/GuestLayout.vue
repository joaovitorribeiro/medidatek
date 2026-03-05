<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <div class="guest-universe relative min-h-screen bg-[#030305] text-white selection:bg-indigo-500/30 selection:text-indigo-200">
        <div class="aurora-bg fixed inset-0 z-0 pointer-events-none">
            <div class="aurora-orb orb-1"></div>
            <div class="aurora-orb orb-2"></div>
            <div class="aurora-orb orb-3"></div>
            <div class="noise-overlay"></div>
        </div>

        <div class="relative z-10 flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <Link href="/" class="mb-8 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-6 py-3 text-xs font-semibold tracking-widest text-white/90 backdrop-blur hover:bg-white/10 transition-colors">
                <ApplicationLogo />
            </Link>

            <div class="w-full max-w-md overflow-hidden rounded-3xl border border-white/10 bg-white/5 px-6 py-6 shadow-2xl backdrop-blur-xl">
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
.guest-universe {
    overflow-x: hidden;
}

.aurora-bg {
    background: radial-gradient(circle at 50% 0%, #1a1a2e 0%, #000000 100%);
}

.aurora-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.4;
    animation: float 20s infinite ease-in-out;
}

.orb-1 {
    top: -10%;
    left: 20%;
    width: 600px;
    height: 600px;
    background: #4f46e5;
    animation-delay: 0s;
}

.orb-2 {
    top: 20%;
    right: 10%;
    width: 500px;
    height: 500px;
    background: #06b6d4;
    animation-delay: -5s;
}

.orb-3 {
    bottom: -10%;
    left: 30%;
    width: 700px;
    height: 700px;
    background: #10b981;
    opacity: 0.2;
    animation-delay: -10s;
}

.noise-overlay {
    position: absolute;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.05'/%3E%3C/svg%3E");
    opacity: 0.4;
    mix-blend-mode: overlay;
}

@keyframes float {
    0%,
    100% {
        transform: translate(0, 0);
    }
    33% {
        transform: translate(30px, -50px);
    }
    66% {
        transform: translate(-20px, 20px);
    }
}
</style>
