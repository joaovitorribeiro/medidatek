<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type UserRow = {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    is_staff: boolean;
    created_at: string;
};

type SimplePagination<T> = {
    data: T[];
    prev_page_url: string | null;
    next_page_url: string | null;
    from: number | null;
    to: number | null;
};

const props = defineProps<{
    users: SimplePagination<UserRow>;
}>();

const isCreateOpen = ref(false);
const isPasswordOpen = ref(false);
const selected = ref<UserRow | null>(null);

const createForm = useForm({
    role: 'socio',
    email: '',
    password: '',
});

const passwordForm = useForm({
    password: '',
});

const adminForm = useForm({
    is_admin: false,
});

const page = usePage();
const isAdmin = computed(() => Boolean((page.props as any)?.auth?.user?.is_admin));

function openCreate() {
    createForm.reset();
    createForm.role = 'socio';
    createForm.clearErrors();
    isCreateOpen.value = true;
}

function closeCreate() {
    isCreateOpen.value = false;
}

function submitCreate() {
    createForm.post(route('admin.team.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isCreateOpen.value = false;
            createForm.reset();
        },
    });
}

function openPassword(row: UserRow) {
    selected.value = row;
    passwordForm.reset();
    passwordForm.clearErrors();
    isPasswordOpen.value = true;
}

function closePassword() {
    isPasswordOpen.value = false;
    selected.value = null;
}

function submitPassword() {
    if (!selected.value) return;
    passwordForm.put(route('admin.team.password.update', selected.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isPasswordOpen.value = false;
            selected.value = null;
            passwordForm.reset();
        },
    });
}

function toggleAdmin(row: UserRow) {
    adminForm.clearErrors();
    adminForm.is_admin = !row.is_admin;
    adminForm.patch(route('admin.team.admin.update', row.id), { preserveScroll: true });
}

const dt = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
function formatDate(value: string) {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return dt.format(date);
}
</script>

<template>
    <Head title="Admin — Equipe" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-slate-900">
                        Equipe
                    </h2>
                    <div class="mt-1 text-sm text-slate-600">
                        Gerencie usuários internos e acessos
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="rounded-2xl border border-white/60 bg-white/70 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">
                        <span v-if="users.from !== null && users.to !== null">
                            {{ users.from }}–{{ users.to }}
                        </span>
                        <span v-else>—</span>
                    </div>
                    <Link
                        :href="users.prev_page_url ?? ''"
                        class="rounded-2xl border border-white/60 bg-white/70 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                        :class="users.prev_page_url ? '' : 'pointer-events-none opacity-50'"
                        preserve-scroll
                    >
                        Anterior
                    </Link>
                    <Link
                        :href="users.next_page_url ?? ''"
                        class="rounded-2xl border border-white/60 bg-white/70 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                        :class="users.next_page_url ? '' : 'pointer-events-none opacity-50'"
                        preserve-scroll
                    >
                        Próximo
                    </Link>
                    <button
                        type="button"
                        class="rounded-2xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                        @click="openCreate"
                    >
                        Novo usuário
                    </button>
                </div>
            </div>
        </template>

        <div class="px-4 py-8 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden rounded-3xl border border-white/60 bg-white/70 shadow-sm backdrop-blur">
                    <div class="border-b border-white/60 px-6 py-5">
                        <div class="text-sm font-semibold text-slate-900">
                            Usuários
                        </div>
                    </div>

                    <div class="divide-y divide-white/60">
                        <div
                            v-for="row in users.data"
                            :key="row.id"
                            class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <div class="truncate text-base font-semibold text-slate-900">
                                        {{ row.name }}
                                    </div>
                                    <div class="text-xs font-semibold text-slate-500">
                                        #{{ row.id }}
                                    </div>
                                    <span
                                        v-if="row.is_admin"
                                        class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700"
                                    >
                                        Admin
                                    </span>
                                    <span
                                        v-else-if="row.is_staff"
                                        class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"
                                    >
                                        Sócio
                                    </span>
                                    <span
                                        v-else
                                        class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500"
                                    >
                                        Sem acesso
                                    </span>
                                </div>

                                <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-600">
                                    <div class="truncate">
                                        {{ row.email }}
                                    </div>
                                    <div class="truncate text-xs text-slate-500">
                                        {{ formatDate(row.created_at) }}
                                    </div>
                                </div>

                                <div v-if="adminForm.hasErrors && adminForm.errors.is_admin" class="mt-2 text-xs font-semibold text-rose-600">
                                    {{ adminForm.errors.is_admin }}
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                <button
                                    type="button"
                                    class="rounded-2xl border border-white/60 bg-white/70 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                    @click="openPassword(row)"
                                >
                                    Trocar senha
                                </button>
                                <button
                                    v-if="isAdmin"
                                    type="button"
                                    class="rounded-2xl px-4 py-2 text-sm font-semibold shadow-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                    :class="row.is_admin ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                                    :disabled="adminForm.processing"
                                    @click="toggleAdmin(row)"
                                >
                                    {{ row.is_admin ? 'Remover admin' : 'Tornar admin' }}
                                </button>
                            </div>
                        </div>

                        <div v-if="users.data.length === 0" class="px-6 py-10 text-sm text-slate-600">
                            Nenhum usuário encontrado.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-show="isCreateOpen || isPasswordOpen"
                class="fixed inset-0 z-50 bg-slate-900/30 backdrop-blur-sm"
                @click="isCreateOpen ? closeCreate() : closePassword()"
                style="display: none"
            ></div>
        </Transition>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="translate-y-3 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-3 opacity-0"
        >
            <div
                v-show="isCreateOpen"
                class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full max-w-xl p-4 sm:bottom-auto sm:top-24"
                style="display: none"
            >
                <div class="overflow-hidden rounded-3xl border border-white/60 bg-white/80 shadow-xl backdrop-blur">
                    <div class="border-b border-white/60 px-6 py-5">
                        <div class="text-base font-semibold text-slate-900">
                            Criar usuário
                        </div>
                        <div class="mt-1 text-sm text-slate-600">
                            Selecione o cargo, email e senha.
                        </div>
                    </div>
                    <form class="space-y-4 px-6 py-6" @submit.prevent="submitCreate">
                        <div>
                            <label class="text-sm font-semibold text-slate-800">Cargo</label>
                            <select
                                v-model="createForm.role"
                                class="mt-2 w-full rounded-2xl border border-white/60 bg-white/70 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                            >
                                <option value="socio">Sócio (leitura)</option>
                                <option value="admin">Admin</option>
                            </select>
                            <div class="mt-2 text-xs text-slate-500">
                                <span v-if="createForm.role === 'admin'">Admin pode editar tudo e ver “Equipe”.</span>
                                <span v-else>Sócio pode visualizar o admin e não vê “Equipe”.</span>
                            </div>
                            <div v-if="createForm.errors.role" class="mt-2 text-xs font-semibold text-rose-600">
                                {{ createForm.errors.role }}
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-800">Email</label>
                            <input
                                v-model="createForm.email"
                                type="email"
                                class="mt-2 w-full rounded-2xl border border-white/60 bg-white/70 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                placeholder="email@empresa.com"
                                autocomplete="off"
                            />
                            <div v-if="createForm.errors.email" class="mt-2 text-xs font-semibold text-rose-600">
                                {{ createForm.errors.email }}
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-800">Senha</label>
                            <input
                                v-model="createForm.password"
                                type="password"
                                class="mt-2 w-full rounded-2xl border border-white/60 bg-white/70 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                placeholder="mínimo 8 caracteres"
                                autocomplete="new-password"
                            />
                            <div v-if="createForm.errors.password" class="mt-2 text-xs font-semibold text-rose-600">
                                {{ createForm.errors.password }}
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button
                                type="button"
                                class="rounded-2xl border border-white/60 bg-white/70 px-5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-white"
                                @click="closeCreate"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="rounded-2xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 disabled:opacity-50"
                                :disabled="createForm.processing"
                            >
                                Criar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="translate-y-3 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-3 opacity-0"
        >
            <div
                v-show="isPasswordOpen"
                class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full max-w-xl p-4 sm:bottom-auto sm:top-24"
                style="display: none"
            >
                <div class="overflow-hidden rounded-3xl border border-white/60 bg-white/80 shadow-xl backdrop-blur">
                    <div class="border-b border-white/60 px-6 py-5">
                        <div class="text-base font-semibold text-slate-900">
                            Alterar senha
                        </div>
                        <div class="mt-1 text-sm text-slate-600">
                            {{ selected?.email ?? '' }}
                        </div>
                    </div>
                    <form class="space-y-4 px-6 py-6" @submit.prevent="submitPassword">
                        <div>
                            <label class="text-sm font-semibold text-slate-800">Nova senha</label>
                            <input
                                v-model="passwordForm.password"
                                type="password"
                                class="mt-2 w-full rounded-2xl border border-white/60 bg-white/70 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                placeholder="mínimo 8 caracteres"
                                autocomplete="new-password"
                            />
                            <div v-if="passwordForm.errors.password" class="mt-2 text-xs font-semibold text-rose-600">
                                {{ passwordForm.errors.password }}
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button
                                type="button"
                                class="rounded-2xl border border-white/60 bg-white/70 px-5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-white"
                                @click="closePassword"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="rounded-2xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 disabled:opacity-50"
                                :disabled="passwordForm.processing"
                            >
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>
