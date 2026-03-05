<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Admin — Entrar">
            <meta name="robots" content="noindex,nofollow" />
        </Head>

        <div v-if="status" class="mb-4 text-sm font-medium text-emerald-300">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="mb-8">
                <div class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/50">
                    Área administrativa
                </div>
                <div class="mt-2 text-2xl font-semibold tracking-tight text-white">
                    Entrar
                </div>
                <div class="mt-2 text-sm text-white/60">
                    Use o e-mail e senha do administrador.
                </div>
            </div>

            <div>
                <InputLabel for="email" value="E-mail" class="text-white/70" />

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

            <div class="mt-4">
                <InputLabel for="password" value="Senha" class="text-white/70" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-2 block w-full rounded-xl border-white/10 bg-white/5 text-white shadow-none placeholder:text-white/30 focus:border-indigo-400 focus:ring-indigo-400/20"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2 text-rose-300" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-white/60">Manter conectado</span>
                </label>
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-white/60 underline decoration-white/30 underline-offset-4 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                >
                    Esqueci minha senha
                </Link>

                <PrimaryButton
                    class="ms-4 !rounded-full !bg-white !px-7 !py-3 !text-sm !font-semibold !normal-case !tracking-normal !text-black shadow-[0_0_20px_rgba(255,255,255,0.15)] hover:!bg-zinc-200 focus:!bg-zinc-200 focus:!ring-indigo-500/30 focus:!ring-offset-0 active:!bg-white"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Entrar
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
