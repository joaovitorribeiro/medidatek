<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    title: string;
    content: string;
    updated_at: string | null;
}>();

const updatedLabel = computed(() => {
    if (!props.updated_at) return '';
    const d = new Date(props.updated_at);
    if (Number.isNaN(d.getTime())) return '';
    return d.toLocaleDateString('pt-BR');
});

const canonicalUrl = computed(() => (typeof window !== 'undefined' ? `${window.location.origin}${window.location.pathname}` : ''));
const ogImageUrl = computed(() => (typeof window !== 'undefined' ? `${window.location.origin}/og/medidatek.svg` : '/og/medidatek.svg'));
</script>

<template>
    <Head :title="title">
        <meta name="description" :content="`${title} da MedidaTek.`" />
        <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1" />

        <link rel="canonical" :href="canonicalUrl" />

        <meta property="og:site_name" content="MedidaTek" />
        <meta property="og:title" :content="`${title} - MedidaTek`" />
        <meta property="og:description" :content="`${title} da MedidaTek.`" />
        <meta property="og:type" content="article" />
        <meta property="og:locale" content="pt_BR" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta property="og:image" :content="ogImageUrl" />
    </Head>

    <div class="legal-universe min-h-screen bg-[#030305] text-white selection:bg-indigo-500/30 selection:text-indigo-200">
        <div class="aurora-bg fixed inset-0 z-0 pointer-events-none">
            <div class="aurora-orb orb-1"></div>
            <div class="aurora-orb orb-2"></div>
            <div class="aurora-orb orb-3"></div>
            <div class="noise-overlay"></div>
        </div>

        <div class="relative z-10 px-4 py-12">
            <div class="mx-auto max-w-3xl">
                <div class="flex items-center justify-between gap-4">
                    <Link href="/" class="text-sm font-semibold text-white/70 hover:text-white transition-colors">
                        Voltar
                    </Link>
                    <div v-if="updatedLabel" class="text-xs text-white/40">
                        Atualizado em {{ updatedLabel }}
                    </div>
                </div>

                <h1 class="mt-8 text-4xl font-medium tracking-tight">
                    {{ title }}
                </h1>

                <div class="mt-8 rounded-3xl border border-white/10 bg-white/5 px-6 py-6 backdrop-blur-xl">
                    <pre class="whitespace-pre-wrap text-sm leading-relaxed text-white/80">{{ content }}</pre>
                </div>

                <div class="mt-8 text-xs text-white/30">
                    © {{ new Date().getFullYear() }} MedidaTek. Todos os direitos reservados.
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.legal-universe {
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
