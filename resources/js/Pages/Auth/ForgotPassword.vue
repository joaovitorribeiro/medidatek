<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Recuperar senha">
            <meta name="robots" content="noindex,nofollow" />
        </Head>

        <div class="mb-4 text-sm text-white/60">
            Esqueceu sua senha? Sem problema. Informe seu email e enviaremos um link para redefinição de senha.
        </div>

        <div
            v-if="status"
            class="mb-4 text-sm font-medium text-emerald-300"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" class="text-white/70" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full rounded-xl border-white/10 bg-white/5 text-white shadow-none placeholder:text-white/30 focus:border-indigo-400 focus:ring-indigo-400/20"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2 text-rose-300" :message="form.errors.email" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton
                    class="!rounded-full !bg-white !px-7 !py-3 !text-sm !font-semibold !normal-case !tracking-normal !text-black shadow-[0_0_20px_rgba(255,255,255,0.15)] hover:!bg-zinc-200 focus:!bg-zinc-200 focus:!ring-indigo-500/30 focus:!ring-offset-0 active:!bg-white"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Enviar link de redefinição
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
