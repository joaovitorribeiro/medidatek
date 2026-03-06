<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';
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
    <div class="min-h-screen bg-slate-100 text-slate-900 font-sans selection:bg-indigo-500/20 selection:text-indigo-900">
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
                class="fixed inset-0 z-40 bg-slate-900/35 backdrop-blur-[2px] lg:hidden"
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
                class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 shadow-2xl lg:hidden flex flex-col"
            >
                <div class="flex h-20 items-center justify-between px-6 border-b border-slate-200">
                    <Link :href="route('dashboard')" class="flex items-center gap-3">
                        <ApplicationLogo icon-only class="h-8 w-8 text-indigo-600" />
                        <span class="text-lg font-bold text-slate-900 tracking-wide">MedidaTek</span>
                    </Link>
                    <button type="button" class="text-slate-400 hover:text-slate-700 transition-colors" @click="showingNavigationDropdown = false">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <nav class="flex-1 px-4 py-6 overflow-y-auto">
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

        <!-- Desktop Sidebar -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col bg-white border-r border-slate-200">
            <!-- Logo Area -->
            <div class="flex h-20 shrink-0 items-center gap-3 px-6 border-b border-slate-200">
                <ApplicationLogo icon-only class="h-8 w-8 text-indigo-600" />
                <div class="flex flex-col">
                    <span class="text-lg font-bold text-slate-900 tracking-wide">MedidaTek</span>
                    <span class="text-[10px] uppercase tracking-widest font-semibold text-indigo-600">{{ userRoleLabel }}</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex flex-1 flex-col px-4 py-6 overflow-y-auto">
                <ul role="list" class="flex flex-1 flex-col gap-y-4">
                    <li>
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-4 px-2">Menu</div>
                        <ul role="list" class="space-y-1">
                            <li>
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="7" height="9" rx="1"></rect>
                                        <rect x="14" y="3" width="7" height="5" rx="1"></rect>
                                        <rect x="14" y="12" width="7" height="9" rx="1"></rect>
                                        <rect x="3" y="16" width="7" height="5" rx="1"></rect>
                                    </svg>
                                    <span>Painel</span>
                                </NavLink>
                            </li>
                            <li v-if="canAccessAdmin">
                                <NavLink :href="route('admin.leads.index')" :active="route().current('admin.leads.*')">
                                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                    <span>Leads</span>
                                </NavLink>
                            </li>
                            <li v-if="canAccessAdmin">
                                <NavLink :href="route('admin.projects.index')" :active="route().current('admin.projects.*')">
                                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                                    </svg>
                                    <span>Projetos</span>
                                </NavLink>
                            </li>
                            <li v-if="canAccessAdmin">
                                <NavLink :href="route('admin.ai')" :active="route().current('admin.ai')">
                                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 2a10 10 0 1 0 10 10H12V2z"></path>
                                        <path d="M12 2a10 10 0 0 1 10 10h-10V2z"></path>
                                        <path d="M12 12L2.5 10.5"></path>
                                        <path d="M12 12l9.5-1.5"></path>
                                        <circle cx="12" cy="12" r="2"></circle>
                                    </svg>
                                    <span>IA</span>
                                </NavLink>
                            </li>
                            <li v-if="canAccessAdmin">
                                <NavLink :href="route('admin.landing.bento.edit')" :active="route().current('admin.landing.bento.*')">
                                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="3" y1="9" x2="21" y2="9"></line>
                                        <line x1="9" y1="21" x2="9" y2="9"></line>
                                    </svg>
                                    <span>Site</span>
                                </NavLink>
                            </li>
                            <li v-if="isAdmin">
                                <NavLink :href="route('admin.team.index')" :active="route().current('admin.team.*')">
                                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                    <span>Equipe</span>
                                </NavLink>
                            </li>
                        </ul>
                    </li>

                    <li class="mt-auto">
                        <div class="rounded-xl bg-slate-50 p-4 border border-slate-200">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 flex-none rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold">
                                    {{ ($page.props.auth.user?.name ?? '').slice(0, 1).toUpperCase() }}
                                </div>
                                <div class="flex flex-col overflow-hidden">
                                    <span class="truncate text-sm font-semibold text-slate-900">{{ $page.props.auth.user?.name }}</span>
                                    <span class="truncate text-xs text-slate-500">{{ $page.props.auth.user?.email }}</span>
                                </div>
                            </div>
                            <div class="mt-3 pt-3 border-t border-slate-200 flex justify-between items-center">
                                <Link :href="route('profile.edit')" class="text-xs text-slate-500 hover:text-indigo-600 transition-colors">
                                    Editar Perfil
                                </Link>
                                <Link :href="route('logout')" method="post" as="button" class="text-xs text-slate-500 hover:text-slate-900 transition-colors">
                                    Sair
                                </Link>
                            </div>
                        </div>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content Wrapper -->
        <div class="lg:pl-72 transition-all duration-500">
            <!-- Header -->
            <header class="sticky top-0 z-40 flex h-20 items-center gap-x-4 bg-white/90 px-4 shadow-sm backdrop-blur-md sm:gap-x-6 sm:px-6 lg:px-8 border-b border-slate-200">
                <button type="button" class="-m-2.5 p-2.5 text-slate-500 lg:hidden hover:text-slate-900" @click="showingNavigationDropdown = true">
                    <span class="sr-only">Abrir menu</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="relative flex flex-1 items-center">
                        <div class="w-full max-w-md">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" aria-hidden="true" fill="none" viewBox="0 0 20 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    class="block w-full p-2.5 pl-10 text-sm text-slate-900 border border-slate-300 rounded-xl bg-white focus:ring-indigo-500 focus:border-indigo-500 placeholder-slate-400 transition-colors" 
                                    placeholder="Buscar..."
                                >
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <!-- Notifications -->
                        <button type="button" class="-m-2.5 p-2.5 text-slate-500 hover:text-slate-900 transition-colors relative">
                            <span class="sr-only">Notificações</span>
                            <div class="absolute top-2.5 right-2.5 h-2 w-2 rounded-full bg-indigo-600"></div>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                        </button>

                        <!-- User Dropdown -->
                        <div class="h-6 w-px bg-slate-200" aria-hidden="true"></div>

                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button type="button" class="-m-1.5 flex items-center p-1.5 text-slate-600 hover:text-slate-900 transition-colors">
                                    <span class="sr-only">Abrir menu</span>
                                    <img class="h-8 w-8 rounded-full bg-slate-200 object-cover ring-2 ring-white" :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user?.name}&background=4f46e5&color=fff`" alt="" />
                                    <span class="hidden lg:flex lg:items-center">
                                        <svg class="ml-2 h-5 w-5 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>
                            </template>
                            <template #content>
                                <div class="block px-4 py-2 text-xs text-gray-500">
                                    Conta
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
            </header>

            <div v-if="$slots.header" class="border-b border-slate-200 bg-slate-50/95 px-4 py-5 shadow-sm backdrop-blur sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <slot name="header" />
                </div>
            </div>

            <!-- Main Content -->
            <main class="py-10 px-4 sm:px-6 lg:px-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<style>
/* Custom Scrollbar for Sidebar */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}
::-webkit-scrollbar-thumb:hover {
    background: #6366f1;
}
</style>

