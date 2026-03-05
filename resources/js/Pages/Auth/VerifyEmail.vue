<script setup lang="ts">
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verificação de email">
            <meta name="robots" content="noindex,nofollow" />
        </Head>

        <div class="mb-4 text-sm text-white/60">
            Obrigado por se cadastrar! Antes de começar, verifique seu email clicando no link que acabamos de enviar.
            Se você não recebeu o email, podemos reenviar.
        </div>

        <div
            class="mb-4 text-sm font-medium text-emerald-300"
            v-if="verificationLinkSent"
        >
            Um novo link de verificação foi enviado para o email informado no cadastro.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton
                    class="!rounded-full !bg-white !px-7 !py-3 !text-sm !font-semibold !normal-case !tracking-normal !text-black shadow-[0_0_20px_rgba(255,255,255,0.15)] hover:!bg-zinc-200 focus:!bg-zinc-200 focus:!ring-indigo-500/30 focus:!ring-offset-0 active:!bg-white"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Reenviar email de verificação
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded-md text-sm text-white/60 underline decoration-white/30 underline-offset-4 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                    >Sair</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>
