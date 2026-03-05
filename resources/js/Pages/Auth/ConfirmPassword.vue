<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirmar senha" />

        <div class="mb-4 text-sm text-white/60">
            Esta é uma área segura do sistema. Confirme sua senha para continuar.
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Senha" class="text-white/70" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-2 block w-full rounded-xl border-white/10 bg-white/5 text-white shadow-none placeholder:text-white/30 focus:border-indigo-400 focus:ring-indigo-400/20"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-2 text-rose-300" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex justify-end">
                <PrimaryButton
                    class="ms-4 !rounded-full !bg-white !px-7 !py-3 !text-sm !font-semibold !normal-case !tracking-normal !text-black shadow-[0_0_20px_rgba(255,255,255,0.15)] hover:!bg-zinc-200 focus:!bg-zinc-200 focus:!ring-indigo-500/30 focus:!ring-offset-0 active:!bg-white"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Confirmar
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
