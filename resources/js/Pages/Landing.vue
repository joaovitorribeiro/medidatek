<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, defineAsyncComponent, onBeforeUnmount, onMounted, ref } from 'vue';
import FuturisticBackground from '@/Components/Marketing/FuturisticBackground.vue';

type ProofLink = {
    name: string;
    url: string;
    image_url?: string | null;
    image_src?: string | null;
    image_srcset?: string | null;
    image_sizes?: string | null;
    image_alt?: string | null;
    tag?: string;
    note?: string;
};

type BentoImage = {
    src: string;
    srcset?: string | null;
    sizes?: string | null;
    alt: string;
};

const props = defineProps<{
    proofLinks: ProofLink[];
    bentoImages: Record<string, BentoImage>;
}>();

const sentOnce = ref(false);
const step = ref<1 | 2 | 3 | 4>(1);
const year = new Date().getFullYear();
const isPerformanceMode = ref(false);
const showChatWidget = ref(false);
const clientErrors = ref<Record<string, string>>({});
const calcMode = ref<'vendas' | 'processos'>('vendas');
const calcLeads = ref(300);
const calcCloseRate = ref(8);
const calcTicket = ref(3500);
const calcHours = ref(120);
const calcHourlyCost = ref(65);
const siteOrigin = computed(() => (typeof window !== 'undefined' ? window.location.origin : ''));
const canonicalUrl = computed(() => (typeof window !== 'undefined' ? `${window.location.origin}${window.location.pathname}` : ''));
const shareTitle = 'Sistema sob medida para sua operacao | MedidaTek';
const shareDescription = 'Criamos sistemas sob medida para eliminar planilhas, automatizar processos e escalar seu negocio com mais controle.';
const ogImageUrl = computed(() => (siteOrigin.value ? `${siteOrigin.value}/og/medidatek-og.png` : '/og/medidatek-og.png'));
const jsonLd = computed(() =>
    JSON.stringify(
        {
            '@context': 'https://schema.org',
            '@graph': [
                {
                    '@type': 'Organization',
                    name: 'MedidaTek',
                    url: siteOrigin.value || undefined,
                    logo: ogImageUrl.value,
                    description: shareDescription,
                },
                {
                    '@type': 'WebSite',
                    name: 'MedidaTek',
                    url: siteOrigin.value || undefined,
                    description: shareDescription,
                },
            ],
        },
        null,
        0,
    ),
);
const integrationList = [
    'Site institucional', 'Landing page', 'E-commerce / Loja virtual', 'Marketplace',
    'Portal do cliente', 'Área de membros', 'SaaS sob medida', 'App iOS/Android (PWA)',
    'Sistema de pedidos', 'Orçamentos & propostas', 'CRM sob medida', 'ERP sob medida',
    'Painel BI / Dashboards', 'Integração com WhatsApp', 'Chatbot/IA', 'Automação de processos',
    'Integrações via API', 'Webhooks', 'Notificações (e-mail/SMS/push)',
    'Checkout & pagamentos', 'Assinaturas', 'Split de pagamento', 'PIX', 'Cartão', 'Boleto',
    'STRIPE', 'MERCADO PAGO', 'ASAAS', 'PAGSEGURO', 'IUGU', 'PAYPAL'
];

const featuredIntegrations = new Set(['Marketplace', 'Portal do cliente', 'Área de membros', 'SaaS sob medida']);
function isFeaturedIntegration(tech: string) {
    return featuredIntegrations.has(tech);
}

function proofHost(url: string) {
    try {
        return new URL(url).hostname.replace(/^www\./, '');
    } catch {
        return url;
    }
}

function normalizeImageSrc(src?: string | null): string | null {
    const value = (src ?? '').trim();
    if (!value) {
        return null;
    }

    if (/^https?:\/\//i.test(value)) {
        if (typeof window !== 'undefined' && window.location.protocol === 'https:' && value.startsWith('http://')) {
            return `https://${value.slice('http://'.length)}`;
        }
        return value;
    }

    if (value.startsWith('//')) {
        return typeof window !== 'undefined' ? `${window.location.protocol}${value}` : value;
    }

    if (value.startsWith('/')) {
        return value;
    }

    return `/${value.replace(/^\.?\//, '')}`;
}

function normalizeSrcset(srcset?: string | null): string | null {
    const value = (srcset ?? '').trim();
    if (!value) {
        return null;
    }

    const parts = value
        .split(',')
        .map((entry) => entry.trim())
        .filter(Boolean)
        .map((entry) => {
            const [rawUrl, ...descriptors] = entry.split(/\s+/);
            const normalizedUrl = normalizeImageSrc(rawUrl) ?? rawUrl;
            return [normalizedUrl, ...descriptors].join(' ');
        });

    return parts.length > 0 ? parts.join(', ') : null;
}

const proofLinks = computed(() =>
    (props.proofLinks ?? []).map((item) => ({
        ...item,
        image_src: normalizeImageSrc(item.image_src ?? item.image_url ?? null),
        image_srcset: normalizeSrcset(item.image_srcset ?? null),
        image_sizes: (item.image_sizes ?? '').trim() || null,
    })),
);

const hasProofLinks = computed(() => proofLinks.value.length > 0);
const projectMarqueeLinks = computed(() =>
    isPerformanceMode.value ? proofLinks.value : [...proofLinks.value, ...proofLinks.value],
);

function projectImageLoading(index: number): 'lazy' | 'eager' {
    if (!isPerformanceMode.value) {
        return 'lazy';
    }
    return index < 3 ? 'eager' : 'lazy';
}

function projectImageFetchPriority(index: number): 'high' | 'low' {
    return index < 2 ? 'high' : 'low';
}

function bentoImage(key: string): BentoImage {
    const image = props.bentoImages?.[key];
    if (!image) {
        return { src: '', alt: '' };
    }

    return {
        src: normalizeImageSrc(image.src) ?? '',
        srcset: normalizeSrcset(image.srcset ?? null),
        sizes: (image.sizes ?? '').trim() || null,
        alt: image.alt,
    };
}

const brl = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', maximumFractionDigits: 0 });
function formatBRL(value: number) {
    return brl.format(Math.round(value));
}

const form = useForm({
    name: '',
    email: '',
    whatsapp: '',
    company: '',
    pain: '',
    investment_range: '',
    message: '',
    contact_consent: false,
    utm_source: '',
    utm_medium: '',
    utm_campaign: '',
    landing_path: '',
    referrer: '',
});

const AiChatWidget = defineAsyncComponent(() => import('@/Components/Marketing/AiChatWidget.vue'));

let revealObserver: IntersectionObserver | null = null;
let resizeTimeout: ReturnType<typeof setTimeout> | null = null;

function refreshPerformanceFlags() {
    if (typeof window === 'undefined') {
        return;
    }

    const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ?? false;
    const mobileViewport = window.matchMedia?.('(max-width: 1024px)')?.matches ?? false;
    const saveData = (navigator as any)?.connection?.saveData === true;
    const lowCpu = typeof navigator.hardwareConcurrency === 'number' && navigator.hardwareConcurrency <= 4;
    const lowMemory = typeof (navigator as any)?.deviceMemory === 'number' && (navigator as any).deviceMemory <= 4;

    isPerformanceMode.value = reduceMotion || mobileViewport || saveData || lowCpu || lowMemory;
}

function handleResize() {
    if (resizeTimeout) {
        clearTimeout(resizeTimeout);
    }
    resizeTimeout = setTimeout(refreshPerformanceFlags, 120);
}

onMounted(() => {
    // UTMs e Tracking
    const params = new URLSearchParams(window.location.search);
    form.utm_source = params.get('utm_source') ?? '';
    form.utm_medium = params.get('utm_medium') ?? '';
    form.utm_campaign = params.get('utm_campaign') ?? '';
    form.landing_path = window.location.pathname;
    form.referrer = document.referrer ?? '';

    refreshPerformanceFlags();
    window.addEventListener('resize', handleResize, { passive: true });

    const mountChatWidget = () => {
        showChatWidget.value = true;
    };
    const idleCallback = (window as any).requestIdleCallback as ((callback: () => void, options?: { timeout: number }) => number) | undefined;
    if (typeof idleCallback === 'function') {
        idleCallback(mountChatWidget, { timeout: 2000 });
    } else {
        setTimeout(mountChatWidget, 350);
    }

    // Scroll & Reveal Animations
    const revealEls = Array.from(document.querySelectorAll('.animate-on-scroll'));
    if (isPerformanceMode.value || !('IntersectionObserver' in window)) {
        revealEls.forEach((el) => el.classList.add('in-view'));
        return;
    }

    revealObserver = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    revealObserver?.unobserve(entry.target);
                }
            }
        },
        { threshold: 0.1, rootMargin: '0px 0px -50px 0px' },
    );

    revealEls.forEach((el) => revealObserver?.observe(el));
});

onBeforeUnmount(() => {
    if (revealObserver) {
        revealObserver.disconnect();
        revealObserver = null;
    }

    window.removeEventListener('resize', handleResize);
    if (resizeTimeout) {
        clearTimeout(resizeTimeout);
        resizeTimeout = null;
    }
});

// Calculadora Lógica
const baselineRevenue = computed(() => (calcLeads.value * (calcCloseRate.value / 100)) * calcTicket.value);
const projectedRevenue = computed(() => (calcLeads.value * 1.32 * ((calcCloseRate.value * 1.18) / 100)) * calcTicket.value);
const revenueDelta = computed(() => projectedRevenue.value - baselineRevenue.value);
const baselineOpsCost = computed(() => calcHours.value * calcHourlyCost.value);
const projectedOpsCost = computed(() => baselineOpsCost.value * 0.6);
const opsDelta = computed(() => baselineOpsCost.value - projectedOpsCost.value);

// Form Wizard Logic
function setStep(next: 1 | 2 | 3 | 4) {
    step.value = next;
    clientErrors.value = {};
}
function nextStep() {
    const errors: Record<string, string> = {};
    if (step.value === 1 && !form.pain) errors.pain = 'Selecione uma prioridade.';
    if (step.value === 2) {
        if (!form.name) errors.name = 'Informe seu nome.';
        if (!form.email) errors.email = 'Informe seu e-mail.';
        if (!form.whatsapp) errors.whatsapp = 'Informe seu WhatsApp.';
        if (!form.contact_consent) errors.contact_consent = 'Aceite o contato.';
    }
    if (step.value === 4 && !form.investment_range) errors.investment_range = 'Selecione uma faixa.';
    
    clientErrors.value = errors;
    if (Object.keys(errors).length > 0) return;
    if (step.value < 4) step.value = (step.value + 1) as any;
}
function prevStep() {
    if (step.value > 1) step.value = (step.value - 1) as any;
}
function startNewLead() {
    sentOnce.value = false;
    clientErrors.value = {};
    form.reset();
    step.value = 1;
}
function submit() {
    form.post(route('leads.store'), {
        preserveScroll: true,
        onSuccess: () => {
            sentOnce.value = true;
            clientErrors.value = {};
            form.reset();
            step.value = 1;
        },
    });
}
</script>

<template>
    <Head :title="shareTitle">
        <meta name="description" :content="shareDescription" />
        <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1" />

        <link rel="canonical" :href="canonicalUrl" />
        <link rel="alternate" hreflang="pt-BR" :href="canonicalUrl" />

        <meta property="og:site_name" content="MedidaTek" />
        <meta property="og:title" :content="shareTitle" />
        <meta property="og:description" :content="shareDescription" />
        <meta property="og:type" content="website" />
        <meta property="og:locale" content="pt_BR" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta property="og:image" :content="ogImageUrl" />
        <meta property="og:image:secure_url" :content="ogImageUrl" />
        <meta property="og:image:type" content="image/png" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
        <meta property="og:image:alt" content="MedidaTek - Sistema sob medida para sua operacao" />

        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="shareTitle" />
        <meta name="twitter:description" :content="shareDescription" />
        <meta name="twitter:image" :content="ogImageUrl" />

        <script type="application/ld+json">{{ jsonLd }}</script>
    </Head>

    <div
        class="landing-universe min-h-screen bg-[#030305] text-white selection:bg-indigo-500/30 selection:text-indigo-200"
        :class="{ 'performance-mode': isPerformanceMode }"
    >
        <!-- Aurora Background -->
        <div class="aurora-bg fixed inset-0 z-0 pointer-events-none">
            <FuturisticBackground v-if="!isPerformanceMode" />
            <div class="future-grid"></div>
            <div class="future-dots"></div>
            <div class="future-scan"></div>
            <div class="aurora-orb orb-1"></div>
            <div class="aurora-orb orb-2"></div>
            <div class="aurora-orb orb-3"></div>
            <div class="noise-overlay"></div>
        </div>

        <!-- Floating Navbar -->
        <header class="fixed top-6 left-0 right-0 z-50 flex justify-center px-4">
            <nav class="glass-nav flex items-center gap-6 rounded-full px-6 py-3 ring-1 ring-white/10 backdrop-blur-xl">
                <a href="#" class="flex items-center gap-2 group">
                    <!-- Logo Tech -->
                    <div class="relative flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500/10 ring-1 ring-inset ring-indigo-500/20 group-hover:bg-indigo-500/20 group-hover:ring-indigo-500/40 transition-all duration-300">
                        <div class="absolute inset-0 rounded-lg bg-indigo-500/20 blur opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <svg class="relative z-10 h-5 w-5 text-indigo-500 group-hover:text-indigo-400 transition-colors" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 6V18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                            <path d="M20 6V18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                            <path d="M4 6L12 13L20 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="17" r="1.5" fill="currentColor" class="group-hover:animate-pulse"/>
                        </svg>
                    </div>
                    <span class="text-sm font-bold tracking-tight text-white group-hover:text-indigo-100 transition-colors">
                        Medida<span class="text-indigo-400">Tek</span>
                    </span>
                </a>
                <div class="h-4 w-px bg-white/10 hidden sm:block"></div>
                <div class="hidden sm:flex items-center gap-6 text-xs font-medium text-white/70">
                    <a href="#metodo" class="hover:text-white transition-colors">Método</a>
                    <a href="#infraestrutura" class="hover:text-white transition-colors">Infraestrutura</a>
                    <a href="#projetos" class="hover:text-white transition-colors">Projetos</a>
                    <a href="#impacto" class="hover:text-white transition-colors">Impacto</a>
                </div>
                <a href="#contato" class="btn-glow ml-2 rounded-full px-4 py-1.5 text-xs font-semibold text-black">
                    Começar
                </a>
            </nav>
        </header>

        <main class="relative z-10">
            <!-- Hero Section -->
            <section class="min-h-screen flex flex-col justify-center items-center px-4 pt-32 pb-20 text-center">
                <div class="animate-on-scroll delay-100 inline-flex items-center gap-2 rounded-full border border-white/5 bg-white/5 px-3 py-1 text-[10px] uppercase tracking-wider text-indigo-200 backdrop-blur-md">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Disponível para novos projetos
                </div>
                
                <h1 class="animate-on-scroll delay-200 mt-8 max-w-4xl text-5xl font-medium tracking-tight sm:text-7xl lg:text-8xl leading-[0.9]">
                    <span class="hero-title-gradient hero-title-soft">Escala não é Sorte</span>
                    <br />
                    <span class="hero-title-gradient hero-title-strong">
                        <span class="hero-title-strong-text">É Sistema.</span>
                    </span>
                </h1>

                <p class="animate-on-scroll delay-300 mt-8 max-w-xl text-lg text-white/60 leading-relaxed font-light">
                   Tecnologia sob medida para o seu negócio.
                </p>

                <div class="animate-on-scroll delay-400 mt-10 flex flex-col sm:flex-row items-center gap-4">
                    <a href="#contato" class="group relative flex h-12 items-center gap-2 rounded-full bg-white px-8 text-sm font-semibold text-black transition hover:bg-zinc-200">
                        <span>Iniciar projeto</span>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                    <a href="#projetos" class="text-sm font-medium text-white/70 hover:text-white transition-colors px-6 py-3">
                        Ver projetos
                    </a>
                </div>
            </section>

            <!-- Marquee Infinito -->
            <section class="perf-section py-10 border-y border-white/5 bg-black/20 backdrop-blur-sm">
                <div class="marquee-container">
                    <div class="marquee-content">
                        <span
                            v-for="tech in integrationList"
                            :key="tech"
                            class="font-bold px-8 uppercase tracking-widest transition-colors cursor-default text-2xl text-white/20 hover:text-white/40"
                        >
                            {{ tech }}
                        </span>
                        <!-- Duplicate for smooth loop -->
                        <span
                            v-for="tech in integrationList"
                            :key="`dup-${tech}`"
                            class="font-bold px-8 uppercase tracking-widest transition-colors cursor-default text-2xl text-white/20 hover:text-white/40"
                        >
                            {{ tech }}
                        </span>
                    </div>
                </div>
            </section>

            <section id="metodo" class="perf-section py-32 px-4 max-w-7xl mx-auto">
                <div class="mb-12">
                    <h2 class="mt-6 text-4xl md:text-5xl font-medium tracking-tight">Método</h2>
                    <p class="mt-4 text-white/60 max-w-2xl text-lg">Execução com processo. Clareza de escopo. Entrega com padrão de engenharia.</p>
                </div>

                <div class="grid gap-6 md:grid-cols-4">
                    <!-- 01 Diagnóstico -->
                    <div class="animate-on-scroll method-step-card method-step-1 group relative overflow-hidden rounded-[2rem] border border-white/10 bg-zinc-900/40 p-8 backdrop-blur-xl transition-all duration-500 hover:bg-zinc-900/60">
                        <div class="method-step-bg absolute inset-0 opacity-20 transition-opacity duration-500 group-hover:opacity-40"></div>
                        <div class="method-step-glow absolute -inset-px opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                        <div class="relative z-10 flex h-full flex-col justify-between">
                            <div class="mb-6 flex items-start justify-between">
                                <div class="method-step-badge inline-flex items-center gap-2 rounded-full border border-white/10 bg-black/20 px-3 py-1 text-xs font-bold uppercase tracking-wider text-indigo-300 backdrop-blur-md">
                                    <span>01</span>
                                    <span class="text-white/40">|</span>
                                    <span class="text-white/70">Diagnóstico</span>
                                </div>
                                <!-- Tech Visual: Scanner Radar -->
                                <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10 ring-1 ring-inset ring-indigo-500/20">
                                    <div class="absolute inset-0 animate-[spin_4s_linear_infinite] rounded-xl bg-gradient-to-tr from-transparent via-indigo-500/10 to-transparent"></div>
                                    <svg class="h-6 w-6 text-indigo-400 drop-shadow-[0_0_8px_rgba(99,102,241,0.5)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                    </svg>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-xl font-bold leading-tight text-white group-hover:text-indigo-200 transition-colors">Identificação de gargalos</h3>
                                <p class="mt-3 text-sm leading-relaxed text-white/60">
                                    Mapeamos processos, dados e riscos que limitam a escala. Entregamos um diagnóstico claro para orientar decisões de arquitetura.
                                </p>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-2">
                                <span class="rounded-lg border border-white/5 bg-white/5 px-2.5 py-1 text-[10px] font-medium uppercase tracking-wide text-indigo-200/70 transition-colors group-hover:border-indigo-500/30 group-hover:bg-indigo-500/10 group-hover:text-indigo-200">Processo</span>
                                <span class="rounded-lg border border-white/5 bg-white/5 px-2.5 py-1 text-[10px] font-medium uppercase tracking-wide text-indigo-200/70 transition-colors group-hover:border-indigo-500/30 group-hover:bg-indigo-500/10 group-hover:text-indigo-200">Risco</span>
                                <span class="rounded-lg border border-white/5 bg-white/5 px-2.5 py-1 text-[10px] font-medium uppercase tracking-wide text-indigo-200/70 transition-colors group-hover:border-indigo-500/30 group-hover:bg-indigo-500/10 group-hover:text-indigo-200">Arquitetura</span>
                            </div>
                        </div>
                    </div>

                    <!-- 02 Design & UX -->
                    <div class="animate-on-scroll method-step-card method-step-2 group relative overflow-hidden rounded-[2rem] border border-white/10 bg-zinc-900/40 p-8 backdrop-blur-xl transition-all duration-500 hover:bg-zinc-900/60 delay-100">
                        <div class="method-step-bg absolute inset-0 opacity-20 transition-opacity duration-500 group-hover:opacity-40"></div>
                        <div class="method-step-glow absolute -inset-px opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                        <div class="relative z-10 flex h-full flex-col justify-between">
                            <div class="mb-6 flex items-start justify-between">
                                <div class="method-step-badge inline-flex items-center gap-2 rounded-full border border-white/10 bg-black/20 px-3 py-1 text-xs font-bold uppercase tracking-wider text-cyan-300 backdrop-blur-md">
                                    <span>02</span>
                                    <span class="text-white/40">|</span>
                                    <span class="text-white/70">Design & UX</span>
                                </div>
                                <!-- Tech Visual: Floating Layers -->
                                <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-500/10 ring-1 ring-inset ring-cyan-500/20">
                                    <div class="absolute h-6 w-6 rounded border border-cyan-400/30 opacity-60 animate-[float_4s_ease-in-out_infinite]"></div>
                                    <div class="absolute h-6 w-6 rounded border border-cyan-400/50 opacity-80 animate-[float_4s_ease-in-out_infinite_1s] translate-x-1 translate-y-1"></div>
                                    <svg class="relative z-10 h-5 w-5 text-cyan-400 drop-shadow-[0_0_8px_rgba(34,211,238,0.5)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                    </svg>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-xl font-bold leading-tight text-white group-hover:text-cyan-200 transition-colors">Fluxos que convertem</h3>
                                <p class="mt-3 text-sm leading-relaxed text-white/60">
                                    Interfaces e fluxos focados em conversão. Cada decisão é validada com dados e testes de usabilidade para maximizar resultados.
                                </p>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-2">
                                <span class="rounded-lg border border-white/5 bg-white/5 px-2.5 py-1 text-[10px] font-medium uppercase tracking-wide text-cyan-200/70 transition-colors group-hover:border-cyan-500/30 group-hover:bg-cyan-500/10 group-hover:text-cyan-200">UX</span>
                                <span class="rounded-lg border border-white/5 bg-white/5 px-2.5 py-1 text-[10px] font-medium uppercase tracking-wide text-cyan-200/70 transition-colors group-hover:border-cyan-500/30 group-hover:bg-cyan-500/10 group-hover:text-cyan-200">Conversão</span>
                                <span class="rounded-lg border border-white/5 bg-white/5 px-2.5 py-1 text-[10px] font-medium uppercase tracking-wide text-cyan-200/70 transition-colors group-hover:border-cyan-500/30 group-hover:bg-cyan-500/10 group-hover:text-cyan-200">Eficiência</span>
                            </div>
                        </div>
                    </div>

                    <!-- 03 Build -->
                    <div class="animate-on-scroll method-step-card method-step-3 group relative overflow-hidden rounded-[2rem] border border-white/10 bg-zinc-900/40 p-8 backdrop-blur-xl transition-all duration-500 hover:bg-zinc-900/60 delay-200">
                        <div class="method-step-bg absolute inset-0 opacity-20 transition-opacity duration-500 group-hover:opacity-40"></div>
                        <div class="method-step-glow absolute -inset-px opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                        <div class="relative z-10 flex h-full flex-col justify-between">
                            <div class="mb-6 flex items-start justify-between">
                                <div class="method-step-badge inline-flex items-center gap-2 rounded-full border border-white/10 bg-black/20 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-300 backdrop-blur-md">
                                    <span>03</span>
                                    <span class="text-white/40">|</span>
                                    <span class="text-white/70">Build</span>
                                </div>
                                <!-- Tech Visual: Code Blocks -->
                                <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 ring-1 ring-inset ring-emerald-500/20">
                                    <div class="absolute inset-0 flex items-center justify-center opacity-30">
                                        <div class="h-8 w-8 rounded border border-dashed border-emerald-400 animate-[spin_10s_linear_infinite]"></div>
                                    </div>
                                    <svg class="h-6 w-6 text-emerald-400 drop-shadow-[0_0_8px_rgba(16,185,129,0.5)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                    </svg>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-xl font-bold leading-tight text-white group-hover:text-emerald-200 transition-colors">Entrega incremental</h3>
                                <p class="mt-3 text-sm leading-relaxed text-white/60">
                                    Desenvolvimento incremental, priorizando testes críticos e performance. Cada entrega é validada antes de avançar.
                                </p>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-2">
                                <span class="rounded-lg border border-white/5 bg-white/5 px-2.5 py-1 text-[10px] font-medium uppercase tracking-wide text-emerald-200/70 transition-colors group-hover:border-emerald-500/30 group-hover:bg-emerald-500/10 group-hover:text-emerald-200">Performance</span>
                                <span class="rounded-lg border border-white/5 bg-white/5 px-2.5 py-1 text-[10px] font-medium uppercase tracking-wide text-emerald-200/70 transition-colors group-hover:border-emerald-500/30 group-hover:bg-emerald-500/10 group-hover:text-emerald-200">Qualidade</span>
                                <span class="rounded-lg border border-white/5 bg-white/5 px-2.5 py-1 text-[10px] font-medium uppercase tracking-wide text-emerald-200/70 transition-colors group-hover:border-emerald-500/30 group-hover:bg-emerald-500/10 group-hover:text-emerald-200">Testes</span>
                            </div>
                        </div>
                    </div>

                    <!-- 04 Evolução -->
                    <div class="animate-on-scroll method-step-card method-step-4 group relative overflow-hidden rounded-[2rem] border border-white/10 bg-zinc-900/40 p-8 backdrop-blur-xl transition-all duration-500 hover:bg-zinc-900/60 delay-300">
                        <div class="method-step-bg absolute inset-0 opacity-20 transition-opacity duration-500 group-hover:opacity-40"></div>
                        <div class="method-step-glow absolute -inset-px opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                        <div class="relative z-10 flex h-full flex-col justify-between">
                            <div class="mb-6 flex items-start justify-between">
                                <div class="method-step-badge inline-flex items-center gap-2 rounded-full border border-white/10 bg-black/20 px-3 py-1 text-xs font-bold uppercase tracking-wider text-purple-300 backdrop-blur-md">
                                    <span>04</span>
                                    <span class="text-white/40">|</span>
                                    <span class="text-white/70">Evolução</span>
                                </div>
                                <!-- Tech Visual: Infinity Loop / Graph -->
                                <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500/10 ring-1 ring-inset ring-purple-500/20">
                                    <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-transparent via-purple-500/50 to-transparent blur-[2px]"></div>
                                    <svg class="h-6 w-6 text-purple-400 drop-shadow-[0_0_8px_rgba(168,85,247,0.5)] animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-xl font-bold leading-tight text-white group-hover:text-purple-200 transition-colors">Otimização contínua</h3>
                                <p class="mt-3 text-sm leading-relaxed text-white/60">
                                    Monitoramos métricas, analisamos dados e aplicamos melhorias. Automação e IA usadas quando o impacto é comprovado.
                                </p>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-2">
                                <span class="rounded-lg border border-white/5 bg-white/5 px-2.5 py-1 text-[10px] font-medium uppercase tracking-wide text-purple-200/70 transition-colors group-hover:border-purple-500/30 group-hover:bg-purple-500/10 group-hover:text-purple-200">Métricas</span>
                                <span class="rounded-lg border border-white/5 bg-white/5 px-2.5 py-1 text-[10px] font-medium uppercase tracking-wide text-purple-200/70 transition-colors group-hover:border-purple-500/30 group-hover:bg-purple-500/10 group-hover:text-purple-200">IA</span>
                                <span class="rounded-lg border border-white/5 bg-white/5 px-2.5 py-1 text-[10px] font-medium uppercase tracking-wide text-purple-200/70 transition-colors group-hover:border-purple-500/30 group-hover:bg-purple-500/10 group-hover:text-purple-200">Impacto</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="infraestrutura" class="perf-section py-28 px-4 max-w-7xl mx-auto">
                <div class="mb-12">
                    <h2 class="text-4xl md:text-5xl font-medium tracking-tight">Infraestrutura</h2>
                    <p class="mt-4 text-white/60 max-w-2xl text-lg">A base que sustenta o sistema: arquitetura, performance, IA, design, mobile e segurança.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 auto-rows-[320px]">
                    <div class="animate-on-scroll bento-card md:col-span-2 md:row-span-2 group relative overflow-hidden rounded-[2rem] bg-zinc-900/50 ring-1 ring-white/10 hover:ring-white/20 transition-all">
                        <div class="absolute inset-0 z-0">
                            <img :src="bentoImage('architecture').src" :srcset="bentoImage('architecture').srcset || undefined" :sizes="bentoImage('architecture').sizes || undefined" :alt="bentoImage('architecture').alt" class="futuristic-image w-full h-full object-cover opacity-75 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700" loading="lazy" decoding="async" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent"></div>
                        </div>
                        <div class="relative z-10 h-full flex flex-col justify-end p-8">
                            <div class="inline-flex items-center gap-2 mb-3">
                                <span class="px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-500/30 text-indigo-300 text-xs font-semibold uppercase tracking-wider">Core</span>
                            </div>
                            <h3 class="text-3xl font-medium text-white mb-2">Arquitetura Escalável</h3>
                            <p class="text-white/70 text-lg leading-relaxed max-w-md">Esqueça adaptações forçadas. Construímos exatamente o que seu processo exige, pronto para milhões de requisições desde o dia 1.</p>
                        </div>
                    </div>

                    <div class="animate-on-scroll bento-card md:col-span-2 group relative overflow-hidden rounded-[2rem] bg-zinc-900/50 ring-1 ring-white/10 hover:ring-white/20 transition-all">
                        <div class="absolute inset-0 z-0">
                            <img :src="bentoImage('speed').src" :srcset="bentoImage('speed').srcset || undefined" :sizes="bentoImage('speed').sizes || undefined" :alt="bentoImage('speed').alt" class="futuristic-image w-full h-full object-cover opacity-65 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700" loading="lazy" decoding="async" />
                            <div class="absolute inset-0 bg-gradient-to-r from-black via-black/40 to-transparent"></div>
                        </div>
                        <div class="relative z-10 h-full flex flex-col justify-center p-8">
                            <h3 class="text-2xl font-medium text-white mb-1">Velocidade Real</h3>
                            <p class="text-sm text-white/60 mb-6">Load time sub-100ms. Interfaces instantâneas.</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-5xl font-mono font-bold text-emerald-400 tracking-tighter">99</span>
                                <span class="text-lg text-emerald-500/50 font-mono">/100 Google PSI</span>
                            </div>
                        </div>
                    </div>

                    <div class="animate-on-scroll bento-card group relative overflow-hidden rounded-[2rem] bg-zinc-900/50 ring-1 ring-white/10 hover:ring-white/20 transition-all">
                        <div class="absolute inset-0 bg-gradient-to-br from-cyan-900/40 via-transparent to-transparent opacity-100"></div>
                        <div class="absolute inset-0 z-0">
                            <img :src="bentoImage('ai').src" :srcset="bentoImage('ai').srcset || undefined" :sizes="bentoImage('ai').sizes || undefined" :alt="bentoImage('ai').alt" class="futuristic-image w-full h-full object-cover opacity-65 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700" loading="lazy" decoding="async" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
                        </div>
                        <div class="relative z-10 h-full flex flex-col justify-between p-8">
                            <div class="self-end">
                                <svg class="w-10 h-10 text-cyan-400 drop-shadow-[0_0_10px_rgba(34,211,238,0.5)]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z"/><path d="M12 6a6 6 0 1 0 6 6 6 6 0 0 0-6-6zm0 10a4 4 0 1 1 4-4 4 4 0 0 1-4 4z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-medium text-white">IA Nativa</h3>
                                <p class="mt-2 text-sm text-white/70">Automação de triagem e insights preditivos.</p>
                            </div>
                        </div>
                    </div>

                    <div class="animate-on-scroll bento-card group relative overflow-hidden rounded-[2rem] bg-zinc-900/50 ring-1 ring-white/10 hover:ring-white/20 transition-all">
                        <div class="absolute inset-0 z-0">
                             <img :src="bentoImage('design').src" :srcset="bentoImage('design').srcset || undefined" :sizes="bentoImage('design').sizes || undefined" :alt="bentoImage('design').alt" class="futuristic-image w-full h-full object-cover opacity-65 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700" loading="lazy" decoding="async" />
                             <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
                        </div>
                        <div class="relative z-10 h-full flex flex-col justify-between p-8">
                            <div class="grid grid-cols-2 gap-2 opacity-80">
                                <div class="h-8 rounded bg-white/10 border border-white/5"></div>
                                <div class="h-8 rounded bg-indigo-500/80 border border-white/5"></div>
                                <div class="h-16 col-span-2 rounded bg-white/5 border border-white/5"></div>
                            </div>
                            <div>
                                <h3 class="text-xl font-medium text-white">Design System</h3>
                                <p class="mt-2 text-sm text-white/70">Consistência visual e UX de elite.</p>
                            </div>
                        </div>
                    </div>

                    <div class="animate-on-scroll bento-card md:col-span-2 group relative overflow-hidden rounded-[2rem] bg-zinc-900/50 ring-1 ring-white/10 hover:ring-white/20 transition-all">
                        <div class="absolute inset-0 z-0 bg-gradient-to-r from-zinc-900 to-transparent z-10"></div>
                        <img :src="bentoImage('mobile').src" :srcset="bentoImage('mobile').srcset || undefined" :sizes="bentoImage('mobile').sizes || undefined" :alt="bentoImage('mobile').alt" class="futuristic-image absolute right-0 top-0 h-full w-2/3 object-cover opacity-65 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700" loading="lazy" decoding="async" />
                        
                        <div class="relative z-20 h-full flex flex-col justify-center p-8">
                            <h3 class="text-2xl font-medium text-white">Mobile-First Real</h3>
                            <p class="mt-2 text-white/60 max-w-xs">Não é "adaptado". É desenhado para o toque, com gestos nativos e performance de app.</p>
                            <div class="mt-6 flex gap-3">
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-xs font-medium text-white/80">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    iOS & Android
                                </div>
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-xs font-medium text-white/80">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>
                                    PWA Ready
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="animate-on-scroll bento-card md:col-span-2 group relative overflow-hidden rounded-[2rem] bg-zinc-900/50 ring-1 ring-white/10 hover:ring-white/20 transition-all">
                        <div class="absolute inset-0 z-0">
                            <img :src="bentoImage('security').src" :srcset="bentoImage('security').srcset || undefined" :sizes="bentoImage('security').sizes || undefined" :alt="bentoImage('security').alt" class="futuristic-image w-full h-full object-cover opacity-65 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700" loading="lazy" decoding="async" />
                            <div class="absolute inset-0 bg-gradient-to-l from-black via-black/40 to-transparent"></div>
                        </div>
                        <div class="relative z-10 h-full flex flex-col justify-center p-8 text-right items-end">
                            <div class="inline-flex items-center gap-2 mb-3">
                                <span class="px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-semibold uppercase tracking-wider">Blindado</span>
                            </div>
                            <h3 class="text-2xl font-medium text-white mb-1">Segurança Enterprise</h3>
                            <p class="text-sm text-white/60 max-w-xs">Proteção de dados nível bancário, conformidade LGPD e backups automáticos.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Galeria de Projetos Horizontal -->
            <section v-if="hasProofLinks" id="projetos" class="perf-section py-20 overflow-hidden">
                <div class="px-4 max-w-7xl mx-auto mb-10 flex justify-between items-end">
                    <h2 class="text-3xl font-medium">Projetos recentes</h2>   
                </div>

                <div class="projects-marquee-container px-4">
                    <div class="projects-marquee-track">
                        <a
                            v-for="(item, index) in projectMarqueeLinks"
                            :key="`project-${index}-${item.url}`"
                            :href="item.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="project-card min-w-[300px] md:min-w-[400px] group relative aspect-[4/3] overflow-hidden rounded-2xl bg-zinc-900 ring-1 ring-white/10 hover:ring-white/35 hover:shadow-2xl hover:shadow-indigo-500/20 transition-all hover:-translate-y-1"
                        >
                            <div class="absolute inset-0 z-0">
                                <img
                                    v-if="item.image_src"
                                    :src="item.image_src"
                                    :srcset="item.image_srcset || undefined"
                                    :sizes="item.image_sizes || undefined"
                                    :alt="item.image_alt || item.name"
                                    class="project-image w-full h-full object-cover opacity-85 contrast-115 saturate-125 brightness-115 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700"
                                    :loading="projectImageLoading(index)"
                                    :fetchpriority="projectImageFetchPriority(index)"
                                    decoding="async"
                                    width="1200"
                                    height="900"
                                    draggable="false"
                                />
                                <div
                                    class="absolute inset-0 bg-gradient-to-br from-zinc-800 to-zinc-950 group-hover:scale-105 transition-transform duration-700"
                                    :class="item.image_src ? 'opacity-10' : ''"
                                ></div>
                                <div v-if="item.image_src" class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/10 to-transparent"></div>
                                <div v-if="item.image_src" class="absolute inset-0 bg-gradient-to-br from-white/10 via-transparent to-transparent"></div>
                                <div
                                    v-if="item.image_src"
                                    class="absolute inset-0 opacity-80 transition-opacity duration-500 group-hover:opacity-100"
                                    style="background: radial-gradient(120% 100% at 50% 0%, rgba(99,102,241,0.22) 0%, rgba(255,255,255,0.08) 35%, rgba(0,0,0,0) 70%);"
                                ></div>
                            </div>

                            <div class="absolute inset-0 z-10 p-6 flex flex-col justify-between bg-gradient-to-t from-black/65 to-transparent">
                                <div class="flex justify-end">
                                    <div v-if="item.tag" class="px-3 py-1 text-xs font-medium bg-white/10 backdrop-blur rounded-full border border-white/10">
                                        {{ item.tag }}
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white group-hover:text-indigo-200 transition-colors">{{ item.name }}</h3>
                                    <p class="text-sm text-white/70 mt-1 line-clamp-2">{{ item.note || 'Plataforma web de alta performance.' }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </section>

            <!-- Interactive Calculator Section -->
            <section id="impacto" class="perf-section py-32 px-4 max-w-5xl mx-auto">
                <div class="animate-on-scroll text-center mb-16">
                    <h2 class="text-3xl md:text-5xl font-medium tracking-tight">ROI Calculável</h2>
                    <p class="mt-4 text-white/60">Simule o impacto financeiro da transformação digital.</p>
                </div>

                <div class="animate-on-scroll bg-zinc-900/30 backdrop-blur-md rounded-[2rem] border border-white/10 p-8 md:p-12">
                    <div class="flex justify-center mb-10">
                        <div class="inline-flex rounded-full bg-black/50 p-1 border border-white/10">
                            <button @click="calcMode = 'vendas'" :class="calcMode === 'vendas' ? 'bg-white text-black shadow-lg' : 'text-white/60 hover:text-white'" class="px-6 py-2 rounded-full text-sm font-medium transition-all">Vendas</button>
                            <button @click="calcMode = 'processos'" :class="calcMode === 'processos' ? 'bg-white text-black shadow-lg' : 'text-white/60 hover:text-white'" class="px-6 py-2 rounded-full text-sm font-medium transition-all">Processos</button>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-12 items-center">
                        <div class="space-y-8">
                            <div v-if="calcMode === 'vendas'" class="space-y-6">
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm"><span class="text-white/70">Leads/mês</span> <span class="font-mono">{{ calcLeads }}</span></div>
                                    <input v-model.number="calcLeads" type="range" min="50" max="5000" class="w-full h-1 bg-white/10 rounded-lg appearance-none cursor-pointer accent-indigo-500">
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm"><span class="text-white/70">Ticket Médio (R$)</span> <span class="font-mono">{{ calcTicket }}</span></div>
                                    <input v-model.number="calcTicket" type="range" min="500" max="50000" step="100" class="w-full h-1 bg-white/10 rounded-lg appearance-none cursor-pointer accent-indigo-500">
                                </div>
                            </div>
                            <div v-else class="space-y-6">
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm"><span class="text-white/70">Horas/mês gastas</span> <span class="font-mono">{{ calcHours }}</span></div>
                                    <input v-model.number="calcHours" type="range" min="10" max="1000" class="w-full h-1 bg-white/10 rounded-lg appearance-none cursor-pointer accent-indigo-500">
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm"><span class="text-white/70">Custo Hora (R$)</span> <span class="font-mono">{{ calcHourlyCost }}</span></div>
                                    <input v-model.number="calcHourlyCost" type="range" min="20" max="500" class="w-full h-1 bg-white/10 rounded-lg appearance-none cursor-pointer accent-indigo-500">
                                </div>
                            </div>
                        </div>

                        <div class="relative group">
                            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-500 blur-2xl opacity-20 group-hover:opacity-30 transition-opacity"></div>
                            <div class="relative bg-black/60 rounded-2xl p-8 border border-white/10 text-center">
                                <div class="text-sm text-white/50 uppercase tracking-widest mb-2">Potencial Mensal</div>
                                <div class="text-4xl md:text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-indigo-200 to-white">
                                    {{ formatBRL(calcMode === 'vendas' ? revenueDelta : opsDelta) }}
                                </div>
                                <div class="mt-4 inline-flex items-center gap-1 text-xs text-emerald-400 bg-emerald-400/10 px-2 py-1 rounded">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                                    Estimativa conservadora
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contato / Wizard Style -->
            <section id="contato" class="perf-section py-32 px-4 relative overflow-hidden">
                <div class="max-w-2xl mx-auto relative z-10">
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-medium">Vamos construir?</h2>
                        <p class="mt-2 text-white/60">Conte sobre o projeto. Sem compromisso, apenas engenharia.</p>
                    </div>

                    <div
                        v-if="sentOnce"
                        class="bg-zinc-900/80 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl"
                    >
                        <div class="flex items-start gap-4">
                            <div class="mt-1 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-300">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-lg font-semibold text-white">Formulário concluído.</div>
                                <div class="mt-1 text-sm text-white/70">Vamos entrar em contato com você o mais rápido possível.</div>
                                <div class="mt-6">
                                    <button
                                        type="button"
                                        class="rounded-full bg-white px-6 py-2 text-sm font-semibold text-black hover:bg-zinc-200 transition-colors"
                                        @click="startNewLead"
                                    >
                                        Enviar outro projeto
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form v-else @submit.prevent="submit" class="bg-zinc-900/80 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl">
                        <div class="mb-8 flex justify-between items-center border-b border-white/5 pb-4">
                            <span class="text-xs font-medium text-white/40 uppercase tracking-widest">Passo {{ step }}/4</span>
                            <div class="flex gap-1">
                                <div v-for="i in 4" :key="i" class="h-1 w-8 rounded-full transition-colors" :class="i <= step ? 'bg-indigo-500' : 'bg-zinc-800'"></div>
                            </div>
                        </div>

                        <transition name="fade" mode="out-in">
                            <!-- STEP 1 -->
                            <div v-if="step === 1" key="step1" class="space-y-6">
                                <h3 class="text-xl font-medium">O que é prioridade máxima hoje?</h3>
                                <div class="grid gap-3">
                                    <label v-for="opt in ['Aumentar Vendas', 'Otimizar Processos', 'Integração de Sistemas', 'Novo Sistema']" :key="opt" 
                                        class="flex items-center p-4 rounded-xl border border-white/10 cursor-pointer hover:bg-white/5 transition-colors"
                                        :class="form.pain === opt ? 'bg-indigo-500/20 border-indigo-500/50' : ''"
                                    >
                                        <input type="radio" v-model="form.pain" :value="opt" class="hidden">
                                        <span class="text-white">{{ opt }}</span>
                                    </label>
                                </div>
                                <div v-if="clientErrors.pain" class="text-rose-400 text-sm">{{ clientErrors.pain }}</div>
                            </div>

                            <!-- STEP 2 -->
                            <div v-else-if="step === 2" key="step2" class="space-y-6">
                                <h3 class="text-xl font-medium">Como podemos te chamar?</h3>
                                <div class="space-y-4">
                                    <input v-model="form.name" type="text" placeholder="Seu nome" class="w-full bg-transparent border-b border-white/20 py-3 text-lg focus:border-indigo-500 focus:outline-none transition-colors">
                                    <input v-model="form.email" type="email" placeholder="Seu melhor e-mail" class="w-full bg-transparent border-b border-white/20 py-3 text-lg focus:border-indigo-500 focus:outline-none transition-colors">
                                    <input v-model="form.whatsapp" type="tel" placeholder="WhatsApp" class="w-full bg-transparent border-b border-white/20 py-3 text-lg focus:border-indigo-500 focus:outline-none transition-colors">
                                </div>
                                <label class="flex items-center gap-3 text-sm text-white/60 cursor-pointer">
                                    <input v-model="form.contact_consent" type="checkbox" class="rounded border-white/20 bg-white/5 text-indigo-500">
                                    Pode me chamar no Zap.
                                </label>
                                <div v-if="Object.keys(clientErrors).length" class="text-rose-400 text-sm">Preencha os campos obrigatórios.</div>
                            </div>

                            <!-- STEP 3 -->
                            <div v-else-if="step === 3" key="step3" class="space-y-6">
                                <h3 class="text-xl font-medium">Detalhes (Opcional)</h3>
                                <input v-model="form.company" type="text" placeholder="Nome da empresa" class="w-full bg-transparent border-b border-white/20 py-3 text-lg focus:border-indigo-500 focus:outline-none transition-colors">
                                <textarea v-model="form.message" rows="3" placeholder="Resumo do desafio..." class="w-full bg-transparent border-b border-white/20 py-3 text-lg focus:border-indigo-500 focus:outline-none transition-colors resize-none"></textarea>
                            </div>

                            <!-- STEP 4 -->
                            <div v-else key="step4" class="space-y-6">
                                <h3 class="text-xl font-medium">Expectativa de Investimento</h3>
                                <p class="text-sm text-white/60">Ajuda a definir se sugerimos um MVP enxuto ou uma solução robusta.</p>
                                <select v-model="form.investment_range" class="w-full bg-zinc-950 border border-white/20 rounded-xl p-4 text-white focus:border-indigo-500 focus:outline-none">
                                    <option value="" disabled>Selecione uma faixa</option>
                                    <option value="ate_15k">Até R$ 15k (MVP)</option>
                                    <option value="15k_30k">R$ 15k - 30k (Versão 1.0)</option>
                                    <option value="30k_60k">R$ 30k - 60k (Scale)</option>
                                    <option value="60k_plus">R$ 60k+ (Enterprise)</option>
                                </select>
                                <div v-if="clientErrors.investment_range" class="text-rose-400 text-sm">{{ clientErrors.investment_range }}</div>
                            </div>
                        </transition>

                        <div class="mt-8 flex justify-between">
                            <button v-if="step > 1" type="button" @click="prevStep" class="text-sm font-medium text-white/60 hover:text-white px-4 py-2">Voltar</button>
                            <div v-else></div>
                            
                            <button v-if="step < 4" type="button" @click="nextStep" class="bg-white text-black px-6 py-2 rounded-full font-semibold hover:bg-zinc-200 transition-colors">
                                Continuar
                            </button>
                            <button v-else type="submit" :disabled="form.processing" class="bg-indigo-500 text-white px-8 py-2 rounded-full font-semibold hover:bg-indigo-400 transition-colors disabled:opacity-50 shadow-[0_0_20px_rgba(99,102,241,0.5)]">
                                Finalizar
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </main>

        <footer class="px-4 py-10 border-t border-white/5 bg-black/40 backdrop-blur">
            <div class="mx-auto max-w-7xl flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-white/55">
                    <Link href="/login" class="hover:text-white transition-colors">
                        Login
                    </Link>
                    <Link :href="route('legal.privacy')" class="hover:text-white transition-colors">
                        Política de privacidade
                    </Link>
                    <Link :href="route('legal.terms')" class="hover:text-white transition-colors">
                        Termos de uso
                    </Link>
                </div>
                <div class="text-xs text-white/35">
                    © {{ year }} MedidaTek. Todos os direitos reservados.
                </div>
            </div>
        </footer>

        <AiChatWidget v-if="showChatWidget" />
    </div>
</template>

<style scoped>
/* Core Animations */
.landing-universe {
    background-color: #030305;
    overflow-x: hidden;
}

.perf-section {
    content-visibility: auto;
    contain-intrinsic-size: 1px 980px;
}

.aurora-bg {
    background: radial-gradient(circle at 50% 0%, #1a1a2e 0%, #000000 100%);
}

.future-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(to right, rgba(255, 255, 255, 0.06) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
    background-size: 96px 96px;
    background-position: 0 0;
    opacity: 0.18;
    transform: translateZ(0);
    mask-image: radial-gradient(circle at 50% 20%, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0.6) 48%, rgba(0, 0, 0, 0) 78%);
    animation: grid-drift 26s linear infinite;
}

.future-grid::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(to right, rgba(99, 102, 241, 0.18), rgba(34, 211, 238, 0.14), rgba(16, 185, 129, 0.10)),
        linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.45) 60%, rgba(0, 0, 0, 0.85) 100%);
    mix-blend-mode: screen;
    opacity: 0.55;
}

.future-dots {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.14) 1px, transparent 1.6px);
    background-size: 22px 22px;
    opacity: 0.10;
    transform: translateZ(0);
    mask-image: radial-gradient(circle at 50% 15%, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.55) 55%, rgba(0, 0, 0, 0) 80%);
    animation: dots-drift 40s linear infinite;
}

.future-scan {
    position: absolute;
    inset: -10% 0;
    background-image:
        repeating-linear-gradient(
            to bottom,
            rgba(255, 255, 255, 0.02) 0px,
            rgba(255, 255, 255, 0.02) 1px,
            rgba(0, 0, 0, 0) 5px,
            rgba(0, 0, 0, 0) 9px
        ),
        radial-gradient(800px 320px at 50% 0%, rgba(34, 211, 238, 0.12) 0%, rgba(34, 211, 238, 0.0) 70%);
    opacity: 0.28;
    mix-blend-mode: overlay;
    transform: translateZ(0);
    animation: scan-sweep 12s ease-in-out infinite;
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

.method-step-1 { --m1: 99, 102, 241; --m2: 34, 211, 238; }
.method-step-2 { --m1: 236, 72, 153; --m2: 167, 139, 250; }
.method-step-3 { --m1: 16, 185, 129; --m2: 34, 211, 238; }
.method-step-4 { --m1: 167, 139, 250; --m2: 99, 102, 241; }

.method-step-card {
    --mouse-x: 50%;
    --mouse-y: 50%;
    box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.02), 0 18px 60px rgba(0, 0, 0, 0.55);
    transform: translateZ(0);
    will-change: transform;
}

.method-step-card::before {
    content: '';
    position: absolute;
    inset: -1px;
    border-radius: 1.75rem;
    background: linear-gradient(120deg, rgba(var(--m1), 0.0) 0%, rgba(var(--m1), 0.65) 35%, rgba(var(--m2), 0.55) 70%, rgba(var(--m2), 0.0) 100%);
    opacity: 0.35;
    filter: blur(10px);
    pointer-events: none;
}

.method-step-card::after {
    content: '';
    position: absolute;
    top: -35%;
    left: -60%;
    width: 70%;
    height: 170%;
    background: linear-gradient(90deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.10) 35%, rgba(255, 255, 255, 0) 70%);
    transform: rotate(18deg);
    opacity: 0.8;
    pointer-events: none;
    animation: method-sheen 7.5s ease-in-out infinite;
}

.method-step-bg {
    background:
        radial-gradient(900px 500px at 20% 25%, rgba(var(--m1), 0.24) 0%, rgba(var(--m1), 0.0) 55%),
        radial-gradient(800px 520px at 85% 20%, rgba(var(--m2), 0.18) 0%, rgba(var(--m2), 0.0) 55%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(0, 0, 0, 0.12));
    background-size: 140% 140%;
    background-position: 0% 40%;
    opacity: 0.95;
    animation: method-pan 10s ease-in-out infinite;
}

.method-step-glow {
    background: radial-gradient(600px circle at var(--mouse-x) var(--mouse-y), rgba(var(--m1), 0.08), transparent 40%);
    pointer-events: none;
    z-index: 1;
}

.performance-mode .future-dots,
.performance-mode .future-scan,
.performance-mode .noise-overlay {
    display: none;
}

.performance-mode .future-grid,
.performance-mode .aurora-orb,
.performance-mode .method-step-bg,
.performance-mode .method-step-card::after,
.performance-mode .hero-title-gradient,
.performance-mode .hero-title-strong::after,
.performance-mode .marquee-content,
.performance-mode .projects-marquee-track {
    animation: none !important;
}

.performance-mode .aurora-orb {
    opacity: 0.18;
    filter: blur(40px);
}

.performance-mode .animate-on-scroll {
    opacity: 1;
    transform: none;
    transition: none;
}

.performance-mode .futuristic-image {
    filter: none;
}

.performance-mode .method-step-card,
.performance-mode .method-step-card:hover {
    transform: none;
    box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.03), 0 12px 32px rgba(0, 0, 0, 0.45);
}

.performance-mode .projects-marquee-container {
    overflow-x: auto;
    mask-image: none;
    -webkit-overflow-scrolling: touch;
    scroll-snap-type: x proximity;
}

.performance-mode .projects-marquee-track {
    padding-bottom: 0;
    will-change: auto;
    gap: 1rem;
}

.performance-mode .project-card,
.performance-mode .project-card:hover {
    transform: none !important;
    box-shadow: none;
}

.performance-mode .project-image {
    filter: none !important;
    transform: none !important;
}

.performance-mode .project-card .backdrop-blur {
    -webkit-backdrop-filter: none !important;
    backdrop-filter: none !important;
}

.performance-mode .glass-nav,
.performance-mode .backdrop-blur-xl,
.performance-mode .backdrop-blur-md,
.performance-mode .backdrop-blur {
    -webkit-backdrop-filter: none !important;
    backdrop-filter: none !important;
}

@media (prefers-reduced-motion: reduce) {
    .animate-spin,
    .animate-pulse,
    .animate-bounce {
        animation: none !important;
    }
}

.method-step-noise {
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.82' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.06'/%3E%3C/svg%3E");
    opacity: 0.6;
    mix-blend-mode: overlay;
}

.method-step-badge {
    background: rgba(0, 0, 0, 0.25);
    border-color: rgba(255, 255, 255, 0.12);
}

.method-step-icon {
    color: rgba(var(--m2), 0.95);
    filter: drop-shadow(0 0 18px rgba(var(--m2), 0.18));
}

.method-step-chip {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(255, 255, 255, 0.10);
}

.method-step-card:hover {
    transform: translateY(-6px) scale(1.02);
    box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.05), 0 26px 90px rgba(0, 0, 0, 0.7);
}

.futuristic-image {
    filter: saturate(1.25) contrast(1.15) brightness(1.05) drop-shadow(0 0 18px rgba(99, 102, 241, 0.22)) drop-shadow(0 0 34px rgba(34, 211, 238, 0.14));
    transform: translateZ(0);
    will-change: transform;
}

.hero-title-gradient {
    display: inline-block;
    color: transparent;
    background-image: linear-gradient(90deg, rgba(165, 180, 252, 1) 0%, rgba(34, 211, 238, 1) 22%, rgba(52, 211, 153, 1) 50%, rgba(167, 139, 250, 1) 75%, rgba(236, 72, 153, 1) 100%);
    background-size: 320% 100%;
    background-position: 0% 50%;
    -webkit-background-clip: text;
    background-clip: text;
    filter: drop-shadow(0 0 18px rgba(99, 102, 241, 0.22)) drop-shadow(0 0 26px rgba(34, 211, 238, 0.14));
    animation: hero-gradient 9s ease-in-out infinite;
}

.hero-title-soft {
    opacity: 0.95;
}

.hero-title-strong {
    position: relative;
    letter-spacing: -0.02em;
    z-index: 0;
    isolation: isolate;
}

.hero-title-strong-text {
    position: relative;
    z-index: 2;
}

.hero-title-strong::after {
    content: '';
    position: absolute;
    left: -0.08em;
    right: -0.08em;
    bottom: -0.24em;
    height: 0.12em;
    border-radius: 999px;
    background: linear-gradient(90deg, rgba(34, 211, 238, 0.0) 0%, rgba(34, 211, 238, 0.7) 20%, rgba(167, 139, 250, 0.9) 55%, rgba(236, 72, 153, 0.75) 85%, rgba(236, 72, 153, 0.0) 100%);
    filter: blur(8px);
    opacity: 0.7;
    transform: translateZ(0);
    animation: hero-underline 3.8s ease-in-out infinite;
    pointer-events: none;
    z-index: 1;
}

@keyframes method-pan {
    0% { background-position: 0% 40%; }
    50% { background-position: 100% 60%; }
    100% { background-position: 0% 40%; }
}

@keyframes method-sheen {
    0% { transform: translateX(0) rotate(18deg); opacity: 0.0; }
    15% { opacity: 0.8; }
    50% { transform: translateX(240%) rotate(18deg); opacity: 0.0; }
    100% { transform: translateX(240%) rotate(18deg); opacity: 0.0; }
}

@keyframes hero-gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

@keyframes hero-underline {
    0%, 100% { opacity: 0.55; transform: translateY(0) scaleX(0.92); }
    50% { opacity: 0.85; transform: translateY(-1px) scaleX(1); }
}

@keyframes float {
    0%, 100% { transform: translate(0, 0); }
    33% { transform: translate(30px, -50px); }
    66% { transform: translate(-20px, 20px); }
}

@keyframes grid-drift {
    0% { background-position: 0 0; }
    100% { background-position: 192px 192px; }
}

@keyframes dots-drift {
    0% { background-position: 0 0; }
    100% { background-position: -220px 160px; }
}

@keyframes scan-sweep {
    0%, 100% { transform: translateY(-2%); opacity: 0.18; }
    50% { transform: translateY(2%); opacity: 0.32; }
}

@media (prefers-reduced-motion: reduce) {
    .future-grid,
    .future-dots,
    .future-scan {
        animation: none;
    }
}

/* Animations Utils */
.animate-on-scroll {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.animate-on-scroll.in-view {
    opacity: 1;
    transform: translateY(0);
}

.delay-100 { transition-delay: 100ms; }
.delay-200 { transition-delay: 200ms; }
.delay-300 { transition-delay: 300ms; }
.delay-400 { transition-delay: 400ms; }

/* Marquee */
.marquee-container {
    overflow: hidden;
    white-space: nowrap;
}

.marquee-content {
    display: inline-block;
    animation: marquee 55s linear infinite;
}

@keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

.projects-marquee-container {
    overflow: hidden;
    mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
}

.projects-marquee-track {
    display: flex;
    --projects-marquee-gap: 1.5rem;
    gap: var(--projects-marquee-gap);
    width: max-content;
    padding-bottom: 2rem;
    --projects-marquee-duration: 70s;
    animation: projects-marquee var(--projects-marquee-duration) linear infinite;
    animation-delay: calc(var(--projects-marquee-duration) * -0.5);
    will-change: transform;
}

.project-card {
    contain: layout paint;
    backface-visibility: hidden;
    transform: translateZ(0);
}

.project-image {
    transform: translateZ(0);
}

.projects-marquee-container:hover .projects-marquee-track {
    animation-play-state: paused;
}

@keyframes projects-marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(calc(-50% - (var(--projects-marquee-gap) * 0.5))); }
}

@media (max-width: 1024px) {
    .projects-marquee-container {
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scroll-snap-type: x mandatory;
        mask-image: none;
        padding-bottom: 0.25rem;
    }

    .projects-marquee-track {
        animation: none;
        will-change: auto;
        padding-bottom: 0;
        gap: 1rem;
        padding-right: 1rem;
    }

    .project-card {
        min-width: min(88vw, 360px) !important;
        scroll-snap-align: start;
    }

    .project-image {
        filter: none;
        transform: none !important;
    }
}

@media (prefers-reduced-motion: reduce) {
    .projects-marquee-container {
        overflow-x: auto;
        mask-image: none;
    }

    .projects-marquee-track {
        animation: none;
        padding-bottom: 0;
    }

    .hero-title-gradient {
        animation: none;
        background-position: 50% 50%;
    }

    .hero-title-strong::after {
        animation: none;
        opacity: 0.6;
    }
}

/* UI Elements */
.btn-glow {
    background: white;
    box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
    transition: box-shadow 0.3s ease, transform 0.2s ease;
}

.btn-glow:hover {
    box-shadow: 0 0 30px rgba(255, 255, 255, 0.5);
    transform: scale(1.05);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from {
    opacity: 0;
    transform: translateX(10px);
}
.fade-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}

/* Scrollbar Hide */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
