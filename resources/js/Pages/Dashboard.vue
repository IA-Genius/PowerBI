<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const page = usePage();
const reportes = computed(() => page.props.userReportes || []);
const selectedReporteId = computed(() => page.props.selectedReporteId || null);

const reporteActual = computed(() => {
    if (!selectedReporteId.value) return null;
    return reportes.value.find((r) => r.id === selectedReporteId.value) || null;
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="w-full">
            <transition name="fade" mode="out-in">
                <iframe
                    v-if="reporteActual"
                    :key="reporteActual.id"
                    class="w-full h-[700px] rounded-lg border border-indigo-100 shadow-md"
                    :src="reporteActual.link"
                    frameborder="0"
                    allowfullscreen
                    sandbox="allow-scripts allow-same-origin allow-popups"
                ></iframe>
                <div
                    v-else
                    key="sin-reporte"
                    class="text-center text-gray-400 py-12"
                >
                    <p class="text-lg font-medium">
                        Selecciona un reporte para visualizar
                    </p>
                </div>
            </transition>
        </div>
    </AuthenticatedLayout>
</template>
