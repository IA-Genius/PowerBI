<script setup>
import { ref, computed, watch } from "vue";

const props = defineProps({
    reportes: { type: Array, required: true },
    carteras: { type: Array, required: true },
});

const emit = defineEmits(["update:selectedReporte"]);

const tabsContainer = ref(null);

const scrollLeft = () => {
    tabsContainer.value?.scrollBy({ left: -150, behavior: "smooth" });
};

const scrollRight = () => {
    tabsContainer.value?.scrollBy({ left: 150, behavior: "smooth" });
};

const selectedCartera = ref(props.carteras[0] || null);
const filteredReportes = computed(() => {
    if (!selectedCartera.value) return [];
    return props.reportes.filter(
        (r) => r.cartera && r.cartera.id === selectedCartera.value.id
    );
});
const selectedReporte = ref(filteredReportes.value[0] || null);

watch(selectedCartera, () => {
    selectedReporte.value = filteredReportes.value[0] || null;
    emit("update:selectedReporte", selectedReporte.value);
});

watch(filteredReportes, (nuevos) => {
    if (!nuevos.includes(selectedReporte.value)) {
        selectedReporte.value = nuevos[0] || null;
        emit("update:selectedReporte", selectedReporte.value);
    }
});

watch(selectedReporte, (nuevo) => {
    emit("update:selectedReporte", nuevo);
});
</script>

<template>
    <div>
        <!-- Dropdown de carteras -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-4">
            <label class="font-semibold text-indigo-700">
                Selecciona una cartera:
            </label>
            <select
                v-model="selectedCartera"
                class="p-2 border border-indigo-200 rounded-md focus:ring-2 focus:ring-indigo-400 focus:outline-none shadow-sm min-w-[200px]"
            >
                <option
                    v-for="cartera in props.carteras"
                    :key="cartera.id"
                    :value="cartera"
                >
                    {{ cartera.nombre }}
                </option>
            </select>
        </div>

        <!-- Carrusel de Tabs -->
        <div class="relative mb-4">
            <div
                class="pointer-events-none absolute inset-y-0 left-0 w-8 bg-gradient-to-r from-white to-transparent"
            ></div>
            <div
                class="pointer-events-none absolute inset-y-0 right-0 w-8 bg-gradient-to-l from-white to-transparent"
            ></div>

            <div
                ref="tabsContainer"
                :class="[
                    'flex w-full gap-2 scroll-smooth snap-x py-2 px-1 bg-indigo-50 rounded-lg border border-indigo-100 shadow-sm',
                    filteredReportes.length <= 3
                        ? 'justify-center'
                        : 'overflow-x-auto scrollbar-hide',
                ]"
            >
                <button
                    v-for="reporte in filteredReportes"
                    :key="reporte.id"
                    @click="selectedReporte = reporte"
                    :class="[
                        'px-3 py-1.5 rounded-full text-xs font-semibold uppercase transition-all duration-300 flex items-center gap-1 snap-start whitespace-nowrap focus:outline-none tracking-wide',
                        reporte.id === selectedReporte?.id
                            ? 'bg-indigo-600 text-white shadow-lg scale-105 border-2 border-indigo-700'
                            : 'bg-white text-gray-600 hover:bg-indigo-100 hover:text-indigo-700 border border-indigo-200',
                    ]"
                >
                    <span>{{ reporte.nombre }}</span>
                </button>
            </div>

            <!-- Flechas navegación -->
            <button
                @click="scrollLeft"
                class="hidden md:flex absolute left-0 top-1/2 -translate-y-1/2 -ml-8 bg-white/80 hover:bg-white shadow rounded-full p-1.5 transition-all duration-200 z-10"
                style="margin-left: -1rem"
            >
                <svg
                    class="w-4 h-4 text-indigo-500"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M12.707 14.707a1 1 0 01-1.414 0L7.586 11l3.707-3.707a1 1 0 011.414 1.414L10.414 11l2.293 2.293a1 1 0 010 1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>

            <button
                @click="scrollRight"
                class="hidden md:flex absolute right-0 top-1/2 -translate-y-1/2 -mr-8 bg-white/80 hover:bg-white shadow rounded-full p-1.5 transition-all duration-200 z-10"
                style="margin-right: -1rem"
            >
                <svg
                    class="w-4 h-4 text-indigo-500"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M7.293 5.293a1 1 0 011.414 0L12.414 9l-3.707 3.707a1 1 0 01-1.414-1.414L10.586 9 7.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>
        </div>

        <!-- Mensaje si no hay reportes -->
        <transition name="fade" mode="out-in">
            <div
                v-if="filteredReportes.length === 0"
                key="no-reporte"
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
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                </svg>
                <p class="text-lg font-medium">No hay reportes disponibles</p>
                <p class="text-sm text-gray-500 mt-2">
                    Selecciona otra cartera o revisa los permisos.
                </p>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.scrollbar-hide {
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
