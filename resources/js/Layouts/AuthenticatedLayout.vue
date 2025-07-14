<script setup>
import { ref, onMounted } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import ApplicationLogoMini from "@/Components/ApplicationLogoMini.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import { Link } from "@inertiajs/vue3";

const isSidebarOpen = ref(false); // Cambiado a false para que empiece cerrado
const showingNavigationDropdown = ref(false);

// Ajustar estado según el tamaño de pantalla
onMounted(() => {
  const handleResize = () => {
    if (window.innerWidth < 1024) {
      isSidebarOpen.value = false;
    } else {
      // En pantallas grandes, dejamos el valor por defecto (false)
    }
  };
  
  window.addEventListener('resize', handleResize);
  handleResize();
  
  return () => window.removeEventListener('resize', handleResize);
});
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Sidebar -->
        <div class="fixed lg:relative inset-y-0 left-0 z-30 h-screen">
            <div 
                class="h-full bg-white shadow-lg flex flex-col transition-all duration-300 ease-in-out"
                :style="{ width: isSidebarOpen ? '250px' : '80px' }"
                :class="{ center: isSidebarOpen }"
            >
                <!-- Logo -->
                <div class="flex items-center justify-center p-4 border-b h-16">
                    <Link :href="route('dashboard')" class="flex items-center justify-center">
                        <ApplicationLogo v-if="isSidebarOpen" class="block h-9 w-auto fill-current text-gray-800" />
                        <ApplicationLogoMini v-else class="block h-9 w-auto fill-current text-gray-800" />
                    </Link>
                </div>

                <!-- Menú colapsable -->
                <nav class="flex-1 overflow-y-auto py-2">
                    <!-- Botón para colapsar/expandir -->
                    <button
                        @click="isSidebarOpen = !isSidebarOpen"
                        class="w-full flex items-center justify-center p-3 hover:bg-gray-100"
                    >
                        <svg
                            class="h-6 w-6 text-gray-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                :d="isSidebarOpen ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'"
                            />
                        </svg>
                        <span v-if="isSidebarOpen" class="ml-2 text-sm"></span>
                    </button>

                    <!-- Carteras Dropdown -->
                    <!-- <div class="">
                        <Dropdown align="left" width="full">
                            <template #trigger>
                                <button
                                    class="w-full flex items-center px-3 py-2 rounded-md hover:bg-gray-100"
                                    :class="{ 'bg-purple-50 text-purple-600': route().current('carteras.*') }"
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
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                        />
                                    </svg>
                                    <transition name="fade">
                                        <span v-if="isSidebarOpen" class="ml-3">Carteras</span>
                                    </transition>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink href="#">Win</DropdownLink>
                                <DropdownLink href="#">PerúFibra</DropdownLink>
                                <DropdownLink href="#">CablePerú</DropdownLink>
                                <DropdownLink href="#">Telefonia</DropdownLink>
                                <DropdownLink href="#">Energia</DropdownLink>
                            </template>
                        </Dropdown>
                    </div> -->

                    <!-- Items del menú -->
                    <NavLink
                        :href="route('dashboard')"
                        :active="route().current('dashboard')"
                        class="flex items-center px-3 py-3 hover:bg-gray-100 full"
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
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                            />
                        </svg>
                        <transition name="fade">
                            <span v-if="isSidebarOpen" class="ml-3">Dashboard</span>
                        </transition>
                    </NavLink>

                    <NavLink
                        :href="route('carteras.index')"
                        :active="route().current('carteras.index')"
                        class="flex items-center px-3 py-3 hover:bg-gray-100 full"
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
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        <transition name="fade">
                            <span v-if="isSidebarOpen" class="ml-3">Gestionar Carteras</span>
                        </transition>
                    </NavLink>

                    <NavLink
                        :href="route('users.index')"
                        :active="route().current('users.index')"
                        class="flex items-center px-3 py-3 hover:bg-gray-100"
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
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        <transition name="fade">
                            <span v-if="isSidebarOpen" class="ml-3">Gestionar Usuarios</span>
                        </transition>
                    </NavLink>
                </nav>
            </div>
        </div>

        <!-- Contenido principal -->
        <div 
            class="flex-1 flex flex-col overflow-hidden transition-all duration-300 ease-in-out"
        >
            <!-- Barra superior -->
            <header class="bg-white shadow-sm z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16 items-center">
                        <!-- Botón para mostrar/ocultar sidebar en móvil -->
                        <button
                            @click="isSidebarOpen = !isSidebarOpen"
                            class="p-2 rounded-md hover:bg-gray-100 lg:hidden"
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

                        <div class="flex-1"></div>

                        <!-- Menú de usuario -->
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
                <div class="mx-auto">
                    <slot name="header" />
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style>
/* Transiciones */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Estilo para enlaces activos - Cambiado a morado */
.router-link-active {
    @apply bg-purple-50 text-purple-600;
}

.router-link-active svg {
    @apply text-purple-600;
}

/* Ajustes para el menú colapsado */
[style*="width: 80px"] .dropdown-content {
    left: 80px;
    min-width: 200px;
}

/* Asegurar que el contenido no se desborde */
.max-w-7xl {
    max-width: 100%;
}

@media (min-width: 1280px) {
    .max-w-7xl {
        max-width: 80rem;
    }
}

/* Asegurar que el sidebar ocupe toda la altura */
.h-screen {
    height: 100vh;
}

/* Ajustar posición del sidebar */
.fixed.inset-y-0 {
    top: 0;
    bottom: 0;
}
</style>