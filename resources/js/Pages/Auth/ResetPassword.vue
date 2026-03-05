<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Redefinir senha" />

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

            <div class="mt-4">
                <InputLabel for="password" value="Senha" class="text-white/70" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-2 block w-full rounded-xl border-white/10 bg-white/5 text-white shadow-none placeholder:text-white/30 focus:border-indigo-400 focus:ring-indigo-400/20"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2 text-rose-300" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirmar senha"
                    class="text-white/70"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-2 block w-full rounded-xl border-white/10 bg-white/5 text-white shadow-none placeholder:text-white/30 focus:border-indigo-400 focus:ring-indigo-400/20"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2 text-rose-300"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton
                    class="!rounded-full !bg-white !px-7 !py-3 !text-sm !font-semibold !normal-case !tracking-normal !text-black shadow-[0_0_20px_rgba(255,255,255,0.15)] hover:!bg-zinc-200 focus:!bg-zinc-200 focus:!ring-indigo-500/30 focus:!ring-offset-0 active:!bg-white"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Redefinir senha
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
