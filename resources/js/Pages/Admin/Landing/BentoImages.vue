<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref } from 'vue';

type BentoCard = {
    id: number;
    key: string;
    title: string;
    image_url: string;
    image_src: string;
    alt: string;
    sort_order: number;
};

const props = defineProps<{
    cards: BentoCard[];
}>();

const alts = ref<string[]>(props.cards.map((c) => c.alt));
const uploadingById = ref<Record<number, boolean>>({});
const errorsById = ref<Record<number, { alt?: string; image?: string }>>({});
const savedById = ref<Record<number, boolean>>({});

const previewUrls = ref<Record<number, string>>({});

function previewSrc(index: number) {
    return previewUrls.value[index] ?? props.cards[index]?.image_src ?? '';
}

function pickImage(index: number, event: Event) {
    const input = event.target as HTMLInputElement | null;
    const file = input?.files?.[0] ?? null;

    const existing = previewUrls.value[index];
    if (existing) {
        URL.revokeObjectURL(existing);
        delete previewUrls.value[index];
    }

    if (file) {
        previewUrls.value[index] = URL.createObjectURL(file);
    }

    if (file) {
        save(index, file);
    }
}

function save(index: number, file: File | null) {
    const card = props.cards[index];
    const rawAlt = alts.value[index] ?? card.alt ?? '';
    const alt = rawAlt.trim() !== '' ? rawAlt : card.title;

    uploadingById.value[card.id] = true;
    savedById.value[card.id] = false;
    delete errorsById.value[card.id];

    router.post(
        route('admin.landing.bento.card.save', card.id),
        {
            alt,
            image: file,
        },
        {
            preserveScroll: true,
            forceFormData: true,
            onError: (errors) => {
                const altError = typeof errors.alt === 'string' ? errors.alt : undefined;
                const imageError = typeof errors.image === 'string' ? errors.image : undefined;
                errorsById.value[card.id] = { alt: altError, image: imageError };
            },
            onSuccess: () => {
                savedById.value[card.id] = true;
            },
            onFinish: () => {
                uploadingById.value[card.id] = false;
            },
        },
    );
}

onBeforeUnmount(() => {
    for (const url of Object.values(previewUrls.value)) {
        URL.revokeObjectURL(url);
    }
});
</script>

<template>
    <Head title="Admin — Site — Imagens" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Site — Imagens
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="grid gap-6">
                            <div v-for="(card, index) in props.cards" :key="card.id" class="rounded-xl border border-gray-200 p-4">
                                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ card.title }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ card.key }}
                                            </div>
                                            <div v-if="uploadingById[card.id]" class="text-xs font-semibold text-indigo-600">
                                                Salvando...
                                            </div>
                                            <div v-else-if="savedById[card.id]" class="text-xs font-semibold text-emerald-600">
                                                Salvo
                                            </div>
                                        </div>

                                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                                            <div>
                                                <label class="text-sm font-semibold text-gray-700">Upload da imagem</label>
                                                <input
                                                    type="file"
                                                    accept="image/png,image/jpeg,image/webp"
                                                    @change="pickImage(index, $event)"
                                                    :disabled="uploadingById[card.id]"
                                                    class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                                />
                                                <div class="mt-2 text-xs text-gray-500">
                                                    PNG/JPG/WebP (até 5MB).
                                                </div>
                                                <div v-if="errorsById[card.id]?.image" class="mt-2 text-xs font-semibold text-red-600">
                                                    {{ errorsById[card.id]?.image }}
                                                </div>
                                            </div>

                                            <div>
                                                <label class="text-sm font-semibold text-gray-700">Alt (acessibilidade/SEO)</label>
                                                <input
                                                    v-model="alts[index]"
                                                    type="text"
                                                    :disabled="uploadingById[card.id]"
                                                    @blur="save(index, null)"
                                                    class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                                    placeholder="Descrição curta da imagem"
                                                />
                                                <div v-if="errorsById[card.id]?.alt" class="mt-2 text-xs font-semibold text-red-600">
                                                    {{ errorsById[card.id]?.alt }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="w-full md:w-56">
                                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                            <img
                                                :src="previewSrc(index)"
                                                :alt="alts[index]"
                                                class="h-36 w-full object-cover"
                                                loading="lazy"
                                            />
                                        </div>
                                        <a
                                            :href="previewSrc(index)"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="mt-2 inline-flex text-xs font-semibold text-indigo-600 hover:text-indigo-800"
                                        >
                                            Abrir imagem
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
