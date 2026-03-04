<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

type ProjectForm = {
    id?: number;
    name: string;
    url: string;
    tag: string;
    note: string;
    sort_order: number;
    is_published: boolean;
};

const props = defineProps<{
    project: null | {
        id: number;
        name: string;
        url: string;
        tag: string | null;
        note: string | null;
        sort_order: number;
        is_published: boolean;
    };
}>();

const form = useForm<ProjectForm>({
    name: props.project?.name ?? '',
    url: props.project?.url ?? '',
    tag: props.project?.tag ?? '',
    note: props.project?.note ?? '',
    sort_order: props.project?.sort_order ?? 0,
    is_published: props.project?.is_published ?? true,
});

function submit() {
    if (props.project) {
        form.put(route('admin.projects.update', props.project.id));
        return;
    }

    form.post(route('admin.projects.store'));
}
</script>

<template>
    <Head :title="project ? 'Admin — Editar projeto' : 'Admin — Novo projeto'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ project ? 'Editar projeto' : 'Novo projeto' }}
                </h2>
                <Link
                    :href="route('admin.projects.index')"
                    class="text-sm font-semibold text-gray-700 hover:text-gray-900"
                >
                    Voltar
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form class="grid gap-4" @submit.prevent="submit">
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Nome</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                />
                                <div v-if="form.errors.name" class="mt-2 text-xs text-rose-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-gray-700">URL</label>
                                <input
                                    v-model="form.url"
                                    type="url"
                                    class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                    placeholder="https://..."
                                />
                                <div v-if="form.errors.url" class="mt-2 text-xs text-rose-600">
                                    {{ form.errors.url }}
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm font-semibold text-gray-700">Tag (opcional)</label>
                                    <input
                                        v-model="form.tag"
                                        type="text"
                                        class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                        placeholder="Financeiro, Indústria..."
                                    />
                                    <div v-if="form.errors.tag" class="mt-2 text-xs text-rose-600">
                                        {{ form.errors.tag }}
                                    </div>
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-gray-700">Ordem</label>
                                    <input
                                        v-model.number="form.sort_order"
                                        type="number"
                                        min="0"
                                        class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                    />
                                    <div v-if="form.errors.sort_order" class="mt-2 text-xs text-rose-600">
                                        {{ form.errors.sort_order }}
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-gray-700">Nota (opcional)</label>
                                <textarea
                                    v-model="form.note"
                                    rows="3"
                                    class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                    placeholder="Uma linha de contexto do projeto, sem detalhes sensíveis."
                                ></textarea>
                                <div v-if="form.errors.note" class="mt-2 text-xs text-rose-600">
                                    {{ form.errors.note }}
                                </div>
                            </div>

                            <label class="flex items-center gap-3 text-sm text-gray-700">
                                <input v-model="form.is_published" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600" />
                                <span>Exibir na landing</span>
                            </label>

                            <div class="mt-2 flex items-center justify-end gap-3">
                                <Link
                                    :href="route('admin.projects.index')"
                                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200"
                                >
                                    Cancelar
                                </Link>
                                <button
                                    type="submit"
                                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
                                    :disabled="form.processing"
                                >
                                    Salvar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
