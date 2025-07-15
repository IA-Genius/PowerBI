<!-- =========================
    1. SCRIPT SETUP
========================= -->
<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { usePage, router, Link } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import ApplicationLogoMini from "@/Components/ApplicationLogoMini.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import {
    BriefcaseIcon,
    UsersIcon,
    ShieldCheckIcon,
    DocumentChartBarIcon,
} from "@heroicons/vue/24/solid";

// Inicializa primero usePage
const page = usePage();
const permissions = page.props.auth?.permissions || [];
const { can } = page.props;
const canDo = (key) => !!can[key];

// Datos
const reportes = computed(() => page.props.userReportes || []);
const carteras = computed(() => page.props.userCarteras || []);

// Selección persistida
const getCarteraIdFromStorage = () => {
    const stored = localStorage.getItem("selectedCarteraId");
    return stored
        ? parseInt(stored)
        : carteras.value.length
        ? carteras.value[0].id
        : null;
};
const selectedCarteraId = ref(
    page.props.selectedCarteraId ?? getCarteraIdFromStorage()
);
watch(
    selectedCarteraId,
    (val) => {
        if (val) localStorage.setItem("selectedCarteraId", val);
    },
    { immediate: true }
);
const selectedCartera = computed(
    () => carteras.value.find((c) => c.id === selectedCarteraId.value) || null
);

// Sidebar estados
const isSidebarOpen = ref(false);
const mobileSidebarOpen = ref(false);
function toggleSidebar() {
    isSidebarOpen.value = !isSidebarOpen.value;
}
function toggleMobileSidebar() {
    mobileSidebarOpen.value = !mobileSidebarOpen.value;
}

// Reportes filtrados
const reportesFiltrados = computed(() => {
    if (!selectedCarteraId.value) return [];
    return reportes.value.filter(
        (r) => r.cartera_id === selectedCarteraId.value
    );
});

function seleccionarReporte(reporte) {
    router.visit(route("dashboard.reporte", reporte.id), {
        preserveState: true,
        preserveScroll: true,
    });
}

onMounted(() => {
    if (!selectedCarteraId.value && carteras.value.length > 0) {
        selectedCarteraId.value = carteras.value[0].id;
    }
    window.addEventListener("resize", () => {
        if (window.innerWidth >= 1024) {
            mobileSidebarOpen.value = false;
        }
    });
});
console.log("Carteras:", carteras.value);
console.log("Reportes:", reportes.value);
</script>

<!-- =========================
    2. TEMPLATE
========================= -->
<template>
    <div class="min-h-screen bg-gray-100 flex">
        <!-- MOBILE OVERLAY -->
        <div
            v-if="mobileSidebarOpen"
            @click="toggleMobileSidebar"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
        ></div>

        <!-- ===== SIDEBAR ===== -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 bg-white shadow-lg flex flex-col transition-transform duration-300 ease-in-out',
                mobileSidebarOpen
                    ? 'translate-x-0'
                    : '-translate-x-full lg:translate-x-0',
                isSidebarOpen && 'w-64',
                !isSidebarOpen && 'w-20',
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center justify-center p-4 border-b h-16">
                <Link :href="route('dashboard')" class="flex items-center">
                    <ApplicationLogo
                        v-if="isSidebarOpen"
                        class="block h-9 w-auto text-gray-800"
                    />
                    <ApplicationLogoMini
                        v-else
                        class="block h-9 w-auto text-gray-800"
                    />
                </Link>
            </div>

            <!-- Menu -->
            <nav class="flex-1 overflow-y-auto py-2 space-y-1">
                <button
                    @click="toggleSidebar"
                    class="w-full flex items-center px-3 py-3 hover:bg-gray-100"
                    :class="isSidebarOpen ? 'justify-start' : 'justify-center'"
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
                            :d="
                                isSidebarOpen
                                    ? 'M6 18L18 6M6 6l12 12'
                                    : 'M4 6h16M4 12h16M4 18h16'
                            "
                        />
                    </svg>
                    <span v-if="isSidebarOpen" class="ml-2 text-sm"
                        >Cerrar menú</span
                    >
                </button>

                <NavLink
                    v-if="canDo('manageCarteras')"
                    :href="route('carteras.index')"
                    :active="route().current('carteras.index')"
                    class="w-full flex items-center px-3 py-3 hover:bg-gray-100"
                    :class="isSidebarOpen ? 'justify-start' : 'justify-center'"
                >
                    <BriefcaseIcon class="h-6 w-6 text-indigo-500" />
                    <transition name="fade">
                        <span v-if="isSidebarOpen" class="ml-3 text-sm"
                            >Carteras</span
                        >
                    </transition>
                </NavLink>

                <NavLink
                    v-if="canDo('manageRoles')"
                    :href="route('roles.index')"
                    :active="route().current('roles.index')"
                    class="w-full flex items-center px-3 py-3 hover:bg-gray-100"
                    :class="isSidebarOpen ? 'justify-start' : 'justify-center'"
                >
                    <ShieldCheckIcon class="h-6 w-6 text-indigo-500" />
                    <transition name="fade">
                        <span v-if="isSidebarOpen" class="ml-3 text-sm"
                            >Roles</span
                        >
                    </transition>
                </NavLink>

                <NavLink
                    v-if="canDo('manageUsers')"
                    :href="route('users.index')"
                    :active="route().current('users.index')"
                    class="w-full flex items-center px-3 py-3 hover:bg-gray-100"
                    :class="isSidebarOpen ? 'justify-start' : 'justify-center'"
                >
                    <UsersIcon class="h-6 w-6 text-indigo-500" />
                    <transition name="fade">
                        <span v-if="isSidebarOpen" class="ml-3 text-sm"
                            >Usuarios</span
                        >
                    </transition>
                </NavLink>

                <div class="px-3">
                    <label
                        v-if="isSidebarOpen && selectedCartera"
                        class="block text-xs text-gray-500 mt-4 mb-2"
                    >
                        Reportes {{ selectedCartera.nombre }}
                    </label>
                    <ul v-if="reportesFiltrados.length" class="space-y-1">
                        <li v-for="r in reportesFiltrados" :key="r.id">
                            <button
                                @click="seleccionarReporte(r)"
                                class="w-full flex items-center px-2 py-1.5 rounded hover:bg-indigo-50"
                                :class="
                                    selectedCarteraId === r.id
                                        ? 'bg-indigo-100 font-semibold text-indigo-800'
                                        : 'text-gray-600'
                                "
                            >
                                <DocumentChartBarIcon class="h-5 w-5" />
                                <span
                                    v-if="isSidebarOpen"
                                    class="ml-2 text-sm"
                                    >{{ r.nombre }}</span
                                >
                            </button>
                        </li>
                    </ul>
                    <div v-else class="text-xs text-gray-400 px-2 py-2">
                        No hay reportes.
                    </div>
                </div>
            </nav>
        </aside>

        <!-- ===== MAIN CONTENT ===== -->
        <div
            :class="[
                'flex-1 flex flex-col overflow-hidden transition-all duration-300 ease-in-out',
                isSidebarOpen ? 'lg:pl-64' : 'lg:pl-20',
            ]"
        >
            <!-- ===== TOP BAR ===== -->
            <header class="bg-white shadow-sm z-10">
                <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16 items-center">
                        <!-- Sidebar Toggle (Mobile) -->
                        <button
                            @click="toggleMobileSidebar"
                            class="md:hidden text-gray-600 hover:text-gray-800 mr-2"
                        >
                            <svg
                                class="w-6 h-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                            </svg>
                        </button>

                        <!-- Carteras Dropdown -->
                        <Dropdown align="left" width="full">
                            <template #trigger>
                                <button
                                    class="w-full flex items-center px-3 py-2 rounded-md hover:bg-gray-100"
                                    :class="{
                                        'bg-purple-50 text-purple-600':
                                            !!selectedCartera,
                                    }"
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
                                        <span class="ml-3">
                                            {{
                                                selectedCartera?.nombre ||
                                                "Carteras"
                                            }}
                                        </span>
                                    </transition>
                                </button>
                            </template>
                            <template #content>
                                <button
                                    v-for="cartera in carteras"
                                    :key="cartera.id"
                                    @click="selectedCarteraId = cartera.id"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                >
                                    {{ cartera.nombre }}
                                </button>
                            </template>
                        </Dropdown>

                        <div class="flex-1"></div>

                        <!-- User Menu -->
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
                                    <DropdownLink :href="route('profile.edit')"
                                        >Profile</DropdownLink
                                    >
                                    <DropdownLink
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        >Log Out</DropdownLink
                                    >
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </header>

            <!-- ===== PAGE CONTENT ===== -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 bg-gray-100">
                <div class="mx-auto">
                    <slot name="header" />
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<!-- =========================
    3. STYLE
========================= -->
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

/* Enlaces activos */
.router-link-active {
    @apply bg-purple-50 text-purple-600;
}
.router-link-active svg {
    @apply text-purple-600;
}

/* Menú colapsado */
[style*="width: 80px"] .dropdown-content {
    left: 80px;
    min-width: 200px;
}

/* Contenido ancho máximo */
.max-w-7xl {
    max-width: 100%;
}
@media (min-width: 1280px) {
    .max-w-7xl {
        max-width: 80rem;
    }
}

/* Sidebar altura completa */
.h-screen {
    height: 100vh;
}

/* Sidebar posición */
.fixed.inset-y-0 {
    top: 0;
    bottom: 0;
}
</style>
