<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type LeadRow = {
    id: number;
    name: string;
    email: string | null;
    whatsapp: string | null;
    company: string | null;
    pain: string | null;
    investment_range: string | null;
    message: string | null;
    utm_source: string | null;
    utm_medium: string | null;
    utm_campaign: string | null;
    utm_content: string | null;
    utm_term: string | null;
    landing_path: string | null;
    referrer: string | null;
    contact_consent: boolean;
    ip_address: string | null;
    user_agent: string | null;
    created_at: string;
};

type SimplePagination<T> = {
    data: T[];
    prev_page_url: string | null;
    next_page_url: string | null;
    from: number | null;
    to: number | null;
};

const props = defineProps<{
    leads: SimplePagination<LeadRow>;
}>();

const selected = ref<LeadRow | null>(null);
const isOpen = computed(() => selected.value !== null);

const dt = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
function formatDate(value: string) {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return dt.format(date);
}

function openLead(row: LeadRow) {
    selected.value = row;
}
function close() {
    selected.value = null;
}

function processSteps(row: LeadRow) {
    const s1 = !!row.pain;
    const s2 = !!row.name && !!row.email && !!row.whatsapp && !!row.contact_consent;
    const s3 = !!row.company || !!row.message;
    const s4 = !!row.investment_range;
    return [s1, s2, s3, s4];
}
</script>

<template>
    <Head title="Admin — Leads" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-slate-900">
                        Leads
                    </h2>
                    <div class="mt-1 text-sm text-slate-600">
                        Capturas do formulário “Vamos construir?” da landing page
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="rounded-2xl border border-white/60 bg-white/70 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">
                        <span v-if="leads.from !== null && leads.to !== null">
                            {{ leads.from }}–{{ leads.to }}
                        </span>
                        <span v-else>
                            —
                        </span>
                    </div>
                    <Link
                        :href="leads.prev_page_url ?? ''"
                        class="rounded-2xl border border-white/60 bg-white/70 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                        :class="leads.prev_page_url ? '' : 'pointer-events-none opacity-50'"
                        preserve-scroll
                    >
                        Anterior
                    </Link>
                    <Link
                        :href="leads.next_page_url ?? ''"
                        class="rounded-2xl border border-white/60 bg-white/70 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                        :class="leads.next_page_url ? '' : 'pointer-events-none opacity-50'"
                        preserve-scroll
                    >
                        Próximo
                    </Link>
                </div>
            </div>
        </template>

        <div class="px-4 py-8 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden rounded-3xl border border-white/60 bg-white/70 shadow-sm backdrop-blur">
                    <div class="border-b border-white/60 px-6 py-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-slate-900">
                                Lista de leads
                            </div>
                            <div class="text-xs text-slate-500">
                                Clique em “Detalhes” para ver tudo que foi preenchido
                            </div>
                        </div>
                    </div>

                    <div class="divide-y divide-white/60">
                        <div
                            v-for="row in leads.data"
                            :key="row.id"
                            class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <div class="truncate text-base font-semibold text-slate-900">
                                        {{ row.name }}
                                    </div>
                                    <div class="text-xs font-semibold text-slate-500">
                                        #{{ row.id }}
                                    </div>
                                    <div class="inline-flex items-center gap-1">
                                        <span
                                            v-for="(ok, idx) in processSteps(row)"
                                            :key="idx"
                                            class="h-2 w-2 rounded-full"
                                            :class="ok ? 'bg-emerald-500' : 'bg-slate-300'"
                                        ></span>
                                    </div>
                                </div>

                                <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-600">
                                    <div class="truncate">
                                        {{ row.email ?? '—' }}
                                    </div>
                                    <div class="truncate">
                                        {{ row.whatsapp ?? '—' }}
                                    </div>
                                    <div class="truncate text-xs text-slate-500">
                                        {{ formatDate(row.created_at) }}
                                    </div>
                                </div>

                                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                    <span class="rounded-full bg-indigo-50 px-3 py-1 font-semibold text-indigo-700" v-if="row.investment_range">
                                        {{ row.investment_range }}
                                    </span>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-700" v-if="row.pain">
                                        {{ row.pain }}
                                    </span>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-700" v-if="row.company">
                                        {{ row.company }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                <button
                                    type="button"
                                    class="rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-indigo-500/20 transition hover:from-indigo-500 hover:to-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                    @click="openLead(row)"
                                >
                                    Detalhes
                                </button>
                            </div>
                        </div>

                        <div v-if="leads.data.length === 0" class="px-6 py-10 text-sm text-slate-600">
                            Nenhum lead capturado ainda.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-show="isOpen"
                class="fixed inset-0 z-50 bg-slate-900/30 backdrop-blur-sm"
                @click="close"
                style="display: none"
            ></div>
        </Transition>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="translate-y-3 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-3 opacity-0"
        >
            <div
                v-show="isOpen"
                class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full max-w-4xl p-4 sm:bottom-auto sm:top-16"
                style="display: none"
            >
                <div class="max-h-[85vh] overflow-hidden rounded-3xl border border-white/60 bg-white/85 shadow-xl backdrop-blur">
                    <div class="flex items-start justify-between gap-4 border-b border-white/60 px-6 py-5">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <div class="truncate text-lg font-semibold text-slate-900">
                                    {{ selected?.name ?? '' }}
                                </div>
                                <div class="text-xs font-semibold text-slate-500">
                                    #{{ selected?.id ?? '' }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ selected ? formatDate(selected.created_at) : '' }}
                                </div>
                            </div>
                            <div class="mt-1 text-sm text-slate-600">
                                Processo do “Vamos construir?” (4 etapas)
                            </div>
                            <div class="mt-3 flex items-center gap-2">
                                <div
                                    v-for="(ok, idx) in (selected ? processSteps(selected) : [])"
                                    :key="idx"
                                    class="flex items-center gap-2 rounded-2xl border border-white/60 bg-white/70 px-3 py-2 text-xs font-semibold"
                                    :class="ok ? 'text-emerald-700' : 'text-slate-500'"
                                >
                                    <span class="h-2 w-2 rounded-full" :class="ok ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                                    Etapa {{ idx + 1 }}
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl text-slate-600 transition hover:bg-white/70 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            @click="close"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                    </div>

                    <div class="max-h-[calc(85vh-5rem)] overflow-y-auto px-6 py-5">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="rounded-2xl border border-white/60 bg-white/70 p-4">
                                <div class="text-xs font-semibold text-slate-500">Contato</div>
                                <div class="mt-3 space-y-2 text-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="text-slate-500">Email</div>
                                        <div class="truncate font-semibold text-slate-900">{{ selected?.email ?? '—' }}</div>
                                    </div>
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="text-slate-500">WhatsApp</div>
                                        <div class="truncate font-semibold text-slate-900">{{ selected?.whatsapp ?? '—' }}</div>
                                    </div>
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="text-slate-500">Consentimento</div>
                                        <div class="font-semibold" :class="selected?.contact_consent ? 'text-emerald-700' : 'text-rose-700'">
                                            {{ selected?.contact_consent ? 'Aceito' : 'Não' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-white/60 bg-white/70 p-4">
                                <div class="text-xs font-semibold text-slate-500">Negócio</div>
                                <div class="mt-3 space-y-2 text-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="text-slate-500">Empresa</div>
                                        <div class="truncate font-semibold text-slate-900">{{ selected?.company ?? '—' }}</div>
                                    </div>
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="text-slate-500">Prioridade</div>
                                        <div class="truncate font-semibold text-slate-900">{{ selected?.pain ?? '—' }}</div>
                                    </div>
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="text-slate-500">Investimento</div>
                                        <div class="truncate font-semibold text-slate-900">{{ selected?.investment_range ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-white/60 bg-white/70 p-4 sm:col-span-2">
                                <div class="text-xs font-semibold text-slate-500">Mensagem</div>
                                <div class="mt-3 whitespace-pre-wrap text-sm font-medium text-slate-900">
                                    {{ selected?.message ?? '—' }}
                                </div>
                            </div>

                            <div class="rounded-2xl border border-white/60 bg-white/70 p-4 sm:col-span-2">
                                <div class="text-xs font-semibold text-slate-500">Tracking</div>
                                <div class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                                    <div class="flex items-start justify-between gap-3 rounded-xl bg-white/60 px-3 py-2">
                                        <div class="text-slate-500">UTM Source</div>
                                        <div class="truncate font-semibold text-slate-900">{{ selected?.utm_source ?? '—' }}</div>
                                    </div>
                                    <div class="flex items-start justify-between gap-3 rounded-xl bg-white/60 px-3 py-2">
                                        <div class="text-slate-500">UTM Medium</div>
                                        <div class="truncate font-semibold text-slate-900">{{ selected?.utm_medium ?? '—' }}</div>
                                    </div>
                                    <div class="flex items-start justify-between gap-3 rounded-xl bg-white/60 px-3 py-2">
                                        <div class="text-slate-500">UTM Campaign</div>
                                        <div class="truncate font-semibold text-slate-900">{{ selected?.utm_campaign ?? '—' }}</div>
                                    </div>
                                    <div class="flex items-start justify-between gap-3 rounded-xl bg-white/60 px-3 py-2">
                                        <div class="text-slate-500">UTM Content</div>
                                        <div class="truncate font-semibold text-slate-900">{{ selected?.utm_content ?? '—' }}</div>
                                    </div>
                                    <div class="flex items-start justify-between gap-3 rounded-xl bg-white/60 px-3 py-2">
                                        <div class="text-slate-500">UTM Term</div>
                                        <div class="truncate font-semibold text-slate-900">{{ selected?.utm_term ?? '—' }}</div>
                                    </div>
                                    <div class="flex items-start justify-between gap-3 rounded-xl bg-white/60 px-3 py-2">
                                        <div class="text-slate-500">Landing</div>
                                        <div class="truncate font-semibold text-slate-900">{{ selected?.landing_path ?? '—' }}</div>
                                    </div>
                                    <div class="flex items-start justify-between gap-3 rounded-xl bg-white/60 px-3 py-2 sm:col-span-2">
                                        <div class="text-slate-500">Referrer</div>
                                        <div class="truncate font-semibold text-slate-900">{{ selected?.referrer ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-white/60 bg-white/70 p-4 sm:col-span-2">
                                <div class="text-xs font-semibold text-slate-500">Técnico</div>
                                <div class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                                    <div class="flex items-start justify-between gap-3 rounded-xl bg-white/60 px-3 py-2">
                                        <div class="text-slate-500">IP</div>
                                        <div class="truncate font-semibold text-slate-900">{{ selected?.ip_address ?? '—' }}</div>
                                    </div>
                                    <div class="flex items-start justify-between gap-3 rounded-xl bg-white/60 px-3 py-2 sm:col-span-2">
                                        <div class="text-slate-500">User Agent</div>
                                        <div class="truncate font-semibold text-slate-900">{{ selected?.user_agent ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>

