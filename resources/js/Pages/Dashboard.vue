<script setup>
import TabsCarrusel from "@/Components/TabsCarrusel.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    reportes: Array,
    carteras: Array,
});

import { onMounted } from "vue";
const selectedReporte = ref(null);

const handleReporteSeleccionado = (reporte) => {
    selectedReporte.value = reporte;
};

onMounted(() => {
    // Selecciona el primer reporte disponible por defecto
    if (props.reportes && props.reportes.length > 0) {
        selectedReporte.value = props.reportes[0];
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6 text-gray-900"
                >
                    <TabsCarrusel
                        :carteras="carteras"
                        :reportes="reportes"
                        @update:selectedReporte="handleReporteSeleccionado"
                    />

                    <transition name="fade" mode="out-in">
                        <iframe
                            v-if="selectedReporte"
                            :key="selectedReporte.id"
                            width="100%"
                            height="700px"
                            sandbox="allow-scripts"
                            :src="selectedReporte.src"
                            frameborder="0"
                            allowfullscreen="true"
                            class="mt-6 rounded-lg border border-indigo-100 shadow-md"
                        ></iframe>

                        <div
                            v-else
                            key="no-iframe"
                            class="flex flex-col items-center justify-center text-gray-400 py-12"
                        >
                            <svg
                                class="w-20 h-20 mb-4 text-indigo-100"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 15a4 4 0 014-4h10a4 4 0 110 8H7a4 4 0 01-4-4z"
                                />
                            </svg>
                            <p class="text-lg font-medium">
                                Selecciona un reporte para visualizar
                            </p>
                        </div>
                    </transition>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
