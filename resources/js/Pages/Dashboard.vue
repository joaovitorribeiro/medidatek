<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

type Metrics = {
    projetos_total: number;
    projetos_publicados: number;
    leads_total: number;
    leads_30d: number;
    ia_requisicoes_7d: number;
    ia_custo_30d_usd: string;
};

type RecentProject = {
    id: number;
    name: string;
    url: string;
    is_published: boolean;
    updated_at: string | null;
};

type RecentLead = {
    id: number;
    name: string;
    email: string | null;
    company: string | null;
    created_at: string | null;
};

const props = defineProps<{
    isAdmin: boolean;
    metrics: Metrics;
    recent: {
        projetos: RecentProject[];
        leads: RecentLead[];
    };
}>();

const nf = new Intl.NumberFormat('pt-BR');
const currency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'USD' });

function fmtDate(iso: string | null) {
    if (!iso) return '—';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '—';
    return d.toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
}
</script>

<template>
    <Head title="Painel" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-1">
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Painel</h2>
                <div class="text-sm text-gray-600">Visão geral do sistema</div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-gray-700">Projetos cadastrados</div>
                        <div class="mt-2 text-3xl font-semibold tracking-tight text-gray-900">
                            {{ nf.format(props.metrics.projetos_total) }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            Publicados: {{ nf.format(props.metrics.projetos_publicados) }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-gray-700">Leads</div>
                        <div class="mt-2 text-3xl font-semibold tracking-tight text-gray-900">
                            {{ nf.format(props.metrics.leads_total) }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            Últimos 30 dias: {{ nf.format(props.metrics.leads_30d) }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-gray-700">IA</div>
                        <div class="mt-2 text-3xl font-semibold tracking-tight text-gray-900">
                            {{ nf.format(props.metrics.ia_requisicoes_7d) }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            Requisições (7 dias) • Custo (30 dias): {{ currency.format(Number(props.metrics.ia_custo_30d_usd || 0)) }}
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <div class="text-sm font-semibold text-gray-900">Projetos recentes</div>
                            <a
                                v-if="props.isAdmin"
                                :href="route('admin.projects.index')"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-800"
                            >
                                Ver todos
                            </a>
                        </div>
                        <div class="mt-4 divide-y divide-gray-100">
                            <div v-for="p in props.recent.projetos" :key="p.id" class="flex items-start justify-between gap-4 py-3">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-gray-900">
                                        {{ p.name }}
                                    </div>
                                    <div class="truncate text-xs text-gray-500">
                                        {{ p.url }}
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <div class="text-xs font-semibold" :class="p.is_published ? 'text-emerald-600' : 'text-gray-500'">
                                        {{ p.is_published ? 'Publicado' : 'Rascunho' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ fmtDate(p.updated_at) }}
                                    </div>
                                </div>
                            </div>
                            <div v-if="props.recent.projetos.length === 0" class="py-6 text-sm text-gray-500">
                                Nenhum projeto cadastrado ainda.
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-semibold text-gray-900">Leads recentes</div>
                        <div class="mt-4 divide-y divide-gray-100">
                            <div v-for="l in props.recent.leads" :key="l.id" class="flex items-start justify-between gap-4 py-3">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-gray-900">
                                        {{ l.name }}
                                    </div>
                                    <div class="truncate text-xs text-gray-500">
                                        {{ l.company || l.email || '—' }}
                                    </div>
                                </div>
                                <div class="shrink-0 text-right text-xs text-gray-500">
                                    {{ fmtDate(l.created_at) }}
                                </div>
                            </div>
                            <div v-if="props.recent.leads.length === 0" class="py-6 text-sm text-gray-500">
                                Nenhum lead capturado ainda.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
