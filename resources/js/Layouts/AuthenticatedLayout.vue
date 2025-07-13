<script setup>
import { ref } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import { Link } from "@inertiajs/vue3";

const isSidebarOpen = ref(false);
const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Overlay para móviles -->
        <transition name="fade">
            <div
                v-if="isSidebarOpen"
                @click="isSidebarOpen = false"
                class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden"
            ></div>
        </transition>

        <!-- Sidebar -->
        <div
            class="fixed lg:relative z-30 transform transition-all duration-300 ease-in-out"
            :class="{
                '-translate-x-full lg:translate-x-0': !isSidebarOpen,
                'translate-x-0': isSidebarOpen,
            }"
        >
            <div
                class="w-64 h-full bg-white shadow-lg flex flex-col menuColapsable"
            >
                <div class="flex items-center justify-between p-4 border-b">
                    <!-- LOGO -->
                    <Link :href="route('dashboard')" class="flex items-center">
                        <ApplicationLogo
                            class="block h-9 w-auto fill-current text-gray-800"
                        />
                    </Link>

                    <button
                        @click="isSidebarOpen = false"
                        class="p-1 rounded-md hover:bg-gray-200 lg:hidden"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 overflow-y-auto">
                    <div class="flex items-center cartera">
                        <Dropdown align="right" width="50">
                            <template #trigger>
                                <span class="inline-flex rounded-md">
                                    <button
                                        type="button"
                                        class="inline-flex items-center focus:outline-none transition ease-in-out duration-150"
                                    >
                                        Cartera
                                        <svg
                                            class="-me-0.5 ms-2 h-4 w-4"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </span>
                            </template>

                            <template #content>
                                <DropdownLink href="#"> Win </DropdownLink>
                                <DropdownLink href="#">
                                    PerúFibra
                                </DropdownLink>
                                <DropdownLink href="#">
                                    CablePerú
                                </DropdownLink>
                                <DropdownLink href="#">
                                    Telefonia
                                </DropdownLink>
                                <DropdownLink href="#"> Energia </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <NavLink
                        :href="route('dashboard')"
                        :active="route().current('dashboard')"
                        class="flex items-center px-4 py-3 hover:bg-gray-100"
                    >
                        <svg
                            class="h-7 w-7 mr-3"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                            />
                        </svg>
                        <span>Dashboard</span>
                    </NavLink>

                    <NavLink
                        :href="route('carteras.index')"
                        :active="route().current('carteras.index')"
                        class="flex items-center px-4 py-3 hover:bg-gray-100"
                    >
                        <svg
                            class="h-7 w-7 mr-3"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        <span>Gestionar Carteras</span>
                    </NavLink>

                    <NavLink
                        :href="route('users.index')"
                        :active="route().current('users.index')"
                        class="flex items-center px-4 py-3 hover:bg-gray-100"
                    >
                        <svg
                            class="h-7 w-7 mr-3"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        <span>Gestionar Usuarios</span>
                    </NavLink>
                </nav>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Barra superior -->
            <header class="bg-white shadow-sm z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16 items-center">
                        <div class="flex">
                            <!-- LOGO -->
                            <Link
                                :href="route('dashboard')"
                                class="flex items-center"
                            >
                                <ApplicationLogo
                                    class="block h-9 w-auto fill-current text-gray-800"
                                />
                            </Link>
                            <button
                                @click="isSidebarOpen = !isSidebarOpen"
                                class="p-2 rounded-md hover:bg-gray-100"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                </svg>
                            </button>
                        </div>

                        <div class="flex items-center">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button
                                            type="button"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                        >
                                            {{ $page.props.auth.user.name }}
                                            <svg
                                                class="-me-0.5 ms-2 h-4 w-4"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </span>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">
                                        Profile
                                    </DropdownLink>
                                    <DropdownLink
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                    >
                                        Log Out
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Contenido de la página -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 bg-gray-100">
                <div class="max-w-7xl mx-auto">
                    <!-- Slot para la cabecera personalizada -->
                    <slot name="header" />
                    <!-- Contenido principal -->
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style>
/* Animación para el sidebar */

.menuColapsable {
    height: 100vh;
}
a.inline-flex.items-center {
    width: 100%;
    padding: 20px;
    font-size: 15px;
}
.cartera,
a.menuActivado {
    background: #5f61ff;
    color: #fff;
}
.cartera {
    width: auto;
    margin: 20px;
    padding: 10px;
    border-radius: 5px;
    text-align: center;
}

.transform {
    transition-property: transform;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}

/* Animación para el overlay */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Estilo para enlaces activos */
.router-link-active {
    background-color: #eff6ff;
    color: #2563eb;
    border-right: 4px solid #2563eb;
}
.router-link-active svg {
    color: #2563eb;
}
</style>
