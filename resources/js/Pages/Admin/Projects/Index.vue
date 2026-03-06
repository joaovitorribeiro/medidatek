<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type ProjectRow = {
    id: number;
    name: string;
    url: string;
    image_src: string | null;
    tag: string | null;
    note: string | null;
    sort_order: number;
    is_published: boolean;
    created_at: string;
};

type SimplePagination<T> = {
    data: T[];
    prev_page_url: string | null;
    next_page_url: string | null;
    from: number | null;
    to: number | null;
};

defineProps<{
    projects: SimplePagination<ProjectRow>;
}>();

const deleteForm = useForm({});
const page = usePage();
const canManageProjects = computed(() => {
    const user = (page.props as any)?.auth?.user;
    return Boolean(user?.is_admin);
});

function destroy(id: number) {
    deleteForm.delete(route('admin.projects.destroy', id));
}
</script>

<template>
    <Head title="Admin — Projetos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-white">
                    Projetos
                </h2>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="canManageProjects"
                        :href="route('admin.projects.create')"
                        class="rounded-lg bg-[#6C5DD3] px-4 py-2 text-sm font-semibold text-white hover:bg-[#5A4BC6]"
                    >
                        Novo projeto
                    </Link>
                    <Link
                        :href="projects.prev_page_url ?? ''"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                        :class="projects.prev_page_url ? '' : 'pointer-events-none opacity-50'"
                        preserve-scroll
                    >
                        Anterior
                    </Link>
                    <Link
                        :href="projects.next_page_url ?? ''"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                        :class="projects.next_page_url ? '' : 'pointer-events-none opacity-50'"
                        preserve-scroll
                    >
                        Próximo
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-4 flex justify-end text-sm text-gray-600">
                            <span v-if="projects.from !== null && projects.to !== null">
                                {{ projects.from }}–{{ projects.to }}
                            </span>
                        </div>
                        <div class="overflow-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="text-xs uppercase text-gray-500">
                                    <tr>
                                        <th class="px-3 py-2">Ordem</th>
                                        <th class="px-3 py-2">Imagem</th>
                                        <th class="px-3 py-2">Projeto</th>
                                        <th class="px-3 py-2">Tag</th>
                                        <th class="px-3 py-2">Status</th>
                                        <th class="px-3 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="row in projects.data" :key="row.id">
                                        <td class="px-3 py-2 text-gray-700">
                                            {{ row.sort_order }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="h-12 w-20 overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                                                <img
                                                    v-if="row.image_src"
                                                    :src="row.image_src"
                                                    :alt="row.name"
                                                    class="h-12 w-20 object-cover"
                                                    loading="lazy"
                                                />
                                            </div>
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
                                            <div v-if="canManageProjects" class="flex justify-end gap-3">
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

                        <div v-if="projects.data.length === 0" class="text-sm text-gray-600">
                            Nenhum projeto cadastrado ainda.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
