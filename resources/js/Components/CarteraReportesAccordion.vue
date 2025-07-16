<template>
    <div class="space-y-2">
        <div
            v-for="cartera in carteras"
            :key="cartera.id"
            class="border border-indigo-200 rounded-lg bg-white shadow-sm"
        >
            <details class="group">
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
                <div class="px-4 pb-3 pt-1">
                    <template v-if="cartera.reportes?.length">
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2"
                        >
                            <div
                                v-for="reporte in cartera.reportes"
                                :key="reporte.id"
                                class="flex items-center gap-2 bg-gray-50 border border-gray-100 rounded px-2 py-1"
                            >
                                <input
                                    type="checkbox"
                                    :id="
                                        'reporte-' +
                                        cartera.id +
                                        '-' +
                                        reporte.id
                                    "
                                    :checked="
                                        modelValue.some(
                                            (r) => r.id === reporte.id
                                        )
                                    "
                                    @change="
                                        handleToggle(
                                            reporte,
                                            $event.target.checked
                                        )
                                    "
                                    class="accent-indigo-600"
                                />
                                <label
                                    :for="
                                        'reporte-' +
                                        cartera.id +
                                        '-' +
                                        reporte.id
                                    "
                                    class="text-xs text-gray-700 cursor-pointer truncate max-w-[140px]"
                                    :title="reporte.nombre"
                                >
                                    {{ reporte.nombre }}
                                </label>
                            </div>
                        </div>
                    </template>
                    <p v-else class="text-xs text-gray-400 italic mt-1">
                        Esta cartera no tiene reportes disponibles.
                    </p>
                </div>
            </details>
        </div>
    </div>
</template>

<script setup>
defineProps({
    carteras: Array,
    modelValue: Array,
});

const emit = defineEmits(["update:modelValue"]);

function handleToggle(reporte, checked) {
    const current = [...(modelValue || [])];
    const index = current.findIndex((r) => r.id === reporte.id);

    if (checked && index === -1) {
        current.push(reporte);
    } else if (!checked && index !== -1) {
        current.splice(index, 1);
    }

    emit("update:modelValue", current);
}
</script>
