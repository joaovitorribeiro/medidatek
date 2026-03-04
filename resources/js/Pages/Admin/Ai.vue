<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

type LogRow = {
    id: number;
    created_at: string;
    endpoint: string;
    status: string;
    model: string | null;
    total_tokens: number | null;
    latency_ms: number | null;
};

defineProps<{
    lastLogs: LogRow[];
    byStatus: Array<{ status: string; count: number }>;
    byEndpoint: Array<{ endpoint: string; count: number }>;
}>();
</script>

<template>
    <Head title="Admin — IA" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                IA — Uso e auditoria
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="text-sm font-semibold">Por status</div>
                            <div class="mt-4 space-y-2 text-sm">
                                <div
                                    v-for="row in byStatus"
                                    :key="row.status"
                                    class="flex items-center justify-between"
                                >
                                    <div class="text-gray-700">{{ row.status }}</div>
                                    <div class="font-semibold">{{ row.count }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg lg:col-span-2">
                        <div class="p-6 text-gray-900">
                            <div class="text-sm font-semibold">Por endpoint</div>
                            <div class="mt-4 space-y-2 text-sm">
                                <div
                                    v-for="row in byEndpoint"
                                    :key="row.endpoint"
                                    class="flex items-center justify-between"
                                >
                                    <div class="text-gray-700">{{ row.endpoint }}</div>
                                    <div class="font-semibold">{{ row.count }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="text-sm font-semibold">Últimos logs</div>
                        <div class="mt-4 overflow-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="text-xs uppercase text-gray-500">
                                    <tr>
                                        <th class="px-3 py-2">Data</th>
                                        <th class="px-3 py-2">Endpoint</th>
                                        <th class="px-3 py-2">Status</th>
                                        <th class="px-3 py-2">Modelo</th>
                                        <th class="px-3 py-2">Tokens</th>
                                        <th class="px-3 py-2">Latência</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="row in lastLogs" :key="row.id">
                                        <td class="px-3 py-2 text-gray-700">
                                            {{ row.created_at }}
                                        </td>
                                        <td class="px-3 py-2 text-gray-700">
                                            {{ row.endpoint }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <span
                                                class="rounded-full px-2 py-1 text-xs"
                                                :class="
                                                    row.status === 'ok'
                                                        ? 'bg-emerald-50 text-emerald-700'
                                                        : row.status === 'stub'
                                                          ? 'bg-amber-50 text-amber-700'
                                                          : 'bg-rose-50 text-rose-700'
                                                "
                                            >
                                                {{ row.status }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-gray-700">
                                            {{ row.model ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-gray-700">
                                            {{ row.total_tokens ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-gray-700">
                                            {{ row.latency_ms != null ? `${row.latency_ms}ms` : '-' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

