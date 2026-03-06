<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';

type ProjectForm = {
    id?: number;
    name: string;
    url: string;
    image: File | null;
    image_alt: string;
    tag: string;
    note: string;
    sort_order: number;
    is_published: boolean;
    remove_image: boolean;
};

const props = defineProps<{
    project: null | {
        id: number;
        name: string;
        url: string;
        image_url: string | null;
        image_src: string | null;
        image_alt: string | null;
        tag: string | null;
        note: string | null;
        sort_order: number;
        is_published: boolean;
    };
    default_sort_order?: number;
}>();

const form = useForm<ProjectForm>({
    name: props.project?.name ?? '',
    url: props.project?.url ?? '',
    image: null,
    image_alt: props.project?.image_alt ?? '',
    tag: props.project?.tag ?? '',
    note: props.project?.note ?? '',
    sort_order: props.project?.sort_order ?? props.default_sort_order ?? 0,
    is_published: props.project?.is_published ?? true,
    remove_image: false,
});

const previewUrl = ref<string | null>(null);

const previewSrc = computed(() => {
    if (form.remove_image) {
        return '';
    }
    return previewUrl.value ?? props.project?.image_src ?? '';
});

function pickImage(event: Event) {
    const input = event.target as HTMLInputElement | null;
    const file = input?.files?.[0] ?? null;

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }

    form.image = file;
    if (file) {
        previewUrl.value = URL.createObjectURL(file);
        form.remove_image = false;
    }
}

function removeImage() {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
    form.image = null;
    form.remove_image = true;
}

function submit() {
    if (props.project) {
        const needsMultipart = !!form.image || form.remove_image;
        if (needsMultipart) {
            form.transform((data) => ({ ...data, _method: 'put' }));
            form.post(route('admin.projects.update', props.project.id), {
                forceFormData: true,
                onFinish: () => {
                    form.transform((d) => d);
                },
            });
            return;
        }

        form.put(route('admin.projects.update', props.project.id));
        return;
    }

    form.post(route('admin.projects.store'), { forceFormData: true });
}

onBeforeUnmount(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
});
</script>

<template>
    <Head :title="project ? 'Admin — Editar projeto' : 'Admin — Novo projeto'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-slate-900">
                    {{ project ? 'Editar projeto' : 'Novo projeto' }}
                </h2>
                <Link
                    :href="route('admin.projects.index')"
                    class="text-sm font-semibold text-gray-200 hover:text-white"
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

                            <div>
                                <div class="flex items-center justify-between gap-3">
                                    <label class="text-sm font-semibold text-gray-700">Imagem do card (opcional)</label>
                                    <button
                                        v-if="previewSrc"
                                        type="button"
                                        class="text-xs font-semibold text-rose-600 hover:text-rose-700"
                                        :disabled="form.processing"
                                        @click="removeImage"
                                    >
                                        Remover imagem
                                    </button>
                                </div>

                                <div class="mt-2 grid gap-4 md:grid-cols-2">
                                    <div>
                                        <input
                                            type="file"
                                            accept="image/png,image/jpeg,image/webp"
                                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                            :disabled="form.processing"
                                            @change="pickImage($event)"
                                        />
                                        <div class="mt-2 text-xs text-gray-500">
                                            PNG/JPG/WebP (até 5MB). A imagem é recortada para 4:3.
                                        </div>
                                        <div v-if="form.errors.image" class="mt-2 text-xs text-rose-600">
                                            {{ form.errors.image }}
                                        </div>
                                    </div>

                                    <div>
                                        <input
                                            v-model="form.image_alt"
                                            type="text"
                                            class="w-full rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                            :disabled="form.processing"
                                            placeholder="Alt (acessibilidade/SEO)"
                                        />
                                        <div v-if="form.errors.image_alt" class="mt-2 text-xs text-rose-600">
                                            {{ form.errors.image_alt }}
                                        </div>
                                    </div>
                                </div>

                                <div v-if="previewSrc" class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                    <img
                                        :src="previewSrc"
                                        :alt="form.image_alt || form.name"
                                        class="h-48 w-full object-cover"
                                        loading="lazy"
                                    />
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
