<script setup lang="ts">
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();
const canAccessAdmin = computed(() => Boolean((page.props as any)?.auth?.user?.is_admin || (page.props as any)?.auth?.user?.is_staff));
const isAdmin = computed(() => Boolean((page.props as any)?.auth?.user?.is_admin));
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
            <div class="absolute -top-48 left-1/2 h-[32rem] w-[32rem] -translate-x-1/2 rounded-full bg-indigo-200/40 blur-3xl"></div>
            <div class="absolute -bottom-56 right-[-10rem] h-[34rem] w-[34rem] rounded-full bg-fuchsia-200/30 blur-3xl"></div>
            <div class="absolute -bottom-56 left-[-12rem] h-[30rem] w-[30rem] rounded-full bg-sky-200/25 blur-3xl"></div>
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
                v-show="showingNavigationDropdown"
                class="fixed inset-0 z-50 bg-slate-900/25 backdrop-blur-sm lg:hidden"
                @click="showingNavigationDropdown = false"
                style="display: none"
            ></div>
        </Transition>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="-translate-x-6 opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="-translate-x-6 opacity-0"
        >
            <div
                v-show="showingNavigationDropdown"
                class="fixed inset-y-0 left-0 z-50 w-80 max-w-[85vw] overflow-y-auto border-r border-white/40 bg-white/80 shadow-xl backdrop-blur lg:hidden"
                style="display: none"
            >
                <div class="flex h-16 items-center justify-between px-5">
                    <Link
                        :href="route('dashboard')"
                        class="inline-flex items-center gap-3"
                        @click="showingNavigationDropdown = false"
                    >
                        <div class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-600 text-white shadow-sm">
                            <ApplicationLogo class="h-6 w-6 fill-current" />
                        </div>
                        <div class="leading-tight">
                            <div class="text-sm font-semibold text-slate-900">
                                Admin
                            </div>
                            <div class="text-xs text-slate-500">
                                MedidaTek
                            </div>
                        </div>
                    </Link>

                    <button
                        type="button"
                        class="grid h-10 w-10 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                        @click="showingNavigationDropdown = false"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                </div>

                <div class="px-3 pb-6 pt-2">
                    <div class="space-y-1">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                            @click="showingNavigationDropdown = false"
                        >
                            <span class="inline-flex items-center gap-3">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.707 1.707a1 1 0 00-1.414 0l-7 7A1 1 0 003 10h1v7a2 2 0 002 2h3a1 1 0 001-1v-4h2v4a1 1 0 001 1h3a2 2 0 002-2v-7h1a1 1 0 00.707-1.707l-7-7z" />
                                </svg>
                                Painel
                            </span>
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            v-if="canAccessAdmin"
                            :href="route('admin.leads.index')"
                            :active="route().current('admin.leads.*')"
                            @click="showingNavigationDropdown = false"
                        >
                            <span class="inline-flex items-center gap-3">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5z" />
                                    <path d="M4 6h12v2H4V6z" />
                                </svg>
                                Leads
                            </span>
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            v-if="canAccessAdmin"
                            :href="route('admin.projects.index')"
                            :active="route().current('admin.projects.*')"
                            @click="showingNavigationDropdown = false"
                        >
                            <span class="inline-flex items-center gap-3">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M4 3a2 2 0 00-2 2v11a1 1 0 001.447.894L6 15.618l2.553 1.276A1 1 0 009 16V5a2 2 0 00-2-2H4z" />
                                    <path d="M11 5a2 2 0 012-2h3a2 2 0 012 2v11a1 1 0 01-1.447.894L14 15.618l-2.553 1.276A1 1 0 0111 16V5z" />
                                </svg>
                                Projetos
                            </span>
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            v-if="canAccessAdmin"
                            :href="route('admin.ai')"
                            :active="route().current('admin.ai')"
                            @click="showingNavigationDropdown = false"
                        >
                            <span class="inline-flex items-center gap-3">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M4 3a2 2 0 00-2 2v4a2 2 0 002 2h3V3H4z" />
                                    <path d="M9 3h2v8H9V3z" />
                                    <path d="M13 3h3a2 2 0 012 2v4a2 2 0 01-2 2h-3V3z" />
                                    <path d="M7 13H4a2 2 0 00-2 2v2h7v-4z" />
                                    <path d="M9 13h2v4H9v-4z" />
                                    <path d="M18 17v-2a2 2 0 00-2-2h-3v4h5z" />
                                </svg>
                                IA
                            </span>
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            v-if="canAccessAdmin"
                            :href="route('admin.landing.bento.edit')"
                            :active="route().current('admin.landing.bento.*')"
                            @click="showingNavigationDropdown = false"
                        >
                            <span class="inline-flex items-center gap-3">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M3 3a2 2 0 012-2h10a2 2 0 012 2v6H3V3z" />
                                    <path d="M3 11h6v8H5a2 2 0 01-2-2v-6z" />
                                    <path d="M11 11h8v6a2 2 0 01-2 2h-6v-8z" />
                                </svg>
                                Site
                            </span>
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            v-if="isAdmin"
                            :href="route('admin.team.index')"
                            :active="route().current('admin.team.*')"
                            @click="showingNavigationDropdown = false"
                        >
                            <span class="inline-flex items-center gap-3">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path fill-rule="evenodd" d="M5 14a4 4 0 018 0v3H5v-3z" clip-rule="evenodd" />
                                    <path d="M17 9a2 2 0 11-4 0 2 2 0 014 0z" />
                                    <path d="M17 14a3 3 0 00-3-3h-1.5a5.5 5.5 0 011.2 3H17z" />
                                </svg>
                                Equipe
                            </span>
                        </ResponsiveNavLink>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white/70 p-4 shadow-sm">
                        <div class="text-sm font-semibold text-slate-900">
                            {{ $page.props.auth.user?.name ?? '' }}
                        </div>
                        <div class="mt-0.5 text-xs text-slate-500">
                            {{ $page.props.auth.user?.email ?? '' }}
                        </div>

                        <div class="mt-4 space-y-1">
                            <ResponsiveNavLink
                                :href="route('profile.edit')"
                                @click="showingNavigationDropdown = false"
                            >
                                Perfil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                                @click="showingNavigationDropdown = false"
                            >
                                Sair
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <div class="relative flex min-h-screen">
            <aside class="sticky top-0 hidden h-screen w-72 flex-col overflow-y-auto border-r border-white/40 bg-white/70 backdrop-blur lg:flex">
                <div class="flex h-16 items-center px-5">
                    <Link :href="route('dashboard')" class="inline-flex items-center gap-3">
                        <div class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-600 text-white shadow-sm">
                            <ApplicationLogo class="h-6 w-6 fill-current" />
                        </div>
                        <div class="leading-tight">
                            <div class="text-sm font-semibold text-slate-900">
                                Admin
                            </div>
                            <div class="text-xs text-slate-500">
                                MedidaTek
                            </div>
                        </div>
                    </Link>
                </div>

                <div class="flex flex-1 flex-col gap-6 px-3 py-6">
                    <nav class="space-y-1">
                        <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            <span class="inline-flex items-center gap-3">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.707 1.707a1 1 0 00-1.414 0l-7 7A1 1 0 003 10h1v7a2 2 0 002 2h3a1 1 0 001-1v-4h2v4a1 1 0 001 1h3a2 2 0 002-2v-7h1a1 1 0 00.707-1.707l-7-7z" />
                                </svg>
                                Painel
                            </span>
                        </NavLink>

                        <NavLink v-if="canAccessAdmin" :href="route('admin.leads.index')" :active="route().current('admin.leads.*')">
                            <span class="inline-flex items-center gap-3">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5z" />
                                    <path d="M4 6h12v2H4V6z" />
                                </svg>
                                Leads
                            </span>
                        </NavLink>

                        <NavLink
                            v-if="canAccessAdmin"
                            :href="route('admin.projects.index')"
                            :active="route().current('admin.projects.*')"
                        >
                            <span class="inline-flex items-center gap-3">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M4 3a2 2 0 00-2 2v11a1 1 0 001.447.894L6 15.618l2.553 1.276A1 1 0 009 16V5a2 2 0 00-2-2H4z" />
                                    <path d="M11 5a2 2 0 012-2h3a2 2 0 012 2v11a1 1 0 01-1.447.894L14 15.618l-2.553 1.276A1 1 0 0111 16V5z" />
                                </svg>
                                Projetos
                            </span>
                        </NavLink>

                        <NavLink v-if="canAccessAdmin" :href="route('admin.ai')" :active="route().current('admin.ai')">
                            <span class="inline-flex items-center gap-3">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M4 3a2 2 0 00-2 2v4a2 2 0 002 2h3V3H4z" />
                                    <path d="M9 3h2v8H9V3z" />
                                    <path d="M13 3h3a2 2 0 012 2v4a2 2 0 01-2 2h-3V3z" />
                                    <path d="M7 13H4a2 2 0 00-2 2v2h7v-4z" />
                                    <path d="M9 13h2v4H9v-4z" />
                                    <path d="M18 17v-2a2 2 0 00-2-2h-3v4h5z" />
                                </svg>
                                IA
                            </span>
                        </NavLink>

                        <NavLink
                            v-if="canAccessAdmin"
                            :href="route('admin.landing.bento.edit')"
                            :active="route().current('admin.landing.bento.*')"
                        >
                            <span class="inline-flex items-center gap-3">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M3 3a2 2 0 012-2h10a2 2 0 012 2v6H3V3z" />
                                    <path d="M3 11h6v8H5a2 2 0 01-2-2v-6z" />
                                    <path d="M11 11h8v6a2 2 0 01-2 2h-6v-8z" />
                                </svg>
                                Site
                            </span>
                        </NavLink>

                        <NavLink v-if="isAdmin" :href="route('admin.team.index')" :active="route().current('admin.team.*')">
                            <span class="inline-flex items-center gap-3">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path fill-rule="evenodd" d="M5 14a4 4 0 018 0v3H5v-3z" clip-rule="evenodd" />
                                    <path d="M17 9a2 2 0 11-4 0 2 2 0 014 0z" />
                                    <path d="M17 14a3 3 0 00-3-3h-1.5a5.5 5.5 0 011.2 3H17z" />
                                </svg>
                                Equipe
                            </span>
                        </NavLink>
                    </nav>

                    <div class="mt-auto rounded-2xl border border-slate-200/70 bg-white/70 p-4 shadow-sm">
                        <div class="text-xs font-semibold text-slate-500">
                            Logado como
                        </div>
                        <div class="mt-1 truncate text-sm font-semibold text-slate-900">
                            {{ $page.props.auth.user?.name ?? '' }}
                        </div>
                        <div class="mt-0.5 truncate text-xs text-slate-500">
                            {{ $page.props.auth.user?.email ?? '' }}
                        </div>
                    </div>
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-40 border-b border-white/40 bg-white/70 backdrop-blur">
                    <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                class="grid h-10 w-10 place-items-center rounded-xl text-slate-600 transition hover:bg-white/60 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 lg:hidden"
                                @click="showingNavigationDropdown = true"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fill-rule="evenodd"
                                        d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm1 4a1 1 0 100 2h12a1 1 0 100-2H4z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>

                            <div class="hidden lg:block">
                                <div class="text-sm font-semibold text-slate-900">
                                    Painel administrativo
                                </div>
                                <div class="text-xs text-slate-500">
                                    Gerencie conteúdo e configurações
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <Dropdown align="right" width="48" content-classes="py-1 bg-white/90 backdrop-blur">
                                <template #trigger>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-3 rounded-2xl border border-white/50 bg-white/70 px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                    >
                                        <span class="grid h-8 w-8 place-items-center rounded-xl bg-indigo-600 text-xs font-bold text-white shadow-sm">
                                            {{ ($page.props.auth.user?.name ?? '').slice(0, 1).toUpperCase() }}
                                        </span>
                                        <span class="hidden sm:block">
                                            {{ $page.props.auth.user?.name ?? '' }}
                                        </span>
                                        <svg class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')"> Perfil </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">
                                        Sair
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </header>

                <div v-if="$slots.header" class="px-4 pt-6 sm:px-6 lg:px-8">
                    <div class="rounded-3xl border border-white/50 bg-white/70 p-6 shadow-sm backdrop-blur">
                        <slot name="header" />
                    </div>
                </div>

                <main class="min-w-0 flex-1">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
