<script setup>
import { computed, ref, onMounted } from "vue";
import { usePage, Head } from "@inertiajs/vue3";
// import { useMotion } from "@vueuse/motion";
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
    if (isMobile.value && reporteActual.value.link_mobile) {
        return reporteActual.value.link_mobile;
    }
    return reporteActual.value.link_desktop || null;
});
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <div class="w-full">
            <transition name="fade" mode="out-in">
                <template v-if="reporteActual && linkReporte">
                    <!-- Si hay reporte, muestra el iframe -->
                    <iframe
                        :key="reporteActual.id"
                        class="w-full h-[90vh] rounded-lg border border-indigo-100 shadow-md"
                        :src="linkReporte"
                        frameborder="0"
                        allowfullscreen
                        sandbox="allow-scripts allow-same-origin allow-popups"
                    ></iframe>
                </template>
                <template v-else>
                    <!-- Si no hay reporte, muestra la bienvenida con Motion stagger -->
                    <div
                        key="sin-reporte"
                        class="inset-0 flex flex-col items-center rounded-lg border border-indigo-100 justify-center w-full h-[90vh] transition-all duration-500 bg-gradient-to-br from-indigo-100 via-white to-blue-200 overflow-hidden z-50"
                    >
                        <div
                            class="flex flex-col items-center justify-center w-full h-full relative"
                        >
                            <h2
                                v-motion="{
                                    initial: { opacity: 0, y: 60, scale: 0.8 },
                                    enter: {
                                        opacity: 1,
                                        y: 0,
                                        scale: 1,
                                        transition: {
                                            delay: 0.1,
                                            type: 'spring',
                                            stiffness: 180,
                                        },
                                    },
                                }"
                                class="text-5xl md:text-5xl font-extrabold text-indigo-700 tracking-tight mb-6 text-center drop-shadow-lg"
                            >
                                ¡Bienvenido!
                            </h2>
                            <p
                                v-motion="{
                                    initial: { opacity: 0, y: 40 },
                                    enter: {
                                        opacity: 1,
                                        y: 0,
                                        transition: {
                                            delay: 0.35,
                                            type: 'spring',
                                            stiffness: 120,
                                        },
                                    },
                                }"
                                class="text-xl md:text-1xl text-gray-700 max-w-3xl mx-auto font-light text-center"
                            >
                                Visualiza, explora y analiza tus
                                <span class="text-indigo-500 font-semibold"
                                    >reportes empresariales</span
                                >
                                en un solo lugar.<br />
                                Selecciona un reporte del menú lateral para
                                comenzar.
                            </p>
                            <div
                                v-motion="{
                                    initial: { opacity: 0, scale: 0.8 },
                                    enter: {
                                        opacity: 1,
                                        scale: 1,
                                        transition: {
                                            delay: 0.65,
                                            type: 'spring',
                                            stiffness: 120,
                                        },
                                    },
                                }"
                                class="relative flex justify-center w-full mt-12"
                            >
                                <lottie-player
                                    id="analyticsLottie"
                                    src="/animation/analytics.json"
                                    background="transparent"
                                    speed="1"
                                    style="
                                        width: 100%;
                                        max-width: 1000px;
                                        height: 520px;
                                    "
                                    class="mx-auto z-10 drop-shadow-2xl"
                                ></lottie-player>
                            </div>
                            <div
                                v-motion="{
                                    initial: { opacity: 0, y: 30, scale: 0.9 },
                                    enter: {
                                        opacity: 1,
                                        y: 0,
                                        scale: 1,
                                        transition: {
                                            delay: 1,
                                            type: 'spring',
                                            stiffness: 120,
                                        },
                                    },
                                }"
                                class="mt-14 flex flex-col items-center gap-2"
                            >
                                <span class="text-xs text-gray-400 mt-2"
                                    >Desarrollado por IA-Genius · 2025</span
                                >
                            </div>
                        </div>
                    </div>
                </template>
            </transition>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes fade-in {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}

@keyframes bounce-in {
    0% {
        opacity: 0;
        transform: scale(0.7) translateY(60px);
    }
    60% {
        opacity: 1;
        transform: scale(1.05) translateY(-10px);
    }
    80% {
        transform: scale(0.98) translateY(4px);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@keyframes fade-slide-in {
    0% {
        opacity: 0;
        transform: translateY(40px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes zoom-in-bounce {
    0% {
        opacity: 0;
        transform: scale(0.7);
    }
    60% {
        opacity: 1;
        transform: scale(1.08);
    }
    80% {
        transform: scale(0.97);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes pop-in {
    0% {
        opacity: 0;
        transform: scale(0.7);
    }
    80% {
        opacity: 1;
        transform: scale(1.1);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-fade-in {
    animation: fade-in 0.6s ease-out both;
}
.animate-bounce-in {
    animation: bounce-in 1s cubic-bezier(0.23, 1, 0.32, 1) both;
}
.animate-fade-slide-in {
    animation: fade-slide-in 0.9s cubic-bezier(0.23, 1, 0.32, 1) both;
}
.animate-zoom-in-bounce {
    animation: zoom-in-bounce 1.1s cubic-bezier(0.23, 1, 0.32, 1) both;
}
.animate-pop-in {
    animation: pop-in 0.7s cubic-bezier(0.23, 1, 0.32, 1) both;
}
</style>
