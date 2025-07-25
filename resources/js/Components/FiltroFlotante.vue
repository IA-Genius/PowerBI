<template>
    <div class="modalFiltros">
        <div class="fdClose" @click="$emit('close')"></div>

        <div
            class="modalContent bg-white shadow-lg border border-gray-200 rounded-xl w-[40rem] overflow-hidden"
        >
            <!-- Encabezado con input -->
            <div
                class="flex items-center bg-[#f9fafb] border-b border-gray-200 px-4 py-3"
            >
                <!-- Icono lupa -->
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z"
                    />
                </svg>

                <input
                    v-model="internalSearch"
                    type="text"
                    :placeholder="placeholder"
                    class="flex-1 bg-transparent border-none text-sm text-gray-900 placeholder-gray-400 px-3 focus:outline-none focus:ring-0"
                />

                <button
                    v-if="internalSearch"
                    @click="internalSearch = ''"
                    class="text-gray-400 hover:text-red-500 transition"
                    title="Limpiar búsqueda"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <!-- Cuerpo con filtros -->
            <div class="px-5 py-5 space-y-5">
                <div
                    v-for="(options, key) in filtros"
                    :key="key"
                    class="space-y-1"
                >
                    <h4
                        class="text-xs font-medium text-gray-500 capitalize tracking-wide"
                    >
                        {{ key.replace(/_/g, " ") }}
                    </h4>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="opt in options"
                            :key="opt"
                            @click="toggleFiltro(key, opt)"
                            :class="[
                                'px-3 py-1 rounded-full text-xs cursor-pointer border',
                                (selectedFiltros[key] || []).includes(opt)
                                    ? 'bg-indigo-600 text-white border-indigo-600'
                                    : 'bg-gray-100 text-gray-800 hover:bg-indigo-100 transition',
                            ]"
                        >
                            {{ opt }}
                        </span>
                    </div>
                </div>

                <!-- Botón limpiar -->
                <div class="flex justify-end pt-2">
                    <button
                        @click="limpiarFiltros"
                        class="flex items-center gap-1 text-sm text-gray-500 hover:text-red-600 hover:underline transition"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                        Limpiar filtros
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, reactive } from "vue";

const props = defineProps({
    filtros: { type: Object, required: true },
    placeholder: { type: String, default: "Buscar..." },
});
const emit = defineEmits(["filtrar", "close"]);

const internalSearch = ref("");
const selectedFiltros = reactive({});

function toggleFiltro(key, value) {
    if (!selectedFiltros[key]) selectedFiltros[key] = [];

    const index = selectedFiltros[key].indexOf(value);
    if (index > -1) {
        selectedFiltros[key].splice(index, 1);
    } else {
        selectedFiltros[key].push(value);
    }

    emitirFiltros();
}

function limpiarFiltros() {
    internalSearch.value = "";
    Object.keys(selectedFiltros).forEach((k) => (selectedFiltros[k] = []));
    emitirFiltros();
}

function emitirFiltros() {
    emit("filtrar", {
        search: internalSearch.value,
        ...selectedFiltros,
    });
}

watch(internalSearch, emitirFiltros);
</script>
