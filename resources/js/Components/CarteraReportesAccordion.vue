<template>
    <div class="space-y-2">
        <div
            v-for="(cartera, idx) in carteras"
            :key="cartera.id"
            class="border border-indigo-200 rounded-lg bg-white shadow-sm"
        >
            <details
                class="group transition-all duration-300"
                :open="openedIndex === idx"
                @toggle="handleToggleAccordion(idx, $event)"
            >
                <summary
                    class="flex items-center justify-between cursor-pointer p-3 bg-indigo-50 hover:bg-indigo-100 rounded-t-lg transition-colors"
                >
                    <div
                        class="flex items-center gap-2 text-sm font-semibold text-indigo-700"
                    >
                        <svg
                            class="w-4 h-4 text-indigo-400"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 7h18"
                            />
                        </svg>
                        {{ cartera.nombre }}
                    </div>
                    <span
                        class="text-xs bg-indigo-100 text-indigo-700 rounded-full px-2 py-0.5"
                    >
                        {{ cartera.reportes?.length ?? 0 }} reportes
                    </span>
                </summary>
                <transition name="fade">
                <div v-show="openedIndex === idx" class="px-4 pb-3 pt-1">
                    <template v-if="cartera.reportes?.length">
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 gap-2"
                        >
                            <div
                                v-for="reporte in cartera.reportes"
                                :key="reporte.id"
                                class="flex items-center gap-2 bg-gray-50 border border-gray-100 rounded px-2 py-1"
                            >
                                <input
                                    type="checkbox"
                                    :id="'reporte-' + cartera.id + '-' + reporte.id"
                                    :checked="modelValue.some((r) => r.id === reporte.id) || props.inheritedReportes.some((r) => r.id === reporte.id)"
                                    :disabled="props.inheritedReportes.some((r) => r.id === reporte.id)"
                                    @change="handleToggle(reporte, $event.target.checked)"
                                    class="accent-indigo-600"
                                />
                                <label
                                    :for="'reporte-' + cartera.id + '-' + reporte.id"
                                    class="text-xs text-gray-700 cursor-pointer truncate max-w-[140px]"
                                    :title="reporte.nombre"
                                >
                                    {{ reporte.nombre }}
                                    <span
                                        v-if="props.inheritedReportes.some((r) => r.id === reporte.id)"
                                        class="ml-1 px-1 py-0.5 rounded bg-emerald-100 text-emerald-700 text-[10px] font-bold"
                                    >
                                        heredado
                                    </span>
                                </label>
                            </div>
                        </div>
                    </template>
                    <p v-else class="text-xs text-gray-400 italic mt-1">
                        Esta cartera no tiene reportes disponibles.
                    </p>
                </div>
                </transition>
            </details>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
const props = defineProps({
    carteras: Array,
    modelValue: Array,
    inheritedReportes: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["update:modelValue"]);
const openedIndex = ref(null);

function handleToggle(reporte, checked) {
    const id = reporte.id;
    let currentIds = Array.isArray(props.modelValue)
        ? props.modelValue.map(r => r.id)
        : [];
    // Si el reporte es heredado, no permitir modificar
    if (props.inheritedReportes.some(r => r.id === id)) return;
    if (checked) {
        if (!currentIds.includes(id)) {
            emit("update:modelValue", [...props.modelValue, reporte]);
        }
    } else {
        emit(
            "update:modelValue",
            props.modelValue.filter(r => r.id !== id)
        );
    }
}

function handleToggleAccordion(idx, event) {
    if (event.target.open) {
        openedIndex.value = idx;
    } else {
        openedIndex.value = null;
    }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
.fade-enter-to, .fade-leave-from {
    opacity: 1;
}
</style>
