<script setup>
import { computed, ref, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const page = usePage();
const reportes = computed(() => page.props.userReportes || []);
const selectedReporteId = computed(() => page.props.selectedReporteId || null);

const reporteActual = computed(() => {
    if (!selectedReporteId.value) return null;
    return reportes.value.find((r) => r.id === selectedReporteId.value) || null;
});

const isMobile = ref(false);
onMounted(() => {
    isMobile.value = window.matchMedia("(max-width: 768px)").matches;
});

const linkReporte = computed(() => {
    if (!reporteActual.value) return null;

    // Prioridad: si es móvil y tiene link_mobile -> usar ese
    if (isMobile.value && reporteActual.value.link_mobile) {
        return reporteActual.value.link_mobile;
    }

    // Si no hay link_mobile o no es móvil, usar link_desktop como fallback
    return reporteActual.value.link_desktop || null;
});
</script>
<template>
    <AuthenticatedLayout>
        <div class="w-full">
            <transition name="fade" mode="out-in">
                <iframe
                    v-if="reporteActual && linkReporte"
                    :key="reporteActual.id"
                    class="w-full h-[90vh] rounded-lg border border-indigo-100 shadow-md"
                    :src="linkReporte"
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
