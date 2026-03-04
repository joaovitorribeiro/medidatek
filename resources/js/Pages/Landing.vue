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

const props = defineProps<{
    proofLinks: ProofLink[];
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
                    Software que <br />
                    <span class="text-transparent bg-clip-text bg-gradient-to-b from-white via-white/90 to-white/50">muda o jogo.</span>
                </h1>

                <p class="animate-on-scroll delay-300 mt-8 max-w-xl text-lg text-white/60 leading-relaxed font-light">
                    Não entregamos apenas código. Entregamos clareza operacional, design de elite e inteligência que escala seu negócio.
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 auto-rows-[300px]">
                    <!-- Card 1 -->
                    <div class="animate-on-scroll bento-card md:col-span-2 group relative overflow-hidden rounded-3xl bg-zinc-900/50 p-8 ring-1 ring-white/10 hover:ring-white/20 transition-all">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative z-10 h-full flex flex-col justify-between">
                            <div>
                                <h3 class="text-2xl font-medium text-white">Engenharia Sob Medida</h3>
                                <p class="mt-2 text-white/60 max-w-md">Esqueça adaptações forçadas. Construímos exatamente o que seu processo exige, com arquitetura escalável desde o dia 1.</p>
                            </div>
                            <div class="flex gap-2 mt-4">
                                <div class="h-2 w-2 rounded-full bg-indigo-500"></div>
                                <div class="h-2 w-2 rounded-full bg-zinc-700"></div>
                                <div class="h-2 w-2 rounded-full bg-zinc-700"></div>
                            </div>
                        </div>
                        <!-- Decorative Abstract UI -->
                        <div class="absolute right-0 bottom-0 w-1/2 h-2/3 bg-zinc-950 rounded-tl-3xl border-t border-l border-white/10 p-4 opacity-80 group-hover:translate-y-2 transition-transform duration-500">
                            <div class="space-y-3">
                                <div class="h-2 w-2/3 bg-zinc-800 rounded-full"></div>
                                <div class="h-2 w-1/2 bg-zinc-800 rounded-full"></div>
                                <div class="h-8 w-full bg-indigo-500/10 rounded-lg border border-indigo-500/20"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="animate-on-scroll bento-card group relative overflow-hidden rounded-3xl bg-zinc-900/50 p-8 ring-1 ring-white/10 hover:ring-white/20 transition-all">
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative z-10">
                            <h3 class="text-xl font-medium text-white">Velocidade Real</h3>
                            <p class="mt-2 text-sm text-white/60">Load time sub-100ms. Interfaces que reagem instantaneamente.</p>
                        </div>
                        <div class="absolute inset-x-0 bottom-0 h-32 flex items-end justify-center pb-8">
                             <div class="text-4xl font-mono font-bold text-emerald-400 tracking-tighter">99<span class="text-lg text-emerald-500/50">/100</span></div>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="animate-on-scroll bento-card group relative overflow-hidden rounded-3xl bg-zinc-900/50 p-8 ring-1 ring-white/10 hover:ring-white/20 transition-all">
                        <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/10 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative z-10">
                            <h3 class="text-xl font-medium text-white">IA Integrada</h3>
                            <p class="mt-2 text-sm text-white/60">Não é hype. É automação de triagem, análise de dados e suporte.</p>
                        </div>
                        <div class="absolute right-4 bottom-4">
                            <svg class="w-12 h-12 text-cyan-500/20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z"/><path d="M12 6a6 6 0 1 0 6 6 6 6 0 0 0-6-6zm0 10a4 4 0 1 1 4-4 4 4 0 0 1-4 4z"/></svg>
                        </div>
                    </div>

                    <!-- Card 4 -->
                    <div class="animate-on-scroll bento-card md:col-span-2 group relative overflow-hidden rounded-3xl bg-zinc-900/50 p-8 ring-1 ring-white/10 hover:ring-white/20 transition-all">
                        <div class="absolute inset-0 bg-gradient-to-br from-rose-500/10 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="grid md:grid-cols-2 gap-8 h-full items-center">
                            <div>
                                <h3 class="text-2xl font-medium text-white">Design System Puro</h3>
                                <p class="mt-2 text-white/60">Componentes consistentes, acessibilidade WCAG AA e beleza funcional. Seu time vai amar usar.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3 opacity-60 group-hover:opacity-100 transition-opacity">
                                <div class="h-10 rounded-lg bg-zinc-800 border border-white/5"></div>
                                <div class="h-10 rounded-lg bg-indigo-600 border border-white/5"></div>
                                <div class="h-24 col-span-2 rounded-lg bg-zinc-950 border border-white/10"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Galeria de Projetos Horizontal -->
            <section v-if="hasProofLinks" id="projetos" class="py-20 overflow-hidden">
                <div class="px-4 max-w-7xl mx-auto mb-10 flex justify-between items-end">
                    <h2 class="text-3xl font-medium">Obras recentes</h2>
                    <a href="#" class="text-sm text-white/50 hover:text-white transition-colors">Ver case studies →</a>
                </div>
                
                <div class="flex gap-6 px-4 overflow-x-auto pb-8 snap-x scrollbar-hide">
                    <a 
                        v-for="(item, index) in proofLinks" 
                        :key="item.url"
                        :href="item.url"
                        target="_blank"
                        class="animate-on-scroll min-w-[300px] md:min-w-[400px] snap-center group relative aspect-[4/3] overflow-hidden rounded-2xl bg-zinc-900 ring-1 ring-white/10"
                        :style="{ transitionDelay: `${index * 100}ms` }"
                    >
                        <!-- Placeholder Gradient for Project Image -->
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
                                    <label v-for="opt in ['Aumentar Vendas', 'Otimizar Processos', 'Integração de Sistemas', 'Nova Plataforma']" :key="opt" 
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
