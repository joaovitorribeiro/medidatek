<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import AiChatWidget from '@/Components/Marketing/AiChatWidget.vue';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();

const sentOnce = ref(false);

const form = useForm({
    name: '',
    email: '',
    whatsapp: '',
    company: '',
    pain: '',
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
});

function submit() {
    form.post(route('leads.store'), {
        preserveScroll: true,
        onSuccess: () => {
            sentOnce.value = true;
            form.reset('name', 'email', 'whatsapp', 'company', 'pain', 'message', 'contact_consent');
        },
    });
}
</script>

<template>
    <Head title="MedidaTeck — Sistemas sob medida com IA">
        <meta
            name="description"
            content="Sistemas sob medida com IA para aumentar vendas e otimizar processos. UX/UI que converte, integrações e automação."
        />
    </Head>

    <div class="min-h-screen bg-zinc-950 text-white">
        <div class="absolute inset-0 -z-10">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_10%_10%,rgba(99,102,241,0.25),transparent_35%),radial-gradient(circle_at_90%_20%,rgba(236,72,153,0.18),transparent_40%),radial-gradient(circle_at_50%_90%,rgba(34,197,94,0.10),transparent_45%)]"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-zinc-950/20 via-zinc-950 to-zinc-950"></div>
        </div>

        <header class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-6 py-6">
            <a href="#topo" class="flex items-center gap-2">
                <div class="h-9 w-9 rounded-xl bg-indigo-500/20 ring-1 ring-indigo-400/30"></div>
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold">MedidaTeck</div>
                    <div class="truncate text-xs text-white/60">Sistemas sob medida + IA</div>
                </div>
            </a>

            <nav v-if="canLogin" class="flex items-center gap-2">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white ring-1 ring-white/10 hover:bg-white/15"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="route('login')"
                        class="rounded-xl px-4 py-2 text-sm font-semibold text-white/80 hover:text-white"
                    >
                        Entrar
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white ring-1 ring-white/10 hover:bg-white/15"
                    >
                        Criar conta
                    </Link>
                </template>
            </nav>
        </header>

        <main id="topo" class="mx-auto max-w-7xl px-6 pb-20">
            <section class="grid gap-10 py-14 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-7">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/5 px-4 py-2 text-xs text-white/70 ring-1 ring-white/10">
                        <span class="rounded-full bg-indigo-500/20 px-2 py-0.5 text-indigo-200 ring-1 ring-indigo-400/30">IA</span>
                        <span>Automação, personalização e insights aplicados ao seu negócio</span>
                    </div>

                    <h1 class="mt-6 text-4xl font-semibold leading-tight tracking-tight sm:text-5xl">
                        Sistemas sob medida que aumentam suas vendas e otimizam processos
                    </h1>

                    <p class="mt-5 max-w-2xl text-base leading-relaxed text-white/70">
                        Transformamos ideias em produtos digitais com UX/UI que converte, integrações reais e inteligência artificial aplicada para automatizar, personalizar e gerar clareza de decisão.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <a
                            href="#contato"
                            class="inline-flex items-center justify-center rounded-2xl bg-indigo-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 hover:bg-indigo-400"
                        >
                            Solicitar orçamento agora
                        </a>
                        <a
                            href="#diferenciais"
                            class="inline-flex items-center justify-center rounded-2xl bg-white/10 px-6 py-3 text-sm font-semibold text-white ring-1 ring-white/10 hover:bg-white/15"
                        >
                            Ver diferenciais
                        </a>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3 text-xs text-white/60">
                        <div class="rounded-full bg-white/5 px-4 py-2 ring-1 ring-white/10">Sem template genérico</div>
                        <div class="rounded-full bg-white/5 px-4 py-2 ring-1 ring-white/10">Entrega incremental</div>
                        <div class="rounded-full bg-white/5 px-4 py-2 ring-1 ring-white/10">Métricas e transparência</div>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl">
                        <div class="text-sm font-semibold">Resultados que importam</div>
                        <div class="mt-1 text-xs text-white/60">Exemplos de impacto (substituir por cases reais)</div>

                        <div class="mt-5 grid gap-3">
                            <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                <div class="text-2xl font-semibold">+32%</div>
                                <div class="mt-1 text-xs text-white/60">Aumento de leads qualificados</div>
                            </div>
                            <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                <div class="text-2xl font-semibold">-40%</div>
                                <div class="mt-1 text-xs text-white/60">Redução de retrabalho operacional</div>
                            </div>
                            <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                <div class="text-2xl font-semibold">+18%</div>
                                <div class="mt-1 text-xs text-white/60">Ganho de conversão em fluxo digital</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 border-t border-white/10 py-14 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <h2 class="text-2xl font-semibold">Serviços</h2>
                    <p class="mt-2 text-sm text-white/70">Tudo pensado para gerar resultado mensurável e escalável.</p>
                </div>

                <div class="grid gap-4 lg:col-span-8 sm:grid-cols-2">
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 hover:bg-white/10">
                        <div class="text-sm font-semibold">Sistema sob medida</div>
                        <div class="mt-2 text-sm text-white/70">Portais, dashboards, automações internas, fluxos de venda e backoffice.</div>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 hover:bg-white/10">
                        <div class="text-sm font-semibold">Integrações e automação</div>
                        <div class="mt-2 text-sm text-white/70">CRMs, gateways, WhatsApp, e-mail, ERPs e APIs externas.</div>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 hover:bg-white/10">
                        <div class="text-sm font-semibold">UX/UI que converte</div>
                        <div class="mt-2 text-sm text-white/70">Design persuasivo, microcopy, hierarquia visual e microinterações.</div>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 hover:bg-white/10">
                        <div class="text-sm font-semibold">IA aplicada ao negócio</div>
                        <div class="mt-2 text-sm text-white/70">Chat, recomendações, personalização controlada e insights do funil.</div>
                    </div>
                </div>
            </section>

            <section class="border-t border-white/10 py-14">
                <h2 class="text-2xl font-semibold">Como fazemos</h2>
                <div class="mt-6 grid gap-4 lg:grid-cols-5">
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <div class="text-sm font-semibold">Diagnóstico</div>
                        <div class="mt-2 text-sm text-white/70">Objetivos, dores e métricas de sucesso.</div>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <div class="text-sm font-semibold">Planejamento</div>
                        <div class="mt-2 text-sm text-white/70">Arquitetura, roadmap e integrações.</div>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <div class="text-sm font-semibold">Ágil</div>
                        <div class="mt-2 text-sm text-white/70">Entregas incrementais com feedback.</div>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <div class="text-sm font-semibold">Qualidade</div>
                        <div class="mt-2 text-sm text-white/70">Performance, segurança, UX e validação de IA.</div>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <div class="text-sm font-semibold">Entrega</div>
                        <div class="mt-2 text-sm text-white/70">Deploy, monitoramento e evolução contínua.</div>
                    </div>
                </div>
            </section>

            <section id="diferenciais" class="border-t border-white/10 py-14">
                <div class="grid gap-8 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <h2 class="text-2xl font-semibold">Diferenciais</h2>
                        <p class="mt-2 text-sm text-white/70">Menos promessa, mais entrega com mensuração.</p>
                    </div>
                    <div class="grid gap-4 lg:col-span-7 sm:grid-cols-2">
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="text-sm font-semibold">Personalização real</div>
                            <div class="mt-2 text-sm text-white/70">Sem “template genérico” disfarçado.</div>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="text-sm font-semibold">Conversão no centro</div>
                            <div class="mt-2 text-sm text-white/70">Copy, UX e fricção pensados para decisão.</div>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="text-sm font-semibold">IA útil</div>
                            <div class="mt-2 text-sm text-white/70">Automação e insights que reduzem custo e aumentam ROI.</div>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="text-sm font-semibold">Transparência</div>
                            <div class="mt-2 text-sm text-white/70">Roadmap, métricas e evolução contínua.</div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="contato" class="border-t border-white/10 py-14">
                <div class="grid gap-10 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <h2 class="text-2xl font-semibold">Fale com um especialista</h2>
                        <p class="mt-2 text-sm text-white/70">
                            Responda 3 coisas e a gente te retorna com um direcionamento técnico e próximo passo.
                        </p>

                        <div
                            v-if="$page.props.flash?.success || sentOnce"
                            class="mt-6 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 p-4 text-sm text-emerald-200"
                        >
                            {{ $page.props.flash?.success ?? 'Recebemos seu contato. Em breve um especialista fala com você.' }}
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
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="text-xs font-semibold text-white/70">Nome</label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-white/40 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/30"
                                        placeholder="Seu nome"
                                    />
                                    <div v-if="form.errors.name" class="mt-2 text-xs text-rose-200">
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-white/70">E-mail</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-white/40 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/30"
                                        placeholder="voce@empresa.com"
                                    />
                                    <div v-if="form.errors.email" class="mt-2 text-xs text-rose-200">
                                        {{ form.errors.email }}
                                    </div>
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-white/70">WhatsApp</label>
                                    <input
                                        v-model="form.whatsapp"
                                        type="text"
                                        class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-white/40 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/30"
                                        placeholder="(xx) xxxxx-xxxx"
                                    />
                                    <div v-if="form.errors.whatsapp" class="mt-2 text-xs text-rose-200">
                                        {{ form.errors.whatsapp }}
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="text-xs font-semibold text-white/70">Empresa (opcional)</label>
                                    <input
                                        v-model="form.company"
                                        type="text"
                                        class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-white/40 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/30"
                                        placeholder="Nome da empresa"
                                    />
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="text-xs font-semibold text-white/70">Prioridade</label>
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
                                    <div v-if="form.errors.pain" class="mt-2 text-xs text-rose-200">
                                        {{ form.errors.pain }}
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
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

                                <div class="sm:col-span-2">
                                    <label class="flex items-start gap-3 text-xs text-white/70">
                                        <input
                                            v-model="form.contact_consent"
                                            type="checkbox"
                                            class="mt-0.5 h-4 w-4 rounded border-white/20 bg-white/10 text-indigo-500 focus:ring-indigo-400/30"
                                        />
                                        <span>Concordo em receber contato por e-mail/WhatsApp sobre este orçamento.</span>
                                    </label>
                                    <div v-if="form.errors.contact_consent" class="mt-2 text-xs text-rose-200">
                                        {{ form.errors.contact_consent }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="text-xs text-white/60">
                                    Resposta inicial com direcionamento técnico.
                                </div>
                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center rounded-2xl bg-indigo-500 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-400 disabled:opacity-50"
                                    :disabled="form.processing"
                                >
                                    Enviar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <section class="border-t border-white/10 py-14">
                <h2 class="text-2xl font-semibold">FAQ</h2>
                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    <details class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <summary class="cursor-pointer text-sm font-semibold">Quanto custa um sistema sob medida?</summary>
                        <div class="mt-3 text-sm text-white/70">Depende de escopo, integrações e nível de automação/IA. A gente começa por um diagnóstico para estimar com transparência.</div>
                    </details>
                    <details class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <summary class="cursor-pointer text-sm font-semibold">Qual é o prazo médio?</summary>
                        <div class="mt-3 text-sm text-white/70">Trabalhamos com entregas incrementais: você vê valor cedo e evolui por etapas, com roadmap claro.</div>
                    </details>
                    <details class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <summary class="cursor-pointer text-sm font-semibold">Vocês integram com ferramentas que já uso?</summary>
                        <div class="mt-3 text-sm text-white/70">Sim. Integramos CRMs, gateways, WhatsApp, e-mail e APIs externas, com rastreio e reprocessamento quando necessário.</div>
                    </details>
                    <details class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <summary class="cursor-pointer text-sm font-semibold">Como a IA é aplicada com segurança?</summary>
                        <div class="mt-3 text-sm text-white/70">Com minimização de dados, rate limit, auditoria e sem enviar informações sensíveis para o provedor.</div>
                    </details>
                </div>
            </section>
        </main>

        <footer class="border-t border-white/10 py-10">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-6 text-sm text-white/60 sm:flex-row sm:items-center sm:justify-between">
                <div>© {{ new Date().getFullYear() }} MedidaTeck</div>
                <div class="flex gap-4">
                    <a href="#contato" class="hover:text-white">Contato</a>
                    <a href="#diferenciais" class="hover:text-white">Diferenciais</a>
                </div>
            </div>
        </footer>

        <AiChatWidget />
    </div>
</template>

