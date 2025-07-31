<template>
    <div
        class="modalFiltros fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
    >
        <div class="absolute inset-0" @click="cerrarModal"></div>
        <div
            class="relative bg-white shadow-2xl border border-gray-200 rounded-2xl w-full max-w-2xl mx-auto overflow-hidden animate__animated animate__fadeIn"
        >
            <!-- Encabezado con búsqueda -->
            <div
                class="flex items-center gap-3 bg-gray-50 border-b border-gray-200 px-4 py-3"
            >
                <svg
                    class="w-5 h-5 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
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

            <!-- Filtros de fecha -->
            <div
                v-if="mostrarFiltrosFecha"
                class="flex flex-wrap gap-4 px-5 py-4 bg-white border-b border-gray-100 justify-center items-center"
            >
                <div
                    class="flex items-center gap-2 bg-indigo-50 rounded-xl px-4 py-2"
                >
                    <span class="text-xs font-semibold text-indigo-600"
                        >Desde:</span
                    >
                    <input
                        type="date"
                        v-model="fechaDesde"
                        class="flex-1 bg-transparent border-none text-sm text-gray-900 placeholder-gray-400 px-3 focus:outline-none focus:ring-0"
                    />
                </div>
                <div
                    class="flex items-center gap-2 bg-indigo-50 rounded-xl px-4 py-2"
                >
                    <span class="text-xs font-semibold text-indigo-600"
                        >Hasta:</span
                    >
                    <input
                        type="date"
                        v-model="fechaHasta"
                        class="flex-1 bg-transparent border-none text-sm text-gray-900 placeholder-gray-400 px-3 focus:outline-none focus:ring-0"
                    />
                </div>
            </div>

            <!-- Filtros dinámicos -->
            <div
                class="px-5 py-6 space-y-5 max-h-72 overflow-y-auto bg-gray-50"
            >
                <div
                    v-for="(options, key) in filtros"
                    :key="key"
                    class="space-y-2"
                >
                    <h4
                        class="text-xs font-medium text-gray-500 capitalize tracking-wide flex items-center gap-1"
                    >
                        {{ key.replace(/_/g, " ") }}
                    </h4>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="opt in options"
                            :key="opt"
                            @click="toggleFiltro(key, opt)"
                            :class="[
                                'px-3 py-1 rounded-full text-xs font-medium cursor-pointer transition flex items-center gap-1',
                                (selectedFiltros[key] || []).includes(opt)
                                    ? 'bg-indigo-600 text-white shadow-sm'
                                    : 'bg-white text-gray-700 border border-gray-300 hover:bg-indigo-50',
                            ]"
                        >
                            <!-- Check solo si la opción está aplicada (en originalFiltros) -->
                            <svg
                                v-if="
                                    (originalFiltros[key] || []).includes(
                                        opt
                                    ) &&
                                    (selectedFiltros[key] || []).includes(opt)
                                "
                                class="w-3 h-3 text-green-400 mr-1"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="3"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                            {{ opt }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer con acciones -->
            <div
                class="flex justify-between items-center px-5 py-4 border-t bg-white"
            >
                <button
                    @click="limpiarFiltros"
                    class="flex items-center justify-center gap-2 px-6 py-2 text-sm font-semibold text-white bg-orange-500 hover:bg-orange-600 rounded-lg shadow-sm transition"
                    title="Limpiar filtros"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 640 640"
                    >
                        <path
                            fill="currentColor"
                            d="M601 73C610.4 63.6 610.4 48.4 601 39.1C591.6 29.8 576.4 29.7 567.1 39.1L367.1 239.1L354.2 226.2C334 206 302.8 201.6 277.9 215.5L48.4 342.9C38.3 348.5 32 359.2 32 370.8C32 379.2 35.4 387.4 41.3 393.3L246.7 598.7C252.7 604.7 260.8 608 269.3 608C280.9 608 291.6 601.7 297.2 591.6L424.6 362.2C438.5 337.2 434.1 306.1 413.9 285.9L401 273L601 73zM320.2 260.1L379.9 319.8C385 324.9 386 332.6 382.6 338.9L367.7 365.7L274.3 272.3L301.1 257.4C307.3 253.9 315.1 255 320.2 260.1zM230.6 296.6L343.4 409.4L265.5 549.7L169 453.2L187 399.3C189.1 393 183.1 387.1 176.9 389.2L123 407.2L90.5 374.7L230.8 296.8z"
                        />
                    </svg>
                </button>
                <button
                    @click="aplicarFiltros"
                    class="ml-auto flex items-center justify-center px-6 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from "vue";
import Swal from "sweetalert2";

const props = defineProps({
    filtros: { type: Object, required: true },
    placeholder: { type: String, default: "Buscar..." },
    selected: { type: Object, default: () => ({}) },
    fechaDesdeProp: { type: String, default: "" },
    fechaHastaProp: { type: String, default: "" },
    mostrarFiltrosFecha: { type: Boolean, default: true },
});
const emit = defineEmits(["filtrar", "close", "search"]);

const internalSearch = ref("");
const selectedFiltros = ref({});
const fechaDesde = ref(props.fechaDesdeProp);
const fechaHasta = ref(props.fechaHastaProp);

// Optimización: Debounce para búsqueda
let searchTimeout = null;

// Para advertencia de cambios sin aplicar
const originalFiltros = ref({});
const originalFechaDesde = ref("");
const originalFechaHasta = ref("");

// Al abrir el modal, guarda el estado original
onMounted(() => {
    selectedFiltros.value = JSON.parse(JSON.stringify(props.selected || {}));
    originalFiltros.value = JSON.parse(JSON.stringify(props.selected || {}));
    if (props.fechaDesdeProp) fechaDesde.value = props.fechaDesdeProp;
    if (props.fechaHastaProp) fechaHasta.value = props.fechaHastaProp;
    originalFechaDesde.value = props.fechaDesdeProp;
    originalFechaHasta.value = props.fechaHastaProp;
});

// Watch para reinicializar la copia local cuando cambian los props (optimizado)
watch(
    () => props.selected,
    (nuevo, anterior) => {
        // Evitar comparaciones costosas si no hay cambios reales
        if (nuevo === anterior) return;

        // Solo actualizar si cambió algo más que la búsqueda
        const nuevoSinSearch = { ...nuevo };
        const anteriorSinSearch = { ...anterior };
        delete nuevoSinSearch.search;
        delete anteriorSinSearch.search;

        // Usar una comparación más eficiente
        const nuevoStringified = JSON.stringify(nuevoSinSearch);
        const anteriorStringified = JSON.stringify(anteriorSinSearch);

        // Si solo cambió la búsqueda, no sobrescribir selectedFiltros
        if (nuevoStringified !== anteriorStringified) {
            selectedFiltros.value = JSON.parse(nuevoStringified);
            originalFiltros.value = JSON.parse(nuevoStringified);
        }
    },
    { deep: false } // Evitar deep watching para mejor rendimiento
);

// Search en tiempo real con debounce optimizado
const debouncedSearch = (searchValue) => {
    if (searchTimeout) clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        emit("search", searchValue);
    }, 300); // 300ms de delay para evitar demasiadas emisiones
};

watch(internalSearch, (val) => {
    debouncedSearch(val);
});

// Cleanup al desmontar
onUnmounted(() => {
    if (searchTimeout) clearTimeout(searchTimeout);
});

function toggleFiltro(key, value) {
    if (!selectedFiltros.value[key]) selectedFiltros.value[key] = [];
    const index = selectedFiltros.value[key].indexOf(value);
    if (index > -1) {
        selectedFiltros.value[key].splice(index, 1);
    } else {
        selectedFiltros.value[key].push(value);
    }
}

function limpiarFiltros() {
    internalSearch.value = "";
    Object.keys(selectedFiltros.value).forEach(
        (k) => (selectedFiltros.value[k] = [])
    );

    const hoy = new Date();
    const ayer = new Date();
    ayer.setDate(hoy.getDate() - 1);
    const pad = (n) => n.toString().padStart(2, "0");
    const format = (d) =>
        `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    fechaDesde.value = format(ayer);
    fechaHasta.value = format(hoy);

    if (selectedFiltros.value.trazabilidad !== undefined) {
        selectedFiltros.value.trazabilidad = ["pendiente"];
    }
    aplicarFiltros();
}
function aplicarFiltros() {
    emit("filtrar", {
        ...selectedFiltros.value,
        ...(fechaDesde.value ? { fecha_desde: fechaDesde.value } : {}),
        ...(fechaHasta.value ? { fecha_hasta: fechaHasta.value } : {}),
    });
    // Actualiza los originales para que no advierta después de aplicar
    originalFiltros.value = JSON.parse(JSON.stringify(selectedFiltros.value));
    originalFechaDesde.value = fechaDesde.value;
    originalFechaHasta.value = fechaHasta.value;
    emit("close");
}

// Detecta si hay cambios sin aplicar
function hayCambiosSinAplicar() {
    return (
        JSON.stringify(selectedFiltros.value) !==
            JSON.stringify(originalFiltros.value) ||
        fechaDesde.value !== originalFechaDesde.value ||
        fechaHasta.value !== originalFechaHasta.value
    );
}

// Al cerrar, advertir si hay cambios sin aplicar (SweetAlert)
function cerrarModal() {
    if (hayCambiosSinAplicar()) {
        Swal.fire({
            title: "Cambios sin aplicar",
            text: "Tienes cambios sin aplicar. ¿Deseas salir sin guardar?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Salir sin guardar",
            cancelButtonText: "Seguir editando",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                // Restaurar los filtros y fechas originales
                selectedFiltros.value = JSON.parse(
                    JSON.stringify(originalFiltros.value)
                );
                fechaDesde.value = originalFechaDesde.value;
                fechaHasta.value = originalFechaHasta.value;
                emit("close");
            }
        });
    } else {
        emit("close");
    }
}
</script>
