<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import FuturisticBackground from '@/Components/Marketing/FuturisticBackground.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();
const canAccessAdmin = computed(() => Boolean((page.props as any)?.auth?.user?.is_admin || (page.props as any)?.auth?.user?.is_staff));
const isAdmin = computed(() => Boolean((page.props as any)?.auth?.user?.is_admin));
const userRoleLabel = computed(() => {
    const user = (page.props as any)?.auth?.user;
    if (user?.is_admin) return 'Admin';
    if (user?.is_staff) return 'Sócio';
    return 'Cliente';
});

const isLoaded = ref(false);
onMounted(() => {
    setTimeout(() => {
        isLoaded.value = true;
    }, 100);
});
</script>

<template>
    <div class="min-h-screen bg-[#030305] text-white selection:bg-indigo-500/30 selection:text-indigo-200 overflow-hidden font-sans">
        <!-- Deep Space Background -->
        <div class="fixed inset-0 z-0 pointer-events-none">
            <FuturisticBackground />
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-[#030305]/80 to-[#030305]"></div>
            <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] bg-indigo-600/10 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-[-20%] right-[-10%] w-[50%] h-[50%] bg-purple-600/10 rounded-full blur-[120px] animate-pulse-slow delay-1000"></div>
        </div>

        <!-- Mobile Sidebar Backdrop -->
        <Transition
            enter-active-class="transition-opacity ease-linear duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-linear duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-show="showingNavigationDropdown"
                class="fixed inset-0 z-40 bg-black/90 backdrop-blur-md lg:hidden"
                @click="showingNavigationDropdown = false"
            ></div>
        </Transition>

        <!-- Mobile Sidebar -->
        <Transition
            enter-active-class="transition ease-out duration-300 transform"
            enter-from-class="-translate-x-full opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition ease-in duration-200 transform"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="-translate-x-full opacity-0"
        >
            <div
                v-show="showingNavigationDropdown"
                class="fixed inset-y-0 left-0 z-50 w-80 max-w-[85vw] bg-[#0A0A0C]/95 border-r border-white/10 backdrop-blur-xl shadow-2xl lg:hidden"
            >
                <div class="flex h-20 items-center justify-between px-6 border-b border-white/5">
                    <Link :href="route('dashboard')" class="flex items-center gap-3 group">
                        <div class="relative">
                            <div class="absolute inset-0 bg-indigo-500/20 blur-md rounded-full group-hover:bg-indigo-500/40 transition-all duration-500"></div>
                            <ApplicationLogo icon-only class="relative h-9 w-9 z-10" />
                        </div>
                        <span class="text-lg font-bold text-white tracking-tight group-hover:text-indigo-200 transition-colors">MedidaTek</span>
                    </Link>
                    <button type="button" class="p-2 text-white/50 hover:text-white hover:bg-white/10 rounded-lg transition-all" @click="showingNavigationDropdown = false">
                        <span class="sr-only">Fechar menu</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <nav class="flex flex-1 flex-col gap-y-6 px-6 py-8 overflow-y-auto">
                    <ul role="list" class="space-y-2">
                        <li>
                            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                Painel
                            </ResponsiveNavLink>
                        </li>
                        <li v-if="canAccessAdmin">
                            <ResponsiveNavLink :href="route('admin.leads.index')" :active="route().current('admin.leads.*')">
                                Leads
                            </ResponsiveNavLink>
                        </li>
                        <li v-if="canAccessAdmin">
                            <ResponsiveNavLink :href="route('admin.projects.index')" :active="route().current('admin.projects.*')">
                                Projetos
                            </ResponsiveNavLink>
                        </li>
                        <li v-if="canAccessAdmin">
                            <ResponsiveNavLink :href="route('admin.ai')" :active="route().current('admin.ai')">
                                IA
                            </ResponsiveNavLink>
                        </li>
                        <li v-if="canAccessAdmin">
                            <ResponsiveNavLink :href="route('admin.landing.bento.edit')" :active="route().current('admin.landing.bento.*')">
                                Site
                            </ResponsiveNavLink>
                        </li>
                        <li v-if="isAdmin">
                            <ResponsiveNavLink :href="route('admin.team.index')" :active="route().current('admin.team.*')">
                                Equipe
                            </ResponsiveNavLink>
                        </li>
                    </ul>
                </nav>
            </div>
        </Transition>

        <!-- Desktop Sidebar (Floating Dock Style) -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-80 lg:flex-col p-4">
            <div 
                class="flex grow flex-col gap-y-6 overflow-y-auto rounded-[2rem] border border-white/10 bg-[#0A0A0C]/60 px-6 py-6 shadow-2xl backdrop-blur-2xl ring-1 ring-white/5 transition-all duration-700"
                :class="isLoaded ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-10'"
            >
                <div class="flex h-12 shrink-0 items-center gap-4 px-2">
                    <div class="relative group cursor-pointer">
                        <div class="absolute inset-0 bg-indigo-500/20 blur-lg rounded-full opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
                        <ApplicationLogo icon-only class="relative h-10 w-10 z-10 transform group-hover:scale-110 transition-transform duration-300" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-base font-bold text-white tracking-tight">MedidaTek</span>
                        <span class="text-[10px] uppercase tracking-widest font-bold text-indigo-400/80">{{ userRoleLabel }}</span>
                    </div>
                </div>

                <nav class="flex flex-1 flex-col mt-4">
                    <ul role="list" class="flex flex-1 flex-col gap-y-8">
                        <li>
                            <div class="text-[10px] font-bold uppercase tracking-widest text-white/30 mb-4 px-2">Menu Principal</div>
                            <ul role="list" class="-mx-2 space-y-2">
                                <li>
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                <svg class="h-5 w-5 shrink-0 transition-all duration-300" :class="route().current('dashboard') ? 'text-indigo-400 drop-shadow-[0_0_8px_rgba(129,140,248,0.5)]' : 'text-white/40 group-hover:text-white'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="7" height="9" rx="1"></rect>
                                    <rect x="14" y="3" width="7" height="5" rx="1"></rect>
                                    <rect x="14" y="12" width="7" height="9" rx="1"></rect>
                                    <rect x="3" y="16" width="7" height="5" rx="1"></rect>
                                </svg>
                                <span class="relative">Painel</span>
                                <span v-if="route().current('dashboard')" class="ml-auto flex h-1.5 w-1.5">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-400 opacity-75"></span>
                                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                </span>
                            </NavLink>
                        </li>
                        <li v-if="canAccessAdmin">
                            <NavLink :href="route('admin.leads.index')" :active="route().current('admin.leads.*')">
                                <svg class="h-5 w-5 shrink-0 transition-all duration-300" :class="route().current('admin.leads.*') ? 'text-indigo-400 drop-shadow-[0_0_8px_rgba(129,140,248,0.5)]' : 'text-white/40 group-hover:text-white'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span>Leads</span>
                                <span v-if="route().current('admin.leads.*')" class="ml-auto flex h-1.5 w-1.5">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-400 opacity-75"></span>
                                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                </span>
                            </NavLink>
                        </li>
                                <li v-if="canAccessAdmin">
                            <NavLink :href="route('admin.projects.index')" :active="route().current('admin.projects.*')">
                                <svg class="h-5 w-5 shrink-0 transition-all duration-300" :class="route().current('admin.projects.*') ? 'text-indigo-400 drop-shadow-[0_0_8px_rgba(129,140,248,0.5)]' : 'text-white/40 group-hover:text-white'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <span>Projetos</span>
                                <span v-if="route().current('admin.projects.*')" class="ml-auto flex h-1.5 w-1.5">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-400 opacity-75"></span>
                                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                </span>
                            </NavLink>
                        </li>
                        <li v-if="canAccessAdmin">
                            <NavLink :href="route('admin.ai')" :active="route().current('admin.ai')">
                                <svg class="h-5 w-5 shrink-0 transition-all duration-300" :class="route().current('admin.ai') ? 'text-indigo-400 drop-shadow-[0_0_8px_rgba(129,140,248,0.5)]' : 'text-white/40 group-hover:text-white'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2a10 10 0 1 0 10 10H12V2z"></path>
                                    <path d="M12 2a10 10 0 0 1 10 10h-10V2z"></path>
                                    <path d="M12 12L2.5 10.5"></path>
                                    <path d="M12 12l9.5-1.5"></path>
                                    <circle cx="12" cy="12" r="2"></circle>
                                </svg>
                                <span>IA</span>
                                <span v-if="route().current('admin.ai')" class="ml-auto flex h-1.5 w-1.5">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-400 opacity-75"></span>
                                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                </span>
                            </NavLink>
                        </li>
                                <li v-if="canAccessAdmin">
                            <NavLink :href="route('admin.landing.bento.edit')" :active="route().current('admin.landing.bento.*')">
                                <svg class="h-5 w-5 shrink-0 transition-all duration-300" :class="route().current('admin.landing.bento.*') ? 'text-indigo-400 drop-shadow-[0_0_8px_rgba(129,140,248,0.5)]' : 'text-white/40 group-hover:text-white'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="3" y1="9" x2="21" y2="9"></line>
                                    <line x1="9" y1="21" x2="9" y2="9"></line>
                                </svg>
                                <span>Site</span>
                                <span v-if="route().current('admin.landing.bento.*')" class="ml-auto flex h-1.5 w-1.5">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-400 opacity-75"></span>
                                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                </span>
                            </NavLink>
                        </li>
                        <li v-if="isAdmin">
                            <NavLink :href="route('admin.team.index')" :active="route().current('admin.team.*')">
                                <svg class="h-5 w-5 shrink-0 transition-all duration-300" :class="route().current('admin.team.*') ? 'text-indigo-400 drop-shadow-[0_0_8px_rgba(129,140,248,0.5)]' : 'text-white/40 group-hover:text-white'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span>Equipe</span>
                                <span v-if="route().current('admin.team.*')" class="ml-auto flex h-1.5 w-1.5">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-400 opacity-75"></span>
                                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                </span>
                            </NavLink>
                        </li>
                            </ul>
                        </li>
                        <li class="mt-auto">
                            <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-white/5 p-4 transition-all duration-300 hover:border-white/10 hover:bg-white/10 hover:shadow-lg hover:shadow-indigo-500/10">
                                <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                                <div class="relative flex items-center gap-x-4">
                                    <div class="h-10 w-10 flex-none rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 p-[1px] shadow-lg shadow-indigo-500/20">
                                        <div class="flex h-full w-full items-center justify-center rounded-xl bg-[#0A0A0C] text-sm font-bold text-white">
                                            {{ ($page.props.auth.user?.name ?? '').slice(0, 1).toUpperCase() }}
                                        </div>
                                    </div>
                                    <div class="flex flex-col overflow-hidden">
                                        <span class="truncate text-sm font-bold text-white group-hover:text-indigo-200 transition-colors">{{ $page.props.auth.user?.name }}</span>
                                        <span class="truncate text-xs text-white/40">{{ $page.props.auth.user?.email }}</span>
                                    </div>
                                    <Link :href="route('logout')" method="post" as="button" class="ml-auto p-1.5 text-white/40 hover:text-white transition-colors">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                        </svg>
                                    </Link>
                                </div>
                            </div>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="lg:pl-80 transition-all duration-700" :class="isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
            <!-- Floating Header -->
            <div class="sticky top-4 z-40 mx-4 sm:mx-6 lg:mx-8 mb-8">
                <div class="flex h-16 items-center gap-x-4 rounded-2xl border border-white/10 bg-[#0A0A0C]/70 px-4 shadow-xl backdrop-blur-xl ring-1 ring-white/5 transition-all duration-300 hover:bg-[#0A0A0C]/80 hover:border-white/20">
                    <button type="button" class="-m-2.5 p-2.5 text-white/70 lg:hidden hover:text-white transition-colors" @click="showingNavigationDropdown = true">
                        <span class="sr-only">Abrir menu</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <div class="h-6 w-px bg-white/10 lg:hidden" aria-hidden="true"></div>

                    <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                        <div class="relative flex flex-1 items-center">
                            <h1 class="text-xl font-bold tracking-tight text-white drop-shadow-md" v-if="$slots.header">
                                <slot name="header" />
                            </h1>
                            <h1 class="text-xl font-bold tracking-tight text-white drop-shadow-md" v-else>
                                Dashboard
                            </h1>
                        </div>
                        <div class="flex items-center gap-x-4 lg:gap-x-6">
                            <!-- Search / Tools Placeholder -->
                            <div class="hidden sm:flex items-center gap-2">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-4 h-4 text-white/30" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                        </svg>
                                    </div>
                                    <input type="text" class="block w-full p-2 pl-10 text-sm text-white border border-white/10 rounded-xl bg-white/5 focus:ring-indigo-500 focus:border-indigo-500 placeholder-white/30 transition-all focus:bg-white/10" placeholder="Buscar...">
                                </div>
                            </div>

                            <div class="h-6 w-px bg-white/10" aria-hidden="true"></div>

                            <!-- Notifications -->
                            <button type="button" class="-m-2.5 p-2.5 text-white/50 hover:text-white transition-colors relative">
                                <span class="sr-only">Notificações</span>
                                <div class="absolute top-2 right-2 h-2 w-2 rounded-full bg-indigo-500 animate-pulse"></div>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                            </button>

                            <!-- User dropdown -->
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button type="button" class="-m-1.5 flex items-center p-1.5 text-white hover:text-indigo-300 transition-colors">
                                        <span class="sr-only">Abrir menu do usuário</span>
                                        <img class="h-8 w-8 rounded-full bg-white/10 object-cover ring-2 ring-white/10" :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user?.name}&background=6366f1&color=fff`" alt="" />
                                        <span class="hidden lg:flex lg:items-center">
                                            <svg class="ml-2 h-5 w-5 text-white/50" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </button>
                                </template>
                                <template #content>
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        Gerenciar Conta
                                    </div>
                                    <DropdownLink :href="route('profile.edit')">
                                        Perfil
                                    </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">
                                        Sair
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="px-4 sm:px-6 lg:px-8 pb-10">
                <div class="rounded-3xl border border-white/5 bg-[#0A0A0C]/40 p-6 shadow-2xl backdrop-blur-md min-h-[calc(100vh-140px)]">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.animate-pulse-slow {
    animation: pulse 8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>