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
                'bgPrincipal customMenu fixed inset-y-0 left-0 z-50 shadow-lg flex flex-col transition-transform duration-300 ease-in-out',
                mobileSidebarOpen
                    ? 'translate-x-0'
                    : '-translate-x-full lg:translate-x-0',
                isSidebarOpen ? 'w-64' : 'w-20',
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center justify-center p-4 border-b h-16">
                <Link :href="route('dashboard')" class="flex items-center">
                    <ApplicationLogo
                        v-if="isSidebarOpen"
                        class="block h-9 w-auto text-white"
                    />
                    <ApplicationLogoMini
                        v-else
                        class="block h-9 w-auto text-white"
                    />
                </Link>
            </div>

            <!-- Menu -->
            <nav class="flex-1 overflow-y-auto py-2">
                <button
                    @click="toggleSidebar"
                    class="w-full flex items-center px-3 py-3 hover:bg-white/10 transition"
                    :class="isSidebarOpen ? 'justify-start' : 'justify-center'"
                >
                    <svg
                        v-if="isSidebarOpen"
                        class="svgMenu"
                        style="width: 22px; height: 22px; color: #fff"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 512 512"
                    >
                        <path
                            fill="white"
                            d="M0 80c0-8.8 7.2-16 16-16l416 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L16 96C7.2 96 0 88.8 0 80zM64 240c0-8.8 7.2-16 16-16l416 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L80 256c-8.8 0-16-7.2-16-16zM448 400c0 8.8-7.2 16-16 16L16 416c-8.8 0-16-7.2-16-16s7.2-16 16-16l416 0c8.8 0 16 7.2 16 16z"
                        />
                    </svg>
                    <svg
                        v-else
                        class="svgMenu"
                        style="width: 22px; height: 22px; color: #fff"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 448 512"
                    >
                        <path
                            fill="white"
                            d="M0 80c0-8.8 7.2-16 16-16l416 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L16 96C7.2 96 0 88.8 0 80zM0 240c0-8.8 7.2-16 16-16l416 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L16 256c-8.8 0-16-7.2-16-16zM448 400c0 8.8-7.2 16-16 16L16 416c-8.8 0-16-7.2-16-16s7.2-16 16-16l416 0c8.8 0 16 7.2 16 16z"
                        />
                    </svg>
                    <span v-if="isSidebarOpen" class="ml-2 text-sm text-white"
                        >Cerrar menú</span
                    >
                </button>

                <div
                    class="separador separadorAdmin"
                    v-if="canDo('manageCarteras')"
                ></div>

                <NavLink
                    v-if="canDo('manageCarteras')"
                    :href="route('carteras.index')"
                    :active="route().current('carteras.index')"
                    class="w-full flex items-center px-3 py-3 hover:bg-white/10 transition"
                    :class="isSidebarOpen ? 'justify-start' : 'justify-center'"
                >
                    <svg
                        class="svgMenu"
                        style="width: 20px; height: 20px; color: #fff"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 512 512"
                    >
                        <path
                            fill="white"
                            d="M80 32C35.8 32 0 67.8 0 112L0 400c0 44.2 35.8 80 80 80l352 0c44.2 0 80-35.8 80-80l0-224c0-44.2-35.8-80-80-80L112 96c-8.8 0-16 7.2-16 16s7.2 16 16 16l320 0c26.5 0 48 21.5 48 48l0 224c0 26.5-21.5 48-48 48L80 448c-26.5 0-48-21.5-48-48l0-288c0-26.5 21.5-48 48-48l384 0c8.8 0 16-7.2 16-16s-7.2-16-16-16L80 32zM384 312a24 24 0 1 0 0-48 24 24 0 1 0 0 48z"
                        />
                    </svg>
                    <transition name="fade">
                        <span
                            v-if="isSidebarOpen"
                            class="ml-3 text-sm text-white"
                            >Gestión de Carteras</span
                        >
                    </transition>
                </NavLink>

                <NavLink
                    v-if="canDo('manageRoles')"
                    :href="route('roles.index')"
                    :active="route().current('roles.index')"
                    class="w-full flex items-center px-3 py-3 hover:bg-white/10 transition"
                    :class="isSidebarOpen ? 'justify-start' : 'justify-center'"
                >
                    <svg
                        class="svgMenu"
                        style="width: 20px; height: 20px; color: #fff"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 640 512"
                    >
                        <path
                            fill="white"
                            d="M544 32L96 32C78.3 32 64 46.3 64 64l0 165.5c-11.9 4.2-22.8 10.7-32 19L32 64C32 28.7 60.7 0 96 0L544 0c35.3 0 64 28.7 64 64l0 184.4c-9.2-8.3-20.1-14.8-32-19L576 64c0-17.7-14.3-32-32-32zM96 352a32 32 0 1 0 0-64 32 32 0 1 0 0 64zm0-96a64 64 0 1 1 0 128 64 64 0 1 1 0-128zm224 96a32 32 0 1 0 0-64 32 32 0 1 0 0 64zm0-96a64 64 0 1 1 0 128 64 64 0 1 1 0-128zm256 64a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zm-96 0a64 64 0 1 1 128 0 64 64 0 1 1 -128 0zM32 480l0 16c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-16c0-35.3 28.7-64 64-64l64 0c35.3 0 64 28.7 64 64l0 16c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-16c0-17.7-14.3-32-32-32l-64 0c-17.7 0-32 14.3-32 32zm256-32c-17.7 0-32 14.3-32 32l0 16c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-16c0-35.3 28.7-64 64-64l64 0c35.3 0 64 28.7 64 64l0 16c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-16c0-17.7-14.3-32-32-32l-64 0zm192 32l0 16c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-16c0-35.3 28.7-64 64-64l64 0c35.3 0 64 28.7 64 64l0 16c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-16c0-17.7-14.3-32-32-32l-64 0c-17.7 0-32 14.3-32 32z"
                        />
                    </svg>
                    <transition name="fade">
                        <span
                            v-if="isSidebarOpen"
                            class="ml-3 text-sm text-white"
                        >
                            Asignación de Roles
                        </span>
                    </transition>
                </NavLink>

                <NavLink
                    v-if="canDo('manageUsers')"
                    :href="route('users.index')"
                    :active="route().current('users.index')"
                    class="w-full flex items-center px-3 py-3 hover:bg-white/10 transition"
                    :class="isSidebarOpen ? 'justify-start' : 'justify-center'"
                >
                    <svg
                        class="svgMenu"
                        style="width: 20px; height: 20px; color: #fff"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 512 512"
                    >
                        <path
                            fill="white"
                            d="M64 64C46.3 64 32 78.3 32 96l0 320c0 17.7 14.3 32 32 32l384 0c17.7 0 32-14.3 32-32l0-320c0-17.7-14.3-32-32-32L64 64zM0 96C0 60.7 28.7 32 64 32l384 0c35.3 0 64 28.7 64 64l0 320c0 35.3-28.7 64-64 64L64 480c-35.3 0-64-28.7-64-64L0 96zm288 96a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zm-96 0a64 64 0 1 1 128 0 64 64 0 1 1 -128 0zM299.4 320l-86.9 0c-18.6 0-34 14-36.3 32l159.4 0c-2.2-18-17.6-32-36.3-32zm-86.9-32l43.4 0 43.4 0c37.9 0 68.6 30.7 68.6 68.6c0 15.1-12.3 27.4-27.4 27.4l-169.1 0c-15.1 0-27.4-12.3-27.4-27.4c0-37.9 30.7-68.6 68.6-68.6z"
                        />
                    </svg>
                    <transition name="fade">
                        <span
                            v-if="isSidebarOpen"
                            class="ml-3 text-sm text-white"
                            >Gestión de Usuarios</span
                        >
                    </transition>
                </NavLink>

                <div class="separador"></div>

                <div>
                    <label
                        v-if="isSidebarOpen && selectedCartera"
                        class="block text-xs text-white mt-4 mb-2"
                    >
                        Reportes de {{ selectedCartera.nombre }}
                    </label>
                    <ul v-if="reportesFiltrados.length">
                        <li v-for="r in reportesFiltrados" :key="r.id">
                            <div class="relative group">
                                <button
                                    @click="seleccionarReporte(r)"
                                    class="w-full flex items-center px-3 py-3 transition hover:bg-white/10 focus:outline-none"
                                    :class="[
                                        isSidebarOpen
                                            ? 'justify-start'
                                            : 'justify-center',
                                        selectedCarteraId === r.id
                                            ? 'bg-white/20'
                                            : 'text-white',
                                    ]"
                                >
                                    <svg
                                        class="svgMenu"
                                        style="
                                            width: 18px;
                                            height: 18px;
                                            color: #fff;
                                        "
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 576 512"
                                    >
                                        <path
                                            fill="white"
                                            d="M320 480c17.7 0 32-14.3 32-32l0-10.7 23.8-5.9c2.8-.7 5.6-1.6 8.2-2.7l0 19.3c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 64C0 28.7 28.7 0 64 0L220.1 0c12.7 0 24.9 5.1 33.9 14.1L369.9 129.9c9 9 14.1 21.2 14.1 33.9l0 39.8-32 32 0-43.6-112 0c-26.5 0-48-21.5-48-48l0-112L64 32C46.3 32 32 46.3 32 64l0 384c0 17.7 14.3 32 32 32l256 0zM240 160l111.5 0c-.7-2.8-2.1-5.4-4.2-7.4L231.4 36.7c-2.1-2.1-4.6-3.5-7.4-4.2L224 144c0 8.8 7.2 16 16 16zM144 349l-9.8 32.8c-6.1 20.3-24.8 34.2-46 34.2L80 416c-8.8 0-16-7.2-16-16s7.2-16 16-16l8.2 0c7.1 0 13.3-4.6 15.3-11.4l14.9-49.5c3.4-11.3 13.8-19.1 25.6-19.1s22.2 7.7 25.6 19.1l12.6 42.1c7.1-8.3 17.5-13.1 28.5-13.1c14.2 0 27.2 8 33.5 20.7l5.6 11.3 41.7 0 15.7-62.6c2.1-8.4 6.5-16.1 12.6-22.3L473.5 145.4c18.7-18.7 49.1-18.7 67.9 0l17.4 17.4c18.7 18.7 18.7 49.1 0 67.9L405.1 384.3c-6.2 6.2-13.9 10.5-22.3 12.6l-74.9 18.7c-2 .5-4.1 .6-6.1 .3L240 416c-6.1 0-11.6-3.4-14.3-8.8L215.6 387c-.9-1.8-2.8-3-4.9-3c-1.7 0-3.3 .8-4.4 2.2l-17.6 23.4c-3.6 4.8-9.7 7.2-15.6 6.2s-10.8-5.4-12.5-11.2L144 349zM518.8 168c-6.2-6.2-16.4-6.2-22.6 0l-24.8 24.8 40 40L536.2 208c6.2-6.2 6.2-16.4 0-22.6L518.8 168zM342.5 321.7c-2.1 2.1-3.5 4.6-4.2 7.4l-12.3 49 49-12.3c2.8-.7 5.4-2.2 7.4-4.2L488.7 255.4l-40-40L342.5 321.7z"
                                        />
                                    </svg>
                                    <transition name="fade">
                                        <span
                                            v-if="isSidebarOpen"
                                            class="ml-3 text-sm text-white truncate"
                                            :title="r.nombre"
                                        >
                                            {{ r.nombre }}
                                        </span>
                                    </transition>
                                </button>
                                <!-- Tooltip solo en desktop -->
                                <div
                                    class="invisible absolute opacity-0 transition-opacity duration-300 group-hover:visible group-hover:opacity-100 left-full top-1/2 z-50"
                                    style="
                                        pointer-events: none;
                                        transform: translateY(-50%)
                                            translateX(10px);
                                    "
                                >
                                    <div class="relative flex items-center">
                                        <!-- Burbuja del mensaje -->
                                        <div
                                            class="text-gray-800 whitespace-nowrap rounded-lg px-4 py-2 text-xs shadow-lg border border-gray-200"
                                            style="
                                                min-width: max-content;
                                                background: rgb(95, 97, 255);
                                            "
                                        >
                                            {{ r.nombre }}
                                        </div>
                                        <!-- Flecha tipo mensaje -->
                                        <span
                                            class="absolute left-0 -translate-x-full top-1/2 -mt-2 w-0 h-0"
                                            style="
                                                border-top: 8px solid
                                                    transparent;
                                                border-bottom: 8px solid
                                                    transparent;
                                                border-right: 10px solid
                                                    rgb(95, 97, 255);
                                                filter: drop-shadow(
                                                    0 1px 2px
                                                        rgba(0, 0, 0, 0.08)
                                                );
                                                left: 3px;
                                            "
                                        ></span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div v-else class="text-xs text-white/70 px-2 py-2">
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
                            class="md:hidden text-white hover:colorPrincipal mr-2"
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
                                    class="w-full flex items-center px-3 py-2 rounded-md"
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
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700"
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
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
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
                                        <div class="flex items-center">
                                            <svg
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    clip-rule="evenodd"
                                                    d="M10.4858 3.5L13.5182 3.5C13.9233 3.5 14.2518 3.82851 14.2518 4.23377C14.2518 5.9529 16.1129 7.02795 17.602 6.1682C17.9528 5.96567 18.4014 6.08586 18.6039 6.43667L20.1203 9.0631C20.3229 9.41407 20.2027 9.86286 19.8517 10.0655C18.3625 10.9253 18.3625 13.0747 19.8517 13.9345C20.2026 14.1372 20.3229 14.5859 20.1203 14.9369L18.6039 17.5634C18.4013 17.9142 17.9528 18.0344 17.602 17.8318C16.1129 16.9721 14.2518 18.0471 14.2518 19.7663C14.2518 20.1715 13.9233 20.5 13.5182 20.5H10.4858C10.0804 20.5 9.75182 20.1714 9.75182 19.766C9.75182 18.0461 7.88983 16.9717 6.40067 17.8314C6.04945 18.0342 5.60037 17.9139 5.39767 17.5628L3.88167 14.937C3.67903 14.586 3.79928 14.1372 4.15026 13.9346C5.63949 13.0748 5.63946 10.9253 4.15025 10.0655C3.79926 9.86282 3.67901 9.41401 3.88165 9.06303L5.39764 6.43725C5.60034 6.08617 6.04943 5.96581 6.40065 6.16858C7.88982 7.02836 9.75182 5.9539 9.75182 4.23399C9.75182 3.82862 10.0804 3.5 10.4858 3.5ZM13.5182 2L10.4858 2C9.25201 2 8.25182 3.00019 8.25182 4.23399C8.25182 4.79884 7.64013 5.15215 7.15065 4.86955C6.08213 4.25263 4.71559 4.61859 4.0986 5.68725L2.58261 8.31303C1.96575 9.38146 2.33183 10.7477 3.40025 11.3645C3.88948 11.647 3.88947 12.3531 3.40026 12.6355C2.33184 13.2524 1.96578 14.6186 2.58263 15.687L4.09863 18.3128C4.71562 19.3814 6.08215 19.7474 7.15067 19.1305C7.64015 18.8479 8.25182 19.2012 8.25182 19.766C8.25182 20.9998 9.25201 22 10.4858 22H13.5182C14.7519 22 15.7518 20.9998 15.7518 19.7663C15.7518 19.2015 16.3632 18.8487 16.852 19.1309C17.9202 19.7476 19.2862 19.3816 19.9029 18.3134L21.4193 15.6869C22.0361 14.6185 21.6701 13.2523 20.6017 12.6355C20.1125 12.3531 20.1125 11.647 20.6017 11.3645C21.6701 10.7477 22.0362 9.38152 21.4193 8.3131L19.903 5.68667C19.2862 4.61842 17.9202 4.25241 16.852 4.86917C16.3632 5.15138 15.7518 4.79856 15.7518 4.23377C15.7518 3.00024 14.7519 2 13.5182 2ZM9.6659 11.9999C9.6659 10.7103 10.7113 9.66493 12.0009 9.66493C13.2905 9.66493 14.3359 10.7103 14.3359 11.9999C14.3359 13.2895 13.2905 14.3349 12.0009 14.3349C10.7113 14.3349 9.6659 13.2895 9.6659 11.9999ZM12.0009 8.16493C9.88289 8.16493 8.1659 9.88191 8.1659 11.9999C8.1659 14.1179 9.88289 15.8349 12.0009 15.8349C14.1189 15.8349 15.8359 14.1179 15.8359 11.9999C15.8359 9.88191 14.1189 8.16493 12.0009 8.16493Z"
                                                    fill="currentColor"
                                                ></path>
                                            </svg>
                                            <span style="margin-left: 10px"
                                                >Perfil</span
                                            >
                                        </div>
                                    </DropdownLink>

                                    <DropdownLink
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                    >
                                        <div class="flex items-center">
                                            <svg
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    clip-rule="evenodd"
                                                    d="M15.1007 19.247C14.6865 19.247 14.3507 18.9112 14.3507 18.497L14.3507 14.245H12.8507V18.497C12.8507 19.7396 13.8581 20.747 15.1007 20.747H18.5007C19.7434 20.747 20.7507 19.7396 20.7507 18.497L20.7507 5.49609C20.7507 4.25345 19.7433 3.24609 18.5007 3.24609H15.1007C13.8581 3.24609 12.8507 4.25345 12.8507 5.49609V9.74501L14.3507 9.74501V5.49609C14.3507 5.08188 14.6865 4.74609 15.1007 4.74609L18.5007 4.74609C18.9149 4.74609 19.2507 5.08188 19.2507 5.49609L19.2507 18.497C19.2507 18.9112 18.9149 19.247 18.5007 19.247H15.1007ZM3.25073 11.9984C3.25073 12.2144 3.34204 12.4091 3.48817 12.546L8.09483 17.1556C8.38763 17.4485 8.86251 17.4487 9.15549 17.1559C9.44848 16.8631 9.44863 16.3882 9.15583 16.0952L5.81116 12.7484L16.0007 12.7484C16.4149 12.7484 16.7507 12.4127 16.7507 11.9984C16.7507 11.5842 16.4149 11.2484 16.0007 11.2484L5.81528 11.2484L9.15585 7.90554C9.44864 7.61255 9.44847 7.13767 9.15547 6.84488C8.86248 6.55209 8.3876 6.55226 8.09481 6.84525L3.52309 11.4202C3.35673 11.5577 3.25073 11.7657 3.25073 11.9984Z"
                                                    fill="currentColor"
                                                ></path>
                                            </svg>
                                            <span style="margin-left: 10px"
                                                >Cerrar Sesión</span
                                            >
                                        </div>
                                    </DropdownLink>
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
.bgPrincipal {
    background: rgb(95, 97, 255);
}

.svgMenu {
    display: inline-block;
    vertical-align: middle;
}
@media (max-width: 1023px) {
    /* Oculta el tooltip en mobile */
    .group:hover .group-hover\:visible {
        visibility: hidden !important;
        opacity: 0 !important;
    }
}
</style>
