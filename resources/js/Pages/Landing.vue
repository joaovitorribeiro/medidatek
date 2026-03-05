<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import AiChatWidget from '@/Components/Marketing/AiChatWidget.vue';

type ProofLink = {
    name: string;
    url: string;
    tag?: string;
    note?: string;
};

type BentoImage = {
    src: string;
    alt: string;
};

const props = defineProps<{
    proofLinks: ProofLink[];
    bentoImages: Record<string, BentoImage>;
}>();

const sentOnce = ref(false);
const step = ref<1 | 2 | 3 | 4>(1);
const year = new Date().getFullYear();
const clientErrors = ref<Record<string, string>>({});
const calcMode = ref<'vendas' | 'processos'>('vendas');
const calcLeads = ref(300);
const calcCloseRate = ref(8);
const calcTicket = ref(3500);
const calcHours = ref(120);
const calcHourlyCost = ref(65);
const integrationList = [
    'HubSpot', 'Pipedrive', 'RD Station', 'WhatsApp', 'E-mail',
    'Stripe', 'Mercado Pago', 'Bling', 'Omie', 'Google Sheets', 'ERP/API'
];

function proofHost(url: string) {
    try {
        return new URL(url).hostname.replace(/^www\./, '');
    } catch {
        return url;
    }
}

const proofLinks = computed(() => props.proofLinks);
const hasProofLinks = computed(() => proofLinks.value.length > 0);

function bentoImage(key: string): BentoImage {
    return props.bentoImages?.[key] ?? { src: '', alt: '' };
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

onMounted(() => {
    // UTMs e Tracking
    const params = new URLSearchParams(window.location.search);
    form.utm_source = params.get('utm_source') ?? '';
    form.utm_medium = params.get('utm_medium') ?? '';
    form.utm_campaign = params.get('utm_campaign') ?? '';
    form.landing_path = window.location.pathname;
    form.referrer = document.referrer ?? '';

    // Scroll & Reveal Animations
    const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ?? false;
    if (!reduceMotion) {
        const observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('in-view');
                        observer.unobserve(entry.target);
                    }
                }
            },
            { threshold: 0.1, rootMargin: '0px 0px -50px 0px' }
        );
        document.querySelectorAll('.animate-on-scroll').forEach((el) => observer.observe(el));

        // Magnetic & Spotlight Effects
        document.addEventListener('mousemove', (e) => {
            const x = e.clientX;
            const y = e.clientY;
            document.documentElement.style.setProperty('--mouse-x', `${x}px`);
            document.documentElement.style.setProperty('--mouse-y', `${y}px`);
        });
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
function submit() {
    form.post(route('leads.store'), {
        preserveScroll: true,
        onSuccess: () => {
            sentOnce.value = true;
            form.reset();
            step.value = 1;
        },
    });
}
</script>

<template>
    <Head title="MedidaTek — O futuro do seu software">
        <meta name="description" content="Engenharia de software sob medida, design de alta performance e IA aplicada." />
    </Head>

    <div class="landing-universe min-h-screen bg-[#030305] text-white selection:bg-indigo-500/30 selection:text-indigo-200">
        <!-- Aurora Background -->
        <div class="aurora-bg fixed inset-0 z-0 pointer-events-none">
            <div class="aurora-orb orb-1"></div>
            <div class="aurora-orb orb-2"></div>
            <div class="aurora-orb orb-3"></div>
            <div class="noise-overlay"></div>
        </div>

        <!-- Floating Navbar -->
        <header class="fixed top-6 left-0 right-0 z-50 flex justify-center px-4">
            <nav class="glass-nav flex items-center gap-6 rounded-full px-6 py-3 ring-1 ring-white/10 backdrop-blur-xl">
                <a href="#" class="text-sm font-bold tracking-tight text-white">MedidaTek</a>
                <div class="h-4 w-px bg-white/10 hidden sm:block"></div>
                <div class="hidden sm:flex items-center gap-6 text-xs font-medium text-white/70">
                    <a href="#metodo" class="hover:text-white transition-colors">Método</a>
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
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
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
                   Tecnologia sob medida para crescer.
                </p>

                <div class="animate-on-scroll delay-400 mt-10 flex flex-col sm:flex-row items-center gap-4">
                    <a href="#contato" class="group relative flex h-12 items-center gap-2 rounded-full bg-white px-8 text-sm font-semibold text-black transition hover:bg-zinc-200">
                        <span>Iniciar projeto</span>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                    <a href="#projetos" class="text-sm font-medium text-white/70 hover:text-white transition-colors px-6 py-3">
                        Ver galeria
                    </a>
                </div>
            </section>

            <!-- Marquee Infinito -->
            <section class="py-10 border-y border-white/5 bg-black/20 backdrop-blur-sm">
                <div class="marquee-container">
                    <div class="marquee-content">
                        <span v-for="tech in integrationList" :key="tech" class="text-2xl font-bold text-white/10 px-8 uppercase tracking-widest hover:text-white/30 transition-colors cursor-default">
                            {{ tech }}
                        </span>
                        <!-- Duplicate for smooth loop -->
                        <span v-for="tech in integrationList" :key="`dup-${tech}`" class="text-2xl font-bold text-white/10 px-8 uppercase tracking-widest hover:text-white/30 transition-colors cursor-default">
                            {{ tech }}
                        </span>
                    </div>
                </div>
            </section>

            <!-- Bento Grid de Benefícios -->
            <section id="metodo" class="py-32 px-4 max-w-7xl mx-auto">
                <div class="mb-12">
                    <h2 class="text-4xl md:text-5xl font-medium tracking-tight">Engenharia de Impacto</h2>
                    <p class="mt-4 text-white/60 max-w-2xl text-lg">Não é só código. É método, design e estratégia para escalar sem quebrar.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 auto-rows-[320px]">
                    <!-- Card 1: Arquitetura (Large) -->
                    <div class="animate-on-scroll bento-card md:col-span-2 md:row-span-2 group relative overflow-hidden rounded-[2rem] bg-zinc-900/50 ring-1 ring-white/10 hover:ring-white/20 transition-all">
                        <div class="absolute inset-0 z-0">
                            <img :src="bentoImage('architecture').src" :alt="bentoImage('architecture').alt" class="futuristic-image w-full h-full object-cover opacity-75 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700" />
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

                    <!-- Card 2: Velocidade -->
                    <div class="animate-on-scroll bento-card md:col-span-2 group relative overflow-hidden rounded-[2rem] bg-zinc-900/50 ring-1 ring-white/10 hover:ring-white/20 transition-all">
                        <div class="absolute inset-0 z-0">
                            <img :src="bentoImage('speed').src" :alt="bentoImage('speed').alt" class="futuristic-image w-full h-full object-cover opacity-65 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700" />
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

                    <!-- Card 3: IA -->
                    <div class="animate-on-scroll bento-card group relative overflow-hidden rounded-[2rem] bg-zinc-900/50 ring-1 ring-white/10 hover:ring-white/20 transition-all">
                        <div class="absolute inset-0 bg-gradient-to-br from-cyan-900/40 via-transparent to-transparent opacity-100"></div>
                        <div class="absolute inset-0 z-0">
                            <img :src="bentoImage('ai').src" :alt="bentoImage('ai').alt" class="futuristic-image w-full h-full object-cover opacity-65 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700" />
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

                    <!-- Card 4: Design System -->
                    <div class="animate-on-scroll bento-card group relative overflow-hidden rounded-[2rem] bg-zinc-900/50 ring-1 ring-white/10 hover:ring-white/20 transition-all">
                        <div class="absolute inset-0 z-0">
                             <img :src="bentoImage('design').src" :alt="bentoImage('design').alt" class="futuristic-image w-full h-full object-cover opacity-65 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700" />
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

                    <!-- Card 5: Mobile First (New) -->
                    <div class="animate-on-scroll bento-card md:col-span-2 group relative overflow-hidden rounded-[2rem] bg-zinc-900/50 ring-1 ring-white/10 hover:ring-white/20 transition-all">
                        <div class="absolute inset-0 z-0 bg-gradient-to-r from-zinc-900 to-transparent z-10"></div>
                        <img :src="bentoImage('mobile').src" :alt="bentoImage('mobile').alt" class="futuristic-image absolute right-0 top-0 h-full w-2/3 object-cover opacity-65 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700" />
                        
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

                    <!-- Card 6: Security (New) -->
                    <div class="animate-on-scroll bento-card md:col-span-2 group relative overflow-hidden rounded-[2rem] bg-zinc-900/50 ring-1 ring-white/10 hover:ring-white/20 transition-all">
                        <div class="absolute inset-0 z-0">
                            <img :src="bentoImage('security').src" :alt="bentoImage('security').alt" class="futuristic-image w-full h-full object-cover opacity-65 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700" />
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
            <section v-if="hasProofLinks" id="projetos" class="py-20 overflow-hidden">
                <div class="px-4 max-w-7xl mx-auto mb-10 flex justify-between items-end">
                    <h2 class="text-3xl font-medium">Projetos recentes</h2>   
                </div>

                <div class="projects-marquee-container px-4">
                    <div class="projects-marquee-track">
                        <a
                            v-for="(item, index) in proofLinks"
                            :key="`p-${index}-${item.url}`"
                            :href="item.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="min-w-[300px] md:min-w-[400px] group relative aspect-[4/3] overflow-hidden rounded-2xl bg-zinc-900 ring-1 ring-white/10 hover:ring-white/20 transition-all"
                        >
                            <div class="absolute inset-0 bg-gradient-to-br from-zinc-800 to-zinc-950 group-hover:scale-105 transition-transform duration-700"></div>

                            <div class="absolute inset-0 p-6 flex flex-col justify-between bg-gradient-to-t from-black/80 to-transparent">
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

                        <a
                            v-for="(item, index) in proofLinks"
                            :key="`d-${index}-${item.url}`"
                            :href="item.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="min-w-[300px] md:min-w-[400px] group relative aspect-[4/3] overflow-hidden rounded-2xl bg-zinc-900 ring-1 ring-white/10 hover:ring-white/20 transition-all"
                        >
                            <div class="absolute inset-0 bg-gradient-to-br from-zinc-800 to-zinc-950 group-hover:scale-105 transition-transform duration-700"></div>

                            <div class="absolute inset-0 p-6 flex flex-col justify-between bg-gradient-to-t from-black/80 to-transparent">
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
            <section id="impacto" class="py-32 px-4 max-w-5xl mx-auto">
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
            <section id="contato" class="py-32 px-4 relative overflow-hidden">
                <div class="max-w-2xl mx-auto relative z-10">
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-medium">Vamos construir?</h2>
                        <p class="mt-2 text-white/60">Conte sobre o projeto. Sem compromisso, apenas engenharia.</p>
                    </div>

                    <form @submit.prevent="submit" class="bg-zinc-900/80 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl">
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

        <footer class="py-20 px-4 border-t border-white/5 bg-black">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="text-center md:text-left">
                    <h2 class="text-[10vw] md:text-[5vw] font-bold leading-none tracking-tighter text-white/20 select-none">MEDIDATEK</h2>
                </div>
                <div class="flex gap-8 text-sm text-white/40">
                    <a href="#" class="hover:text-white transition-colors">LinkedIn</a>
                    <a href="#" class="hover:text-white transition-colors">Instagram</a>
                    <a href="#" class="hover:text-white transition-colors">GitHub</a>
                </div>
            </div>
            <div class="max-w-7xl mx-auto mt-8 text-center md:text-right text-xs text-white/20">
                © {{ year }} MedidaTek. All rights reserved.
            </div>
        </footer>

        <AiChatWidget />
    </div>
</template>

<style scoped>
/* Core Animations */
.landing-universe {
    background-color: #030305;
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
    animation: marquee 40s linear infinite;
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

.projects-marquee-container:hover .projects-marquee-track {
    animation-play-state: paused;
}

@keyframes projects-marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(calc(-50% - (var(--projects-marquee-gap) * 0.5))); }
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
