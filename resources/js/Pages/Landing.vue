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
    'HubSpot',
    'Pipedrive',
    'RD Station',
    'WhatsApp',
    'E-mail',
    'Stripe',
    'Mercado Pago',
    'Bling',
    'Omie',
    'Google Sheets',
    'ERP/API',
    'BI/Dashboards',
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
const featuredProofLinks = computed(() => proofLinks.value.slice(0, 6));

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
    utm_source: '',
    utm_medium: '',
    utm_campaign: '',
    utm_content: '',
    utm_term: '',
    landing_path: '',
    referrer: '',
    contact_consent: false,
});

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    form.utm_source = params.get('utm_source') ?? '';
    form.utm_medium = params.get('utm_medium') ?? '';
    form.utm_campaign = params.get('utm_campaign') ?? '';
    form.utm_content = params.get('utm_content') ?? '';
    form.utm_term = params.get('utm_term') ?? '';
    form.landing_path = window.location.pathname;
    form.referrer = document.referrer ?? '';

    const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ?? false;
    if (!reduceMotion) {
        const observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('reveal-in');
                        observer.unobserve(entry.target);
                    }
                }
            },
            { threshold: 0.12, rootMargin: '0px 0px -10% 0px' },
        );

        document.querySelectorAll('[data-reveal]').forEach((el) => observer.observe(el));
    }
});

const baselineRevenue = computed(() => (calcLeads.value * (calcCloseRate.value / 100)) * calcTicket.value);
const projectedRevenue = computed(() => (calcLeads.value * 1.32 * ((calcCloseRate.value * 1.18) / 100)) * calcTicket.value);
const revenueDelta = computed(() => projectedRevenue.value - baselineRevenue.value);

const baselineOpsCost = computed(() => calcHours.value * calcHourlyCost.value);
const projectedOpsCost = computed(() => baselineOpsCost.value * 0.6);
const opsDelta = computed(() => baselineOpsCost.value - projectedOpsCost.value);

const faqJsonLd = computed(() => {
    const items = [
        {
            question: 'Quanto custa um sistema sob medida?',
            answer:
                'Depende de escopo, integrações e nível de automação/IA. No diagnóstico definimos o menor escopo que gera impacto e uma faixa de investimento realista.',
        },
        {
            question: 'Qual é o prazo médio?',
            answer:
                'Trabalhamos por entregas incrementais: você vê valor cedo (primeira versão) e evolui por etapas, com metas e métricas.',
        },
        {
            question: 'Vocês integram com ferramentas que já uso?',
            answer:
                'Sim. Integramos CRMs, WhatsApp, e-mail, ERPs, gateways e APIs externas com rastreio e reprocessamento quando necessário.',
        },
        {
            question: 'Como a IA é aplicada com segurança?',
            answer:
                'Com minimização de dados, limites de uso, auditoria e controle do que pode ou não ser enviado a provedores. IA entra onde é útil e mensurável.',
        },
    ];

    return JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'FAQPage',
        mainEntity: items.map((item) => ({
            '@type': 'Question',
            name: item.question,
            acceptedAnswer: {
                '@type': 'Answer',
                text: item.answer,
            },
        })),
    });
});

function setStep(next: 1 | 2 | 3 | 4) {
    step.value = next;
    clientErrors.value = {};
}

function nextStep() {
    const errors: Record<string, string> = {};

    if (step.value === 1) {
        if (!form.pain) errors.pain = 'Selecione a prioridade.';
    }

    if (step.value === 2) {
        if (!form.name) errors.name = 'Informe seu nome.';
        if (!form.email) errors.email = 'Informe seu e-mail.';
        if (!form.whatsapp) errors.whatsapp = 'Informe seu WhatsApp.';
        if (!form.contact_consent) errors.contact_consent = 'Confirme o consentimento de contato.';
    }

    if (step.value === 4) {
        if (!form.investment_range) errors.investment_range = 'Selecione a pretensão de investimento.';
    }

    clientErrors.value = errors;
    if (Object.keys(errors).length > 0) return;

    if (step.value === 1) step.value = 2;
    else if (step.value === 2) step.value = 3;
    else if (step.value === 3) step.value = 4;
}

function prevStep() {
    if (step.value === 2) step.value = 1;
    else if (step.value === 3) step.value = 2;
    else if (step.value === 4) step.value = 3;
}

function submit() {
    form.post(route('leads.store'), {
        preserveScroll: true,
        onSuccess: () => {
            sentOnce.value = true;
            form.reset('name', 'email', 'whatsapp', 'company', 'pain', 'investment_range', 'message', 'contact_consent');
            step.value = 1;
            clientErrors.value = {};
        },
    });
}
</script>

<template>
    <Head title="MedidaTek — Software sob medida que gera resultado">
        <meta
            name="description"
            content="Software sob medida e IA para resolver problemas reais e gerar resultado mensurável. Integrações, automação e entregas incrementais com foco no negócio."
        />
        <meta property="og:title" content="MedidaTek — Software sob medida que gera resultado" />
        <meta
            property="og:description"
            content="Software sob medida e IA para resolver problemas reais e gerar resultado mensurável. Integrações, automação e entregas incrementais com foco no negócio."
        />
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="$page.props.ziggy?.location ?? 'http://localhost'" />
        <script type="application/ld+json" v-html="faqJsonLd"></script>
    </Head>

    <div class="landing-shell min-h-screen bg-zinc-950 text-white selection:bg-indigo-500/30 selection:text-white">
        <div class="absolute inset-0 -z-10">
            <div class="animated-mesh absolute inset-0 opacity-80"></div>
            <div class="grain absolute inset-0 opacity-[0.18]"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-zinc-950/20 via-zinc-950 to-zinc-950"></div>
        </div>

        <header class="sticky top-0 z-40 border-b border-white/5 bg-zinc-950/35 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                <a href="#topo" class="flex items-center gap-3">
                    <div class="relative h-9 w-9 overflow-hidden rounded-xl bg-white/5 ring-1 ring-white/10">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_30%,rgba(99,102,241,0.75),transparent_55%),radial-gradient(circle_at_70%_60%,rgba(34,211,238,0.55),transparent_50%)]"></div>
                    </div>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold tracking-tight">MedidaTek</div>
                        <div class="truncate text-xs text-white/60">Software sob medida + IA</div>
                    </div>
                </a>

                <a
                    href="#contato"
                    class="btn-secondary inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold"
                >
                    Falar com especialistas
                </a>
            </div>
        </header>

        <main id="topo" class="mx-auto max-w-7xl px-4 pb-20 sm:px-6">
            <section class="grid gap-10 py-16 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-7">
                    <div data-reveal class="reveal inline-flex items-center gap-2 rounded-full bg-white/5 px-4 py-2 text-xs text-white/70 ring-1 ring-white/10">
                        <span class="rounded-full bg-indigo-500/20 px-2 py-0.5 text-indigo-200 ring-1 ring-indigo-400/30">Software + IA</span>
                        <span>Foco em resultado, não em buzzword</span>
                    </div>

                    <h1 data-reveal class="hero-title reveal mt-7 text-4xl font-semibold leading-tight tracking-tight sm:text-6xl">
                        Sistemas sob medida
                        <span class="bg-gradient-to-r from-indigo-200 via-cyan-200 to-emerald-200 bg-clip-text text-transparent">para destravar operação e vendas</span>.
                    </h1>

                    <p data-reveal class="reveal mt-5 max-w-2xl text-base leading-relaxed text-white/70">
                        Software sob medida com foco em impacto: integrações, automação e IA aplicada onde gera retorno.
                    </p>

                    <div data-reveal class="reveal mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <a
                            href="#contato"
                            class="btn-primary cta-shine inline-flex items-center justify-center rounded-2xl px-6 py-3 text-sm font-semibold text-white"
                        >
                            Quero falar com vocês
                        </a>
                        <a
                            href="#diferenciais"
                            class="btn-secondary inline-flex items-center justify-center rounded-2xl px-6 py-3 text-sm font-semibold"
                        >
                            Ver como trabalhamos
                        </a>
                    </div>

                    <div data-reveal class="reveal mt-3 text-xs text-white/60">
                        Diagnóstico direto: prioridade, escopo e faixa de investimento.
                    </div>

                    <div data-reveal class="reveal mt-7 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <div class="text-xs text-white/60">Clareza</div>
                            <div class="mt-1 text-sm font-semibold">Escopo que faz sentido</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <div class="text-xs text-white/60">Execução</div>
                            <div class="mt-1 text-sm font-semibold">Entrega incremental</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <div class="text-xs text-white/60">Resultado</div>
                            <div class="mt-1 text-sm font-semibold">Métricas e ROI</div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-semibold">Impacto no negócio</div>
                            <div class="text-xs text-white/60">referências típicas — validamos no diagnóstico</div>
                        </div>

                        <div class="mt-5 grid gap-3">
                            <div class="relative overflow-hidden rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                <div class="absolute -left-10 -top-10 h-28 w-28 rounded-full bg-indigo-500/15 blur-2xl"></div>
                                <div class="text-3xl font-semibold tracking-tight">+32%</div>
                                <div class="mt-1 text-xs text-white/60">Leads qualificados</div>
                            </div>
                            <div class="relative overflow-hidden rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-cyan-500/15 blur-2xl"></div>
                                <div class="text-3xl font-semibold tracking-tight">-40%</div>
                                <div class="mt-1 text-xs text-white/60">Retrabalho operacional</div>
                            </div>
                            <div class="relative overflow-hidden rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                <div class="absolute -left-10 -bottom-10 h-28 w-28 rounded-full bg-emerald-500/15 blur-2xl"></div>
                                <div class="text-3xl font-semibold tracking-tight">+18%</div>
                                <div class="mt-1 text-xs text-white/60">Conversão em fluxo digital</div>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl border border-white/10 bg-zinc-950/30 p-4">
                            <div class="text-xs text-white/60">O que isso significa na prática</div>
                            <div class="mt-2 space-y-2 text-sm text-white/75">
                                <div class="flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full bg-indigo-300"></span>
                                    <span>Follow-up automático e rastreável</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full bg-cyan-300"></span>
                                    <span>Integrações reais (CRM, WhatsApp, ERP)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                                    <span>Dashboards com decisões claras</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="hasProofLinks" class="pb-6">
                <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 px-6 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-white/60">Prova social</div>
                            <div class="mt-1 text-sm font-semibold">Projetos publicados</div>
                        </div>
                        <a href="#prova" class="text-sm font-semibold text-indigo-200 hover:text-white">
                            Ver todos →
                        </a>
                    </div>

                    <div class="mt-5 -mx-6 overflow-x-auto px-6">
                        <div class="flex gap-3 pb-2">
                            <a
                                v-for="item in featuredProofLinks"
                                :key="`featured-${item.url}`"
                                :href="item.url"
                                target="_blank"
                                rel="noopener noreferrer nofollow"
                                class="shrink-0 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 transition hover:bg-white/10"
                            >
                                <div class="max-w-[220px] truncate text-sm font-semibold">{{ item.name }}</div>
                                <div class="mt-1 text-xs text-white/60">{{ proofHost(item.url) }}</div>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="py-8">
                <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 px-6 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-white/60">Integrações</div>
                            <div class="mt-1 text-sm font-semibold">Entra no seu stack sem gambiarra</div>
                        </div>
                        <div class="marquee relative overflow-hidden">
                            <div class="marquee__track">
                                <div class="marquee__group">
                                    <span
                                        v-for="item in integrationList"
                                        :key="`a-${item}`"
                                        class="marquee__pill"
                                    >
                                        {{ item }}
                                    </span>
                                </div>
                                <div class="marquee__group" aria-hidden="true">
                                    <span
                                        v-for="item in integrationList"
                                        :key="`b-${item}`"
                                        class="marquee__pill"
                                    >
                                        {{ item }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="hasProofLinks" id="prova" class="py-10 scroll-mt-24">
                <div class="grid gap-6 lg:grid-cols-12 lg:items-start">
                    <div class="lg:col-span-4">
                        <div data-reveal class="reveal">
                            <h2 class="text-2xl font-semibold">Prova social</h2>
                            <p class="mt-2 text-sm text-white/70">Projetos publicados para você validar padrão e qualidade.</p>
                        </div>
                    </div>
                    <div class="grid gap-4 lg:col-span-8 sm:grid-cols-2">
                        <a
                            v-for="item in proofLinks"
                            :key="item.url"
                            :href="item.url"
                            target="_blank"
                            rel="noopener noreferrer nofollow"
                            class="group rounded-3xl border border-white/10 bg-white/5 p-6 transition hover:bg-white/10"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold">{{ item.name }}</div>
                                    <div class="mt-1 text-xs text-white/60">{{ proofHost(item.url) }}</div>
                                </div>
                                <div
                                    v-if="item.tag"
                                    class="shrink-0 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-semibold text-white/70"
                                >
                                    {{ item.tag }}
                                </div>
                            </div>
                            <div v-if="item.note" class="mt-3 text-sm text-white/70">
                                {{ item.note }}
                            </div>
                            <div class="mt-4 text-sm font-semibold text-indigo-200 group-hover:text-white">
                                Abrir projeto →
                            </div>
                        </a>
                    </div>
                </div>
            </section>

            <section class="border-y border-white/10 py-10">
                <div data-reveal class="reveal grid items-center gap-6 lg:grid-cols-12">
                    <div class="lg:col-span-4">
                        <div class="text-xs font-semibold text-white/60">Setores</div>
                        <div class="mt-2 text-lg font-semibold">quando o problema é processo, dado e decisão</div>
                    </div>
                    <div class="grid gap-3 lg:col-span-8 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white/80">Financeiro</div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white/80">Indústria</div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white/80">Serviços</div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white/80">Varejo</div>
                    </div>
                </div>
            </section>

            <section class="border-b border-white/10 py-14">
                <div class="grid gap-8 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <div data-reveal class="reveal">
                            <h2 class="text-2xl font-semibold">Cases e resultados</h2>
                            <p class="mt-2 text-sm text-white/70">
                                Contexto, decisão e impacto. No diagnóstico, mostramos exemplos relevantes (quando possível, sob NDA).
                            </p>
                        </div>
                        <div data-reveal class="reveal mt-6">
                            <a href="#contato" class="btn-primary inline-flex w-full items-center justify-center rounded-2xl px-6 py-3 text-sm font-semibold text-white sm:w-auto">
                                Quero ver cases relevantes
                            </a>
                        </div>
                    </div>
                    <div class="grid gap-4 lg:col-span-7 sm:grid-cols-2">
                        <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="text-xs font-semibold text-white/60">Financeiro</div>
                            <div class="mt-2 text-sm font-semibold">Pagamentos, conciliação, antifraude</div>
                            <div class="mt-2 text-sm text-white/70">KPIs: falhas no fluxo, tempo de conciliação, custo operacional, taxa de aprovação.</div>
                        </div>
                        <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="text-xs font-semibold text-white/60">Indústria</div>
                            <div class="mt-2 text-sm font-semibold">Integrações, ERP, rastreabilidade</div>
                            <div class="mt-2 text-sm text-white/70">KPIs: lead time, retrabalho, consistência do dado, visibilidade ponta a ponta.</div>
                        </div>
                        <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="text-xs font-semibold text-white/60">Serviços</div>
                            <div class="mt-2 text-sm font-semibold">Operação, atendimento, workflows</div>
                            <div class="mt-2 text-sm text-white/70">KPIs: tempo de resposta, produtividade, SLA, qualidade do atendimento.</div>
                        </div>
                        <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="text-xs font-semibold text-white/60">Varejo</div>
                            <div class="mt-2 text-sm font-semibold">E-commerce, catálogo, CRM</div>
                            <div class="mt-2 text-sm text-white/70">KPIs: conversão, recompra, carrinho, tempo de entrega e dados de cliente.</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="border-b border-white/10 py-14">
                <div class="grid gap-8 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <div data-reveal class="reveal">
                            <h2 class="text-2xl font-semibold">Design + engenharia</h2>
                            <p class="mt-2 text-sm text-white/70">
                                Clareza, velocidade e confiança em qualquer dispositivo.
                            </p>
                        </div>
                    </div>
                    <div class="grid gap-4 lg:col-span-7 sm:grid-cols-2">
                        <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="text-sm font-semibold">Performance real</div>
                            <div class="mt-2 text-sm text-white/70">Assets versionados, cache forte e carregamento previsível.</div>
                        </div>
                        <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="text-sm font-semibold">Acessibilidade</div>
                            <div class="mt-2 text-sm text-white/70">Contraste, foco visível, hierarquia e leitura rápida.</div>
                        </div>
                        <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="text-sm font-semibold">Segurança e base</div>
                            <div class="mt-2 text-sm text-white/70">Back-end robusto, autenticação e rate limit onde importa.</div>
                        </div>
                        <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="text-sm font-semibold">SEO e estrutura</div>
                            <div class="mt-2 text-sm text-white/70">Meta tags, FAQ schema e copy orientada por intenção.</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="py-14">
                <div class="grid gap-10 lg:grid-cols-12 lg:items-start">
                    <div class="lg:col-span-5">
                        <div data-reveal class="reveal">
                            <h2 class="text-2xl font-semibold">Veja o impacto antes de construir</h2>
                            <p class="mt-2 text-sm text-white/70">
                                Um modelo simples para visualizar potencial e alinhar prioridade. Ajuste os números e enxergue o efeito.
                            </p>
                        </div>
                        <div data-reveal class="reveal mt-6 rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-sm font-semibold">Modo</div>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-white/10"
                                        :class="calcMode === 'vendas' ? 'bg-white/15 text-white' : 'bg-white/5 text-white/70 hover:bg-white/10'"
                                        @click="calcMode = 'vendas'"
                                    >
                                        Vendas
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-white/10"
                                        :class="calcMode === 'processos' ? 'bg-white/15 text-white' : 'bg-white/5 text-white/70 hover:bg-white/10'"
                                        @click="calcMode = 'processos'"
                                    >
                                        Processos
                                    </button>
                                </div>
                            </div>

                            <div v-if="calcMode === 'vendas'" class="mt-5 grid gap-4">
                                <div class="grid gap-2">
                                    <div class="flex items-center justify-between text-xs text-white/70">
                                        <div>Leads/mês</div>
                                        <div class="font-semibold text-white">{{ calcLeads }}</div>
                                    </div>
                                    <input v-model.number="calcLeads" type="range" min="50" max="5000" step="10" class="range" />
                                </div>
                                <div class="grid gap-2">
                                    <div class="flex items-center justify-between text-xs text-white/70">
                                        <div>Conversão (%)</div>
                                        <div class="font-semibold text-white">{{ calcCloseRate }}</div>
                                    </div>
                                    <input v-model.number="calcCloseRate" type="range" min="1" max="40" step="1" class="range" />
                                </div>
                                <div class="grid gap-2">
                                    <div class="flex items-center justify-between text-xs text-white/70">
                                        <div>Ticket médio (R$)</div>
                                        <div class="font-semibold text-white">{{ calcTicket }}</div>
                                    </div>
                                    <input v-model.number="calcTicket" type="range" min="300" max="50000" step="100" class="range" />
                                </div>
                            </div>

                            <div v-else class="mt-5 grid gap-4">
                                <div class="grid gap-2">
                                    <div class="flex items-center justify-between text-xs text-white/70">
                                        <div>Horas/mês hoje</div>
                                        <div class="font-semibold text-white">{{ calcHours }}</div>
                                    </div>
                                    <input v-model.number="calcHours" type="range" min="10" max="800" step="5" class="range" />
                                </div>
                                <div class="grid gap-2">
                                    <div class="flex items-center justify-between text-xs text-white/70">
                                        <div>Custo/hora (R$)</div>
                                        <div class="font-semibold text-white">{{ calcHourlyCost }}</div>
                                    </div>
                                    <input v-model.number="calcHourlyCost" type="range" min="20" max="350" step="5" class="range" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-7">
                        <div data-reveal class="reveal grid gap-4 sm:grid-cols-2">
                            <div class="glow-card rounded-3xl border border-white/10 bg-white/5 p-6">
                                <div class="text-xs font-semibold text-white/60">Hoje</div>
                                <div v-if="calcMode === 'vendas'" class="mt-3 text-2xl font-semibold tracking-tight">
                                    {{ formatBRL(baselineRevenue) }}<span class="ml-2 text-xs font-semibold text-white/60">/mês</span>
                                </div>
                                <div v-else class="mt-3 text-2xl font-semibold tracking-tight">
                                    {{ formatBRL(baselineOpsCost) }}<span class="ml-2 text-xs font-semibold text-white/60">/mês</span>
                                </div>
                                <div class="mt-2 text-sm text-white/70">
                                    <span v-if="calcMode === 'vendas'">Receita estimada pelo seu funil atual.</span>
                                    <span v-else>Custo estimado do esforço operacional atual.</span>
                                </div>
                            </div>

                            <div class="glow-card rounded-3xl border border-white/10 bg-white/5 p-6">
                                <div class="text-xs font-semibold text-white/60">Com sistema + IA</div>
                                <div v-if="calcMode === 'vendas'" class="mt-3 text-2xl font-semibold tracking-tight">
                                    {{ formatBRL(projectedRevenue) }}<span class="ml-2 text-xs font-semibold text-white/60">/mês</span>
                                </div>
                                <div v-else class="mt-3 text-2xl font-semibold tracking-tight">
                                    {{ formatBRL(projectedOpsCost) }}<span class="ml-2 text-xs font-semibold text-white/60">/mês</span>
                                </div>
                                <div class="mt-2 text-sm text-white/70">
                                    <span v-if="calcMode === 'vendas'">Aumento por automação, follow-up e UX no fluxo.</span>
                                    <span v-else>Redução por automação e eliminação de retrabalho.</span>
                                </div>
                            </div>
                        </div>

                        <div data-reveal class="reveal mt-4 rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div class="text-sm font-semibold">Impacto estimado</div>
                                <div class="text-xs text-white/60">Hipóteses comuns: +32% leads / +18% conversão / -40% retrabalho</div>
                            </div>

                            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                                <div class="rounded-2xl border border-white/10 bg-zinc-950/30 p-4">
                                    <div class="text-xs text-white/60">Ganho</div>
                                    <div v-if="calcMode === 'vendas'" class="mt-2 text-lg font-semibold">{{ formatBRL(revenueDelta) }}/mês</div>
                                    <div v-else class="mt-2 text-lg font-semibold">{{ formatBRL(opsDelta) }}/mês</div>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-zinc-950/30 p-4">
                                    <div class="text-xs text-white/60">Principal driver</div>
                                    <div class="mt-2 text-sm font-semibold">
                                        <span v-if="calcMode === 'vendas'">Follow-up + fricção menor</span>
                                        <span v-else>Automação + integração</span>
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-zinc-950/30 p-4">
                                    <div class="text-xs text-white/60">Próximo passo</div>
                                    <a href="#contato" class="mt-2 inline-flex text-sm font-semibold text-indigo-200 hover:text-white">Pedir diagnóstico →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="pb-4">
                <div data-reveal class="reveal overflow-hidden rounded-3xl border border-white/10 bg-white/5">
                    <div class="relative px-6 py-6">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_10%,rgba(99,102,241,0.18),transparent_55%),radial-gradient(circle_at_85%_30%,rgba(34,211,238,0.12),transparent_55%),radial-gradient(circle_at_50%_120%,rgba(16,185,129,0.10),transparent_55%)]"></div>
                        <div class="relative grid gap-4 lg:grid-cols-12 lg:items-center">
                            <div class="lg:col-span-7">
                                <div class="text-xs font-semibold text-white/60">Próximo passo</div>
                                <div class="mt-2 text-lg font-semibold tracking-tight">
                                    Se faz sentido no número, faz sentido no sistema.
                                </div>
                                <div class="mt-2 text-sm text-white/70">
                                    A gente transforma a hipótese em escopo e entrega incremental, com foco em conversão e previsibilidade.
                                </div>
                            </div>
                            <div class="flex flex-col gap-3 lg:col-span-5 lg:items-end sm:flex-row sm:items-center">
                                <a
                                    href="#contato"
                                    class="btn-primary inline-flex w-full items-center justify-center rounded-2xl px-6 py-3 text-sm font-semibold text-white sm:w-auto"
                                >
                                    Começar diagnóstico
                                </a>
                                <a
                                    href="#diferenciais"
                                    class="btn-secondary inline-flex w-full items-center justify-center rounded-2xl px-6 py-3 text-sm font-semibold sm:w-auto"
                                >
                                    Ver diferenciais
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 border-t border-white/10 py-14 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <h2 class="text-2xl font-semibold">O que entregamos</h2>
                    <p class="mt-2 text-sm text-white/70">Produto completo: design, engenharia, integrações, automação e métricas.</p>
                </div>

                <div class="grid gap-4 lg:col-span-8 sm:grid-cols-2">
                    <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6 hover:bg-white/10">
                        <div class="text-sm font-semibold">Sistemas sob medida</div>
                        <div class="mt-2 text-sm text-white/70">Portais, dashboards, CRM interno, backoffice, assinatura, billing e áreas do cliente.</div>
                    </div>
                    <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6 hover:bg-white/10">
                        <div class="text-sm font-semibold">Integrações e automação</div>
                        <div class="mt-2 text-sm text-white/70">Conecta dados e dispara ações: CRM, WhatsApp, e-mail, ERP, gateways e APIs.</div>
                    </div>
                    <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6 hover:bg-white/10">
                        <div class="text-sm font-semibold">Design que vende</div>
                        <div class="mt-2 text-sm text-white/70">Hierarquia visual, microcopy, microinterações e experiência consistente no sistema inteiro.</div>
                    </div>
                    <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6 hover:bg-white/10">
                        <div class="text-sm font-semibold">IA aplicada (de verdade)</div>
                        <div class="mt-2 text-sm text-white/70">Automação, triagem, recomendações e insights auditáveis — sem “IA por IA”.</div>
                    </div>
                </div>
            </section>

            <section class="border-t border-white/10 py-14">
                <h2 class="text-2xl font-semibold">Como isso vira realidade</h2>
                <div class="mt-6 grid gap-4 lg:grid-cols-5">
                    <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                        <div class="text-sm font-semibold">Diagnóstico</div>
                        <div class="mt-2 text-sm text-white/70">Mapeamos objetivo, gargalos e dados. Definimos KPIs.</div>
                    </div>
                    <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                        <div class="text-sm font-semibold">Arquitetura</div>
                        <div class="mt-2 text-sm text-white/70">Roadmap, integrações e decisões técnicas que não quebram depois.</div>
                    </div>
                    <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                        <div class="text-sm font-semibold">Entrega</div>
                        <div class="mt-2 text-sm text-white/70">Versões curtas com validação. Você vê valor cedo.</div>
                    </div>
                    <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                        <div class="text-sm font-semibold">Qualidade</div>
                        <div class="mt-2 text-sm text-white/70">Performance, segurança e UX. IA com controle e auditoria.</div>
                    </div>
                    <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                        <div class="text-sm font-semibold">Evolução</div>
                        <div class="mt-2 text-sm text-white/70">Deploy, observabilidade e melhorias contínuas com dados.</div>
                    </div>
                </div>
            </section>

            <section id="diferenciais" class="border-t border-white/10 py-14">
                <div class="grid gap-8 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <h2 class="text-2xl font-semibold">Diferenciais</h2>
                        <p class="mt-2 text-sm text-white/70">O que separa “bonito” de “profissional” é engenharia + método.</p>
                    </div>
                    <div class="grid gap-4 lg:col-span-7 sm:grid-cols-2">
                        <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="text-sm font-semibold">Precisão visual</div>
                            <div class="mt-2 text-sm text-white/70">Sistema alinhado, consistente e com acabamento premium.</div>
                        </div>
                        <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="text-sm font-semibold">Psicologia aplicada</div>
                        <div class="mt-2 text-sm text-white/70">Clareza, hierarquia, prova e risco controlado — persuasão sem truque.</div>
                        </div>
                        <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="text-sm font-semibold">IA com governança</div>
                            <div class="mt-2 text-sm text-white/70">Auditoria, limites e foco no resultado: custo menor, receita maior.</div>
                        </div>
                        <div data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="text-sm font-semibold">Entrega previsível</div>
                            <div class="mt-2 text-sm text-white/70">Roadmap, métricas e evolução contínua — sem surpresa.</div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="contato" class="border-t border-white/10 py-14">
                <div class="grid gap-10 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <h2 class="text-2xl font-semibold">Fale com especialistas</h2>
                        <p class="mt-2 text-sm text-white/70">
                            Você responde 4 perguntas. A gente retorna com escopo sugerido, riscos, próximos passos e uma faixa de investimento realista.
                        </p>

                        <div data-reveal class="reveal mt-6 grid gap-3">
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-white/80">
                                <div class="text-xs font-semibold text-white/60">Você recebe</div>
                                <div class="mt-2 space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="h-1.5 w-1.5 rounded-full bg-indigo-300"></span>
                                        <span>Escopo sugerido (o mínimo que gera impacto)</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="h-1.5 w-1.5 rounded-full bg-cyan-300"></span>
                                        <span>Roadmap por entregas + métricas</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                                        <span>Riscos e decisões técnicas importantes</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="$page.props.flash?.success || sentOnce"
                            class="mt-6 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 p-4 text-sm text-emerald-200"
                        >
                            {{ $page.props.flash?.success ?? 'Recebemos seu contato. Próximo passo: diagnóstico e proposta objetiva.' }}
                        </div>
                        <div
                            v-else-if="$page.props.flash?.error"
                            class="mt-6 rounded-2xl border border-rose-400/20 bg-rose-500/10 p-4 text-sm text-rose-200"
                        >
                            {{ $page.props.flash.error }}
                        </div>
                    </div>

                    <div class="lg:col-span-7">
                        <form class="rounded-3xl border border-white/10 bg-white/5 p-6" @submit.prevent="submit">
                            <div class="flex items-center justify-between gap-4">
                                <div class="grid">
                                    <div class="text-sm font-semibold">Diagnóstico rápido</div>
                                    <div class="mt-1 text-xs text-white/60">Etapa {{ step }} de 4</div>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-white/60">
                                    <button
                                        type="button"
                                        class="rounded-full px-2 py-1 hover:text-white"
                                        :class="step === 1 ? 'bg-white/10 text-white ring-1 ring-white/10' : ''"
                                        @click="setStep(1)"
                                    >
                                        1
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-full px-2 py-1 hover:text-white"
                                        :class="step === 2 ? 'bg-white/10 text-white ring-1 ring-white/10' : ''"
                                        @click="setStep(2)"
                                    >
                                        2
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-full px-2 py-1 hover:text-white"
                                        :class="step === 3 ? 'bg-white/10 text-white ring-1 ring-white/10' : ''"
                                        @click="setStep(3)"
                                    >
                                        3
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-full px-2 py-1 hover:text-white"
                                        :class="step === 4 ? 'bg-white/10 text-white ring-1 ring-white/10' : ''"
                                        @click="setStep(4)"
                                    >
                                        4
                                    </button>
                                </div>
                            </div>

                            <div class="mt-5">
                                <div v-if="step === 1" class="space-y-4">
                                    <div>
                                        <label class="text-xs font-semibold text-white/70">Qual é a prioridade agora?</label>
                                        <select
                                            v-model="form.pain"
                                            class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/30"
                                        >
                                            <option value="" class="bg-zinc-900">Selecione</option>
                                            <option value="vendas" class="bg-zinc-900">Aumentar vendas / conversão</option>
                                            <option value="processos" class="bg-zinc-900">Otimizar processos internos</option>
                                            <option value="integracoes" class="bg-zinc-900">Integrações e automação</option>
                                            <option value="ia" class="bg-zinc-900">IA aplicada (chat, recomendações, insights)</option>
                                        </select>
                                        <div v-if="clientErrors.pain || form.errors.pain" class="mt-2 text-xs text-rose-200">
                                            {{ clientErrors.pain ?? form.errors.pain }}
                                        </div>
                                    </div>
                                </div>

                                <div v-else-if="step === 2" class="grid gap-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label class="text-xs font-semibold text-white/70">Nome</label>
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            autocomplete="name"
                                            class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-white/40 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/30"
                                            placeholder="Seu nome"
                                        />
                                        <div v-if="clientErrors.name || form.errors.name" class="mt-2 text-xs text-rose-200">
                                            {{ clientErrors.name ?? form.errors.name }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="text-xs font-semibold text-white/70">E-mail</label>
                                        <input
                                            v-model="form.email"
                                            type="email"
                                            autocomplete="email"
                                            class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-white/40 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/30"
                                            placeholder="voce@empresa.com"
                                        />
                                        <div v-if="clientErrors.email || form.errors.email" class="mt-2 text-xs text-rose-200">
                                            {{ clientErrors.email ?? form.errors.email }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="text-xs font-semibold text-white/70">WhatsApp</label>
                                        <input
                                            v-model="form.whatsapp"
                                            type="tel"
                                            inputmode="tel"
                                            autocomplete="tel"
                                            class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-white/40 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/30"
                                            placeholder="(xx) xxxxx-xxxx"
                                        />
                                        <div v-if="clientErrors.whatsapp || form.errors.whatsapp" class="mt-2 text-xs text-rose-200">
                                            {{ clientErrors.whatsapp ?? form.errors.whatsapp }}
                                        </div>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="flex items-start gap-3 text-xs text-white/70">
                                            <input
                                                v-model="form.contact_consent"
                                                type="checkbox"
                                                class="mt-0.5 h-4 w-4 rounded border-white/20 bg-white/10 text-indigo-500 focus:ring-indigo-400/30"
                                            />
                                            <span>Concordo em receber contato por e-mail/WhatsApp sobre este diagnóstico.</span>
                                        </label>
                                        <div v-if="clientErrors.contact_consent || form.errors.contact_consent" class="mt-2 text-xs text-rose-200">
                                            {{ clientErrors.contact_consent ?? form.errors.contact_consent }}
                                        </div>
                                    </div>
                                </div>

                                <div v-else-if="step === 3" class="grid gap-4">
                                    <div>
                                        <label class="text-xs font-semibold text-white/70">Empresa (opcional)</label>
                                        <input
                                            v-model="form.company"
                                            type="text"
                                            autocomplete="organization"
                                            class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-white/40 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/30"
                                            placeholder="Nome da empresa"
                                        />
                                    </div>

                                    <div>
                                        <label class="text-xs font-semibold text-white/70">Conte em 1–2 frases</label>
                                        <textarea
                                            v-model="form.message"
                                            rows="4"
                                            class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-white/40 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/30"
                                            placeholder="O que você quer construir e qual resultado espera?"
                                        ></textarea>
                                        <div v-if="form.errors.message" class="mt-2 text-xs text-rose-200">
                                            {{ form.errors.message }}
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="grid gap-4">
                                    <div>
                                        <label class="text-xs font-semibold text-white/70">Pretensão de investimento</label>
                                        <select
                                            v-model="form.investment_range"
                                            class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/30"
                                        >
                                            <option value="" class="bg-zinc-900">Selecione</option>
                                            <option value="ate_15k" class="bg-zinc-900">Até R$ 15 mil (MVP simples)</option>
                                            <option value="15k_30k" class="bg-zinc-900">R$ 15–30 mil (primeira versão completa)</option>
                                            <option value="30k_60k" class="bg-zinc-900">R$ 30–60 mil (integrações + automação)</option>
                                            <option value="60k_120k" class="bg-zinc-900">R$ 60–120 mil (produto robusto)</option>
                                            <option value="120k_plus" class="bg-zinc-900">R$ 120 mil+ (escala/complexidade)</option>
                                            <option value="nao_sei" class="bg-zinc-900">Quero entender o melhor caminho</option>
                                        </select>
                                        <div
                                            v-if="clientErrors.investment_range || form.errors.investment_range"
                                            class="mt-2 text-xs text-rose-200"
                                        >
                                            {{ clientErrors.investment_range ?? form.errors.investment_range }}
                                        </div>
                                        <div class="mt-2 text-xs text-white/60">
                                            Só para dimensionar o escopo. Você não fica preso a uma faixa.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="text-xs text-white/60">Retorno com escopo e próximos passos.</div>
                                <div class="flex flex-col gap-2 sm:flex-row">
                                    <button
                                        type="button"
                                        class="btn-secondary inline-flex items-center justify-center rounded-2xl px-6 py-3 text-sm font-semibold"
                                        :disabled="step === 1 || form.processing"
                                        @click="prevStep"
                                    >
                                        Voltar
                                    </button>
                                    <button
                                        v-if="step !== 4"
                                        type="button"
                                        class="btn-secondary inline-flex items-center justify-center rounded-2xl px-6 py-3 text-sm font-semibold"
                                        :disabled="form.processing"
                                        @click="nextStep"
                                    >
                                        Continuar
                                    </button>
                                    <button
                                        v-else
                                        type="submit"
                                        class="btn-primary inline-flex items-center justify-center rounded-2xl px-6 py-3 text-sm font-semibold text-white disabled:opacity-50"
                                        :disabled="form.processing"
                                    >
                                        Enviar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <section class="border-t border-white/10 py-14">
                <h2 class="text-2xl font-semibold">FAQ</h2>
                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    <details data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                        <summary class="cursor-pointer text-sm font-semibold">Quanto custa um sistema sob medida?</summary>
                        <div class="mt-3 text-sm text-white/70">Depende de escopo, integrações e automação/IA. No diagnóstico a gente recomenda um escopo enxuto e uma faixa de investimento realista.</div>
                    </details>
                    <details data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                        <summary class="cursor-pointer text-sm font-semibold">Qual é o prazo médio?</summary>
                        <div class="mt-3 text-sm text-white/70">Trabalhamos com entregas incrementais: você vê valor cedo e evolui por etapas, com roadmap claro.</div>
                    </details>
                    <details data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                        <summary class="cursor-pointer text-sm font-semibold">Vocês integram com ferramentas que já uso?</summary>
                        <div class="mt-3 text-sm text-white/70">Sim. Integramos CRMs, gateways, WhatsApp, e-mail e APIs externas, com rastreio e reprocessamento quando necessário.</div>
                    </details>
                    <details data-reveal class="reveal rounded-3xl border border-white/10 bg-white/5 p-6">
                        <summary class="cursor-pointer text-sm font-semibold">Como a IA é aplicada com segurança?</summary>
                        <div class="mt-3 text-sm text-white/70">Com minimização de dados, rate limit, auditoria e sem enviar informações sensíveis para o provedor.</div>
                    </details>
                </div>
            </section>
        </main>

        <footer class="border-t border-white/10 py-10">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 text-sm text-white/60 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>© {{ year }} MedidaTek</div>
                <div class="flex gap-4">
                    <a href="#contato" class="hover:text-white">Contato</a>
                    <a href="#diferenciais" class="hover:text-white">Diferenciais</a>
                </div>
            </div>
        </footer>

        <AiChatWidget />

        <div class="pointer-events-none fixed inset-x-0 bottom-0 z-40 sm:hidden">
            <div class="pointer-events-auto border-t border-white/10 bg-zinc-950/70 px-4 pb-[calc(env(safe-area-inset-bottom)+12px)] pt-3 backdrop-blur">
                <a href="#contato" class="btn-primary inline-flex w-full items-center justify-center rounded-2xl px-6 py-3 text-sm font-semibold text-white">
                    Falar com especialistas
                </a>
            </div>
        </div>
    </div>
</template>

<style scoped>
.landing-shell {
    --accent: 99 102 241;
    --accent2: 34 211 238;
    --success: 16 185 129;
    -webkit-tap-highlight-color: transparent;
}

.btn-primary {
    background: linear-gradient(135deg, rgb(var(--accent)) 0%, rgba(var(--accent2), 0.95) 55%, rgba(var(--success), 0.9) 110%);
    box-shadow:
        0 18px 40px rgba(var(--accent), 0.22),
        0 8px 20px rgba(0, 0, 0, 0.45);
    transition:
        transform 180ms ease,
        filter 180ms ease,
        box-shadow 180ms ease;
}

.btn-primary:hover {
    filter: saturate(1.08);
    transform: translateY(-1px);
    box-shadow:
        0 22px 52px rgba(var(--accent), 0.26),
        0 10px 26px rgba(0, 0, 0, 0.5);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.06);
    color: rgba(255, 255, 255, 0.88);
    border: 1px solid rgba(255, 255, 255, 0.12);
    transition:
        transform 180ms ease,
        background 180ms ease,
        border-color 180ms ease;
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.16);
    transform: translateY(-1px);
}

.hero-title {
    text-wrap: balance;
}

.animated-mesh {
    background:
        radial-gradient(circle at 12% 18%, rgba(99, 102, 241, 0.32), transparent 38%),
        radial-gradient(circle at 88% 22%, rgba(34, 211, 238, 0.22), transparent 40%),
        radial-gradient(circle at 55% 90%, rgba(16, 185, 129, 0.16), transparent 44%),
        radial-gradient(circle at 60% 40%, rgba(236, 72, 153, 0.14), transparent 45%);
    filter: saturate(1.15);
    animation: meshMove 16s ease-in-out infinite;
}

.grain {
    background-image:
        radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.045), transparent 35%),
        radial-gradient(circle at 60% 65%, rgba(255, 255, 255, 0.03), transparent 40%),
        repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.016) 0 1px, transparent 1px 2px);
    mix-blend-mode: overlay;
    filter: blur(0.2px);
}

@keyframes meshMove {
    0% {
        transform: translate3d(0, 0, 0) scale(1);
    }
    50% {
        transform: translate3d(0, -10px, 0) scale(1.03);
    }
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }
}

.reveal {
    opacity: 0;
    transform: translateY(10px);
    transition:
        opacity 700ms cubic-bezier(0.2, 0.8, 0.2, 1),
        transform 700ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.reveal-in {
    opacity: 1;
    transform: translateY(0);
}

.cta-shine {
    position: relative;
    overflow: hidden;
}

.cta-shine::after {
    content: '';
    position: absolute;
    inset: -2px;
    background: linear-gradient(110deg, transparent 0%, rgba(255, 255, 255, 0.25) 45%, transparent 55%);
    transform: translateX(-120%);
    animation: shine 3.2s ease-in-out infinite;
    pointer-events: none;
}

@keyframes shine {
    0% {
        transform: translateX(-120%);
    }
    45% {
        transform: translateX(-120%);
    }
    85% {
        transform: translateX(120%);
    }
    100% {
        transform: translateX(120%);
    }
}

@keyframes marquee {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

.marquee {
    width: 100%;
    max-width: 780px;
    -webkit-mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
    mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
}

.marquee__track {
    display: flex;
    width: max-content;
    gap: 10px;
    animation: marquee 22s linear infinite;
}

.marquee__group {
    display: flex;
    gap: 10px;
    padding-right: 10px;
}

.marquee__pill {
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    border-radius: 9999px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.06);
    padding: 8px 12px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.82);
}

.glow-card {
    position: relative;
    overflow: hidden;
}

.glow-card::before {
    content: '';
    position: absolute;
    inset: -2px;
    background: radial-gradient(circle at 20% 15%, rgba(99, 102, 241, 0.18), transparent 55%),
        radial-gradient(circle at 80% 30%, rgba(34, 211, 238, 0.14), transparent 55%),
        radial-gradient(circle at 40% 90%, rgba(16, 185, 129, 0.12), transparent 55%);
    filter: blur(14px);
    pointer-events: none;
}

.range {
    width: 100%;
    accent-color: rgb(var(--accent));
}

@media (prefers-reduced-motion: reduce) {
    .animated-mesh,
    .cta-shine::after,
    .marquee__track {
        animation: none;
    }

    .reveal {
        opacity: 1;
        transform: none;
        transition: none;
    }

    .btn-primary,
    .btn-secondary {
        transition: none;
        transform: none;
    }
}
</style>
