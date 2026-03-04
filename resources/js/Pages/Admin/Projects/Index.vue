<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

type ProjectRow = {
    id: number;
    name: string;
    url: string;
    tag: string | null;
    note: string | null;
    sort_order: number;
    is_published: boolean;
    created_at: string;
};

defineProps<{
    projects: ProjectRow[];
}>();

const deleteForm = useForm({});

function destroy(id: number) {
    deleteForm.delete(route('admin.projects.destroy', id));
}
</script>

<template>
    <Head title="Admin — Projetos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Projetos
                </h2>
                <Link
                    :href="route('admin.projects.create')"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                >
                    Novo projeto
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="overflow-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="text-xs uppercase text-gray-500">
                                    <tr>
                                        <th class="px-3 py-2">Ordem</th>
                                        <th class="px-3 py-2">Projeto</th>
                                        <th class="px-3 py-2">Tag</th>
                                        <th class="px-3 py-2">Status</th>
                                        <th class="px-3 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="row in projects" :key="row.id">
                                        <td class="px-3 py-2 text-gray-700">
                                            {{ row.sort_order }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="font-semibold text-gray-900">
                                                {{ row.name }}
                                            </div>
                                            <a
                                                :href="row.url"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="mt-1 inline-flex text-xs text-indigo-600 hover:text-indigo-800"
                                            >
                                                {{ row.url }}
                                            </a>
                                            <div v-if="row.note" class="mt-2 text-xs text-gray-600">
                                                {{ row.note }}
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-gray-700">
                                            {{ row.tag ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <span
                                                class="rounded-full px-2 py-1 text-xs font-semibold"
                                                :class="row.is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-700'"
                                            >
                                                {{ row.is_published ? 'Publicado' : 'Oculto' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <div class="flex justify-end gap-3">
                                                <Link
                                                    :href="route('admin.projects.edit', row.id)"
                                                    class="text-sm font-semibold text-gray-700 hover:text-gray-900"
                                                >
                                                    Editar
                                                </Link>
                                                <button
                                                    type="button"
                                                    class="text-sm font-semibold text-rose-600 hover:text-rose-700"
                                                    :disabled="deleteForm.processing"
                                                    @click="destroy(row.id)"
                                                >
                                                    Excluir
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="projects.length === 0" class="text-sm text-gray-600">
                            Nenhum projeto cadastrado ainda.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
