<template>
    <div class="relative">
        <!-- Botones de selección flotantes -->
        <div class="fixed bottom-6 right-8 z-40 flex gap-2 select-none">
            <button
                @click="selectAll"
                class="modern-btn"
                title="Seleccionar todo"
            >
                <svg
                    class="w-5 h-5 text-indigo-500"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 448 512"
                >
                    <path
                        fill="currentColor"
                        d="M64 64C46.3 64 32 78.3 32 96l0 320c0 17.7 14.3 32 32 32l320 0c17.7 0 32-14.3 32-32l0-320c0-17.7-14.3-32-32-32L64 64zM0 96C0 60.7 28.7 32 64 32l320 0c35.3 0 64 28.7 64 64l0 320c0 35.3-28.7 64-64 64L64 480c-35.3 0-64-28.7-64-64L0 96zM301.6 200.5l-80 128c-2.8 4.5-7.6 7.3-12.9 7.5s-10.3-2.2-13.5-6.4l-48-64c-5.3-7.1-3.9-17.1 3.2-22.4s17.1-3.9 22.4 3.2l34 45.3 67.6-108.2c4.7-7.5 14.6-9.8 22-5.1s9.8 14.6 5.1 22z"
                    />
                </svg>
            </button>
            <button
                @click="clearSelection"
                class="modern-btn"
                title="Limpiar selección"
            >
                <svg
                    class="w-5 h-5 text-indigo-500"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 640 640"
                >
                    <path
                        fill="currentColor"
                        d="M601 73C610.4 63.6 610.4 48.4 601 39.1C591.6 29.8 576.4 29.7 567.1 39.1L367.1 239.1L354.2 226.2C334 206 302.8 201.6 277.9 215.5L48.4 342.9C38.3 348.5 32 359.2 32 370.8C32 379.2 35.4 387.4 41.3 393.3L246.7 598.7C252.7 604.7 260.8 608 269.3 608C280.9 608 291.6 601.7 297.2 591.6L424.6 362.2C438.5 337.2 434.1 306.1 413.9 285.9L401 273L601 73zM320.2 260.1L379.9 319.8C385 324.9 386 332.6 382.6 338.9L367.7 365.7L274.3 272.3L301.1 257.4C307.3 253.9 315.1 255 320.2 260.1zM230.6 296.6L343.4 409.4L265.5 549.7L169 453.2L187 399.3C189.1 393 183.1 387.1 176.9 389.2L123 407.2L90.5 374.7L230.8 296.8z"
                    />
                </svg>
            </button>
        </div>

        <!-- Vista Grid (AG-Grid) -->
        <div v-show="props.viewMode === 'grid'" class="relative">
            <!-- Grid con datos -->
            <div
                v-if="props.rows && props.rows.length > 0"
                ref="gridContainer"
                id="myGrid"
                class="ag-theme-alpine w-full rounded-lg shadow border"
                :style="{ height: gridHeightStyle.height }"
            ></div>

            <!-- Sin datos en vista grid -->
            <div
                v-else
                class="flex flex-col items-center justify-center min-h-[700px] h-full w-full text-center px-8 bg-white rounded-lg border shadow"
            >
                <div class="flex flex-col items-center space-y-4">
                    <svg
                        class="h-20 w-20 text-gray-300"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                    <div class="space-y-2">
                        <h3 class="text-xl font-semibold text-gray-600">
                            Sin registros para mostrar
                        </h3>
                        <p class="text-sm text-gray-400 max-w-md">
                            No hay datos disponibles en este momento. Intenta
                            ajustar los filtros o cargar nuevos datos.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vista de Tarjetas -->
        <div v-show="props.viewMode === 'cards'" class="relative">
            <div :style="{ minHeight: '700px' }">
                <!-- Loading state -->
                <div
                    v-if="isLoadingViewSwitch"
                    class="flex items-center justify-center min-h-[400px]"
                >
                    <div class="text-center space-y-4">
                        <div
                            class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto"
                        ></div>
                        <p class="text-sm text-gray-600">
                            Preparando vista de tarjetas...
                        </p>
                    </div>
                </div>

                <!-- Sin datos -->
                <div
                    v-else-if="!props.rows || props.rows.length === 0"
                    class="flex flex-col items-center justify-center min-h-[700px] h-full w-full text-center px-8"
                >
                    <div class="flex flex-col items-center space-y-4">
                        <svg
                            class="h-20 w-20 text-gray-300"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        <div class="space-y-2">
                            <h3 class="text-xl font-semibold text-gray-600">
                                Sin registros para mostrar
                            </h3>
                            <p class="text-sm text-gray-400 max-w-md">
                                No hay datos disponibles en este momento.
                                Intenta ajustar los filtros o cargar nuevos
                                datos.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tarjetas -->
                <div v-else class="flex gap-4">
                    <div
                        v-for="columnIndex in getColumnCount"
                        :key="columnIndex"
                        class="flex-1 min-w-0 card-column"
                    >
                        <div class="space-y-4">
                            <div
                                v-for="(item, itemIndex) in getItemsForColumn(
                                    columnIndex - 1
                                )"
                                :key="item.id"
                                @click="toggleCardSelection(item)"
                                :class="[
                                    'card bg-white rounded-xl border cursor-pointer shadow-sm w-full rounded',
                                    selectedItems.has(item.id)
                                        ? 'border-orange-200 shadow-blue-100 shadow-lg ring-1 ring-blue-200'
                                        : 'border-gray-200 hover:border-gray-300 hover:shadow-md',
                                ]"
                            >
                                <!-- Header -->
                                <div
                                    :class="[
                                        'p-4 border-b flex items-center justify-between bgPrincipal tiCardRoles rounded',
                                        selectedItems.has(item.id)
                                            ? 'bagroundSecundario'
                                            : 'bg-white border-gray-100',
                                    ]"
                                >
                                    <div
                                        class="flex items-center space-x-3 min-w-0 flex-1"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="
                                                selectedItems.has(item.id)
                                            "
                                            @click.stop="
                                                toggleCardSelection(item)
                                            "
                                            class="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                        />
                                        <div class="min-w-0 flex-1">
                                            <h3
                                                class="font-semibold text-white text-sm truncate"
                                            >
                                                {{
                                                    item.nombre_cliente ||
                                                    "Sin nombre"
                                                }}
                                            </h3>
                                            <p class="text-xs text-gray-100">
                                                ID: {{ item.id }}
                                            </p>
                                        </div>
                                    </div>
                                    <span
                                        :class="
                                            getStatusClass(item.trazabilidad)
                                        "
                                    >
                                        {{ item.trazabilidad || "—" }}
                                    </span>
                                </div>

                                <!-- Contenido -->
                                <div class="p-4 space-y-3">
                                    <div class="space-y-2">
                                        <div
                                            class="flex justify-between items-center gap-2"
                                        >
                                            <span
                                                class="text-xs text-gray-500 font-medium flex-shrink-0"
                                                >DNI:</span
                                            >
                                            <span
                                                class="text-sm font-medium text-gray-900 truncate max-w-[60%] text-right"
                                            >
                                                {{ item.dni_cliente || "—" }}
                                            </span>
                                        </div>
                                        <div
                                            class="flex justify-between items-center gap-2"
                                        >
                                            <span
                                                class="text-xs text-gray-500 font-medium flex-shrink-0"
                                                >Teléfono:</span
                                            >
                                            <span
                                                class="text-sm font-medium text-gray-900 truncate max-w-[60%] text-right"
                                            >
                                                {{
                                                    item.telefono_principal ||
                                                    "—"
                                                }}
                                            </span>
                                        </div>
                                        <div
                                            v-if="item.orden_trabajo_anterior"
                                            class="flex justify-between items-center gap-2"
                                        >
                                            <span
                                                class="text-xs text-gray-500 font-medium flex-shrink-0"
                                                >Orden:</span
                                            >
                                            <span
                                                class="text-xs text-gray-700 font-mono truncate max-w-[65%] text-right"
                                            >
                                                {{
                                                    item.orden_trabajo_anterior
                                                }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Detalles expandibles -->
                                    <details class="group details-animated">
                                        <summary
                                            class="flex items-center justify-center cursor-pointer text-xs font-medium text-blue-600 hover:text-blue-800 py-2 border-t border-gray-100 transition-colors duration-200"
                                        >
                                            <span>Ver más detalles</span>
                                            <svg
                                                class="w-3 h-3 ml-1 transform group-open:rotate-180 transition-transform duration-200 ease-out"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 9l-7 7-7-7"
                                                />
                                            </svg>
                                        </summary>

                                        <div
                                            class="details-content overflow-hidden"
                                        >
                                            <div
                                                class="details-inner mt-2 space-y-2 text-xs"
                                            >
                                                <div
                                                    v-if="item.origen_base"
                                                    class="flex justify-between items-center gap-2"
                                                >
                                                    <span
                                                        class="text-gray-500 flex-shrink-0"
                                                        >Origen:</span
                                                    >
                                                    <span
                                                        class="font-medium truncate max-w-[60%] text-right"
                                                        >{{
                                                            item.origen_base
                                                        }}</span
                                                    >
                                                </div>
                                                <div
                                                    v-if="
                                                        item.telefono_adicional
                                                    "
                                                    class="flex justify-between items-center gap-2"
                                                >
                                                    <span
                                                        class="text-gray-500 flex-shrink-0"
                                                        >Tel. Adicional:</span
                                                    >
                                                    <span
                                                        class="font-medium truncate max-w-[55%] text-right"
                                                        >{{
                                                            item.telefono_adicional
                                                        }}</span
                                                    >
                                                </div>
                                                <div
                                                    v-if="
                                                        item.correo_referencia
                                                    "
                                                    class="flex justify-between items-center gap-2"
                                                >
                                                    <span
                                                        class="text-gray-500 flex-shrink-0"
                                                        >Email:</span
                                                    >
                                                    <span
                                                        class="font-medium truncate max-w-[65%] text-right"
                                                        >{{
                                                            item.correo_referencia
                                                        }}</span
                                                    >
                                                </div>
                                                <div
                                                    v-if="item.marca_base"
                                                    class="flex justify-between items-center gap-2"
                                                >
                                                    <span
                                                        class="text-gray-500 flex-shrink-0"
                                                        >Marca:</span
                                                    >
                                                    <span
                                                        class="font-medium truncate max-w-[60%] text-right"
                                                        >{{
                                                            item.marca_base
                                                        }}</span
                                                    >
                                                </div>
                                                <div
                                                    v-if="
                                                        item.direccion_historico
                                                    "
                                                    class="pt-2 border-t border-gray-100"
                                                >
                                                    <span
                                                        class="text-gray-500 block"
                                                        >Dirección:</span
                                                    >
                                                    <p
                                                        class="text-gray-700 mt-1 truncate"
                                                    >
                                                        {{
                                                            item.direccion_historico
                                                        }}
                                                    </p>
                                                </div>
                                                <div
                                                    v-if="
                                                        item.origen_motivo_cancelacion
                                                    "
                                                    class="pt-2 border-t border-gray-100"
                                                >
                                                    <span
                                                        class="text-gray-500 block"
                                                        >Motivo
                                                        Cancelación:</span
                                                    >
                                                    <p
                                                        class="text-gray-700 mt-1 truncate"
                                                    >
                                                        {{
                                                            item.origen_motivo_cancelacion
                                                        }}
                                                    </p>
                                                </div>
                                                <div
                                                    v-if="item.observaciones"
                                                    class="pt-2 border-t border-gray-100"
                                                >
                                                    <span
                                                        class="text-gray-500 block"
                                                        >Observaciones:</span
                                                    >
                                                    <p
                                                        class="text-gray-700 mt-1 truncate"
                                                    >
                                                        {{ item.observaciones }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </details>
                                </div>

                                <!-- Footer -->
                                <div
                                    class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between"
                                >
                                    <div
                                        class="text-xs text-gray-500 min-w-0 flex-1 mr-2"
                                    >
                                        <div class="truncate">
                                            {{ item.created_at_formatted }}
                                        </div>
                                        <div
                                            v-if="item.asignado_a?.name"
                                            class="font-medium truncate"
                                        >
                                            {{ item.asignado_a.name }}
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <Actions
                                            :edit="props.canEdit"
                                            :editDisabled="
                                                props.canEdit &&
                                                !canEditThisRecord(item)
                                            "
                                            :remove="props.canDelete"
                                            :list="props.canList"
                                            :canViewHistory="
                                                props.canViewHistory
                                            "
                                            :canSchedule="props.canSchedule"
                                            :scheduleDisabled="
                                                props.canSchedule &&
                                                !canScheduleThisRecord(item)
                                            "
                                            @edit="emit('edit', item)"
                                            @delete="emit('delete', item)"
                                            @history="emit('showHistory', item)"
                                            @schedule="emit('schedule', item)"
                                            @click.stop
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// ========================================
// IMPORTS
// ========================================
import {
    onMounted,
    ref,
    watch,
    h,
    render,
    computed,
    onUnmounted,
    shallowRef,
    markRaw,
    nextTick,
} from "vue";
import Actions from "@/Components/Actions.vue";

// ========================================
// PROPS Y EMITS
// ========================================
const props = defineProps({
    rows: Array,
    columns: Array,
    canViewGlobal: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
    canList: Boolean,
    isLoading: Boolean,
    canViewHistory: Boolean,
    canSchedule: Boolean,
    canEditRecord: Function,
    canEditComplete: Boolean,
    viewMode: {
        type: String,
        default: "grid",
        validator: (value) => ["grid", "cards"].includes(value),
    },
});

const emit = defineEmits([
    "edit",
    "delete",
    "list",
    "update:selected",
    "showHistory",
    "schedule",
]);

// ========================================
// VARIABLES REACTIVAS
// ========================================
const gridContainer = ref(null);
const selectedItems = ref(new Set());
const windowWidth = ref(
    typeof window !== "undefined" ? window.innerWidth : 1280
);
const rowDataInternal = shallowRef([]);
const isUpdatingGrid = ref(false);
const isLoadingViewSwitch = ref(false);
const cardColumnsData = ref([]);
let gridApi = null;

// ========================================
// COMPUTADAS
// ========================================
const gridHeightStyle = computed(() => {
    if (!props.rows || props.rows.length === 0) {
        return { height: "700px" };
    }
    const rowCount = props.rows.length;
    const rowHeight = 37;
    const headerHeight = 35;
    let totalHeight = headerHeight + rowCount * rowHeight + 2;
    if (totalHeight < 200) totalHeight = 200;
    if (totalHeight > 700) totalHeight = 700;
    return { height: totalHeight + "px" };
});

const getColumnCount = computed(() => {
    const width = windowWidth.value;
    if (width < 640) return 1; // sm: 1 columna
    if (width < 1024) return 2; // md: 2 columnas
    if (width < 1280) return 3; // lg: 3 columnas
    return 4; // xl: 4 columnas
});

// ========================================
// FUNCIONES DE TARJETAS
// ========================================
function toggleCardSelection(item) {
    if (selectedItems.value.has(item.id)) {
        selectedItems.value.delete(item.id);
    } else {
        selectedItems.value.add(item.id);
    }
    emitSelectedFromCards();
}

function emitSelectedFromCards() {
    if (props.viewMode === "cards") {
        const selected =
            props.rows?.filter((item) => selectedItems.value.has(item.id)) ||
            [];
        emit("update:selected", selected);
    }
}

function canEditThisRecord(item) {
    return props.canEditRecord ? props.canEditRecord(item) : true;
}

function canScheduleThisRecord(item) {
    return item?.trazabilidad === "completado";
}

function getStatusClass(status) {
    const estado = (status || "—").toLowerCase();
    const baseClass = "px-2 py-1 rounded-md text-xs font-medium";

    switch (estado) {
        case "pendiente":
            return `bg-yellow-100 text-yellow-700 ${baseClass}`;
        case "asignado":
            return `bg-blue-100 text-blue-700 ${baseClass}`;
        case "completado":
            return `bg-green-100 text-green-700 ${baseClass}`;
        case "retornado":
            return `bg-red-100 text-red-700 ${baseClass}`;
        case "agendado":
            return `bg-purple-100 text-purple-700 ${baseClass}`;
        default:
            return `bg-gray-100 text-gray-700 ${baseClass}`;
    }
}

function getItemsForColumn(columnIndex) {
    if (!props.rows) return [];

    // Usar datos cacheados si están disponibles
    if (cardColumnsData.value[columnIndex]) {
        return cardColumnsData.value[columnIndex];
    }

    const totalColumns = getColumnCount.value;
    const items = [];

    // Distribuir elementos por columnas
    for (let i = columnIndex; i < props.rows.length; i += totalColumns) {
        items.push(props.rows[i]);
    }

    return items;
}

function prepareColumnData() {
    return new Promise((resolve) => {
        const totalColumns = getColumnCount.value;
        const newColumnsData = Array(totalColumns)
            .fill(null)
            .map(() => []);

        if (props.rows) {
            const chunkSize = Math.max(50, Math.floor(props.rows.length / 10));
            let currentIndex = 0;

            const processChunk = () => {
                const endIndex = Math.min(
                    currentIndex + chunkSize,
                    props.rows.length
                );

                for (let i = currentIndex; i < endIndex; i++) {
                    const columnIndex = i % totalColumns;
                    newColumnsData[columnIndex].push(props.rows[i]);
                }

                currentIndex = endIndex;

                if (currentIndex < props.rows.length) {
                    requestAnimationFrame(processChunk);
                } else {
                    cardColumnsData.value = newColumnsData;
                    resolve();
                }
            };

            requestAnimationFrame(processChunk);
        } else {
            resolve();
        }
    });
}

// ========================================
// DEFINICIÓN DE COLUMNAS AG-GRID
// ========================================
const createColumnDef = (col) => {
    const columnMap = {
        id: {
            field: "id",
            headerName: "ID",
            minWidth: 50,
            maxWidth: 60,
            width: 50,
        },
        upload_id: {
            field: "upload_id",
            headerName: "Carga",
            minWidth: 75,
            maxWidth: 100,
            width: 70,
        },
        created_at_formatted: {
            valueGetter: (params) => params.data?.created_at_formatted || "",
            headerName: "Fecha de Carga",
            minWidth: 130,
            maxWidth: 180,
            width: 150,
        },
        nombre_cliente: {
            field: "nombre_cliente",
            headerName: "Nombre del Cliente",
        },
        dni_cliente: { field: "dni_cliente", headerName: "DNI Cliente" },
        telefono_principal: {
            field: "telefono_principal",
            headerName: "Teléfono Principal",
        },
        telefono_adicional: {
            field: "telefono_adicional",
            headerName: "Teléfono Adicional",
        },
        correo_referencia: {
            field: "correo_referencia",
            headerName: "Correo de Referencia",
        },
        orden_trabajo_anterior: {
            field: "orden_trabajo_anterior",
            headerName: "Orden Trabajo Anterior",
        },
        direccion_historico: {
            field: "direccion_historico",
            headerName: "Dirección Histórico",
        },
        observaciones: { field: "observaciones", headerName: "Observaciones" },
        marca_base: { field: "marca_base", headerName: "Marca de la Base" },
        origen_motivo_cancelacion: {
            field: "origen_motivo_cancelacion",
            headerName: "Origen Cancelación",
        },
        user: {
            headerName: "Usuario",
            valueGetter: (p) => p.data.user?.name || "Sin usuario",
        },
        asignado_a: {
            headerName: "Asignado",
            valueGetter: (params) => params.data?.asignado_a?.name || "—",
        },
        trazabilidad: {
            field: "trazabilidad",
            headerName: "Trazabilidad",
            minWidth: 100,
            maxWidth: 340,
            width: 220,
            cellRenderer: ({ data }) => {
                const estado = (data.trazabilidad || "—").toLowerCase();
                const colorMap = {
                    pendiente: { bg: "bg-yellow-100", text: "text-yellow-800" },
                    asignado: { bg: "bg-blue-100", text: "text-blue-700" },
                    completado: { bg: "bg-green-100", text: "text-green-700" },
                    retornado: { bg: "bg-red-100", text: "text-red-700" },
                    agendado: { bg: "bg-purple-100", text: "text-purple-700" },
                };
                const colors = colorMap[estado] || {
                    bg: "bg-gray-200",
                    text: "text-gray-700",
                };

                const span = document.createElement("span");
                span.className = `${colors.bg} ${colors.text} inline-block px-2 py-1 rounded-full text-xs font-semibold min-w-[70px] text-center whitespace-nowrap`;
                span.textContent = data.trazabilidad || "—";
                span.title = data.trazabilidad || "—";
                return span;
            },
        },
        origen_base: {
            field: "origen_base",
            headerName: "Origen de la Base",
            minWidth: 100,
            maxWidth: 200,
            width: 150,
            cellRenderer: ({ data }) => {
                const origen = (data.origen_base || "—").toLowerCase();
                const colorMap = {
                    vodafone: { bg: "bg-blue-100", text: "text-blue-700" },
                    movistar: { bg: "bg-green-100", text: "text-green-700" },
                    orange: { bg: "bg-orange-100", text: "text-orange-700" },
                    otros: { bg: "bg-purple-100", text: "text-purple-700" },
                };
                const colors = colorMap[origen] || {
                    bg: "bg-gray-200",
                    text: "text-gray-700",
                };

                const span = document.createElement("span");
                span.className = `${colors.bg} ${colors.text} inline-block px-2 py-1 rounded-full text-xs font-semibold min-w-[70px] text-center whitespace-nowrap`;
                span.textContent = data.origen_base || "—";
                span.title = data.origen_base || "—";
                return span;
            },
        },
    };

    return columnMap[col] || null;
};

const columnDefs = props.columns.map(createColumnDef).filter(Boolean);

// Agregar columna de acciones si es necesario
const hasActions =
    props.canEdit ||
    props.canDelete ||
    props.canList ||
    props.canViewHistory ||
    props.canSchedule ||
    props.canEditComplete;

if (hasActions) {
    // Calcular ancho dinámico basado en la cantidad de acciones disponibles
    const calculateActionsWidth = () => {
        let actionCount = 0;

        if (props.canEdit) actionCount++;
        if (props.canDelete) actionCount++;
        if (props.canList) actionCount++;
        if (props.canViewHistory) actionCount++;
        if (props.canSchedule) actionCount++;
        if (props.canEditComplete) actionCount++;

        // Ancho base por acción (40px) + padding + gap
        // 1 acción: ~80px, 2-3 acciones: ~140px, 4+ acciones: ~200px, 5+ acciones: ~250px
        const baseWidth = 40; // Ancho por botón
        const padding = 40; // Padding lateral
        const gap = 8; // Gap entre botones

        let calculatedWidth =
            actionCount * baseWidth + padding + (actionCount - 1) * gap;

        // Rangos mínimos y máximos
        if (actionCount === 1) {
            calculatedWidth = Math.max(calculatedWidth, 54);
            calculatedWidth = Math.min(calculatedWidth, 72);
        } else if (actionCount === 2) {
            calculatedWidth = Math.max(calculatedWidth, 72);
            calculatedWidth = Math.min(calculatedWidth, 96);
        } else if (actionCount === 3) {
            calculatedWidth = Math.max(calculatedWidth, 96);
            calculatedWidth = Math.min(calculatedWidth, 120);
        } else if (actionCount === 4) {
            calculatedWidth = Math.max(calculatedWidth, 120);
            calculatedWidth = Math.min(calculatedWidth, 144);
        } else if (actionCount >= 5) {
            calculatedWidth = Math.max(calculatedWidth, 144);
            calculatedWidth = Math.min(calculatedWidth, 180);
        }

        return calculatedWidth;
    };

    const dynamicWidth = calculateActionsWidth();

    columnDefs.push({
        headerName: "Acciones",
        field: "acciones",
        pinned: "right",
        minWidth: Math.max(dynamicWidth - 20, 80), // Mínimo absoluto de 80px
        maxWidth: Math.min(dynamicWidth + 50, 350), // Máximo de 350px
        width: dynamicWidth,
        resizable: false,
        flex: undefined,
        cellRenderer: (params) => {
            const container = document.createElement("div");
            container.className =
                "flex items-center justify-center gap-1 h-full";

            const canEditThisRecord = props.canEditRecord
                ? props.canEditRecord(params.data)
                : true;
            const isEditEnabled = canEditThisRecord;
            const isScheduleEnabled = canScheduleThisRecord(params.data);

            const vnode = h(Actions, {
                edit: props.canEdit,
                editDisabled: props.canEdit && !isEditEnabled,
                remove: props.canDelete,
                list: props.canList,
                canViewHistory: props.canViewHistory,
                canSchedule: props.canSchedule,
                scheduleDisabled: props.canSchedule && !isScheduleEnabled,
                onEdit: () => emit("edit", params.data),
                onDelete: () => emit("delete", params.data),
                onHistory: () => emit("showHistory", params.data),
                onSchedule: () => emit("schedule", params.data),
            });

            render(vnode, container);
            return container;
        },
    });
}

const defaultColDef = {
    resizable: true,
    flex: 1,
    sortable: false,
    minWidth: 100,
    maxWidth: 250,
    cellClass: "ag-center-cols",
};

// ========================================
// FUNCIONES DE SELECCIÓN
// ========================================
let isDragging = false;
let startRowIndex = null;

function getRowIdFromElement(el) {
    const row = el.closest(".ag-row");
    return row?.getAttribute("row-id");
}

function emitSelectedRows() {
    const selected = gridApi?.getSelectedRows() || [];
    emit("update:selected", selected);
}

function selectAll() {
    if (props.viewMode === "grid") {
        gridApi?.selectAll();
        emitSelectedRows();
    } else {
        selectedItems.value.clear();
        props.rows?.forEach((item) => selectedItems.value.add(item.id));
        emitSelectedFromCards();
    }
}

function clearSelection() {
    if (props.viewMode === "grid") {
        gridApi?.deselectAll();
        emitSelectedRows();
    } else {
        selectedItems.value.clear();
        emitSelectedFromCards();
    }
}

function handleMouseDown(e) {
    if (e.button !== 0) return;
    const cellEl = e.target.closest(".ag-cell");
    if (!cellEl) return;

    const rowId = getRowIdFromElement(cellEl);
    const node = gridApi?.getRowNode(rowId);
    if (!node) return;

    startRowIndex = node.rowIndex;
    isDragging = true;
    if (!e.ctrlKey && !e.metaKey) gridApi?.deselectAll();
}

function handleMouseMove(e) {
    if (!isDragging) return;

    const el = document.elementFromPoint(e.clientX, e.clientY);
    const cellEl = el?.closest(".ag-cell");
    if (!cellEl) return;

    const rowId = getRowIdFromElement(cellEl);
    const node = gridApi?.getRowNode(rowId);
    if (!node) return;

    const endRowIndex = node.rowIndex;
    selectRowRange(startRowIndex, endRowIndex, true);

    const viewport = gridContainer.value.querySelector(
        ".ag-body-viewport no-border"
    );
    if (!viewport) return;

    const viewportRect = viewport.getBoundingClientRect();
    const scrollThreshold = 40;
    const maxSpeed = 10;

    let scrollDelta = 0;

    if (e.clientY < viewportRect.top + scrollThreshold) {
        scrollDelta = -maxSpeed;
    } else if (e.clientY > viewportRect.bottom - scrollThreshold) {
        scrollDelta = maxSpeed;
    }

    if (scrollDelta !== 0) {
        requestAnimationFrame(() => {
            viewport.scrollTop += scrollDelta;
        });
    }
}

function handleMouseUp() {
    isDragging = false;
    startRowIndex = null;
}

function selectRowRange(start, end, additive = false) {
    if (!additive) gridApi?.deselectAll();
    const min = Math.min(start, end);
    const max = Math.max(start, end);
    gridApi?.forEachNodeAfterFilterAndSort((node) => {
        if (node.rowIndex >= min && node.rowIndex <= max) {
            node.setSelected(true);
        }
    });
}

// ========================================
// FUNCIONES DE AG-GRID
// ========================================
function onGridReady(params) {
    gridApi = params.api;
    gridColumnApi = params.columnApi;

    setTimeout(() => {
        if (gridApi && gridApi.isDestroyed?.() === false) {
            gridApi.sizeColumnsToFit();
        }
    }, 100);
}

function onSelectionChanged() {
    emitSelectedRows();
}

function onFirstDataRendered(params) {
    if (gridApi && gridApi.isDestroyed?.() === false) {
        gridApi.sizeColumnsToFit();
    }
}

// ========================================
// FUNCIONES DE MODO CARDS
// ========================================
function isCardSelected(item) {
    return selectedItems.value.has(item.id);
}

function handleCardEdit(item) {
    emit("edit", item);
}

function handleCardDelete(item) {
    emit("delete", item);
}

function handleCardHistory(item) {
    emit("showHistory", item);
}

function handleCardSchedule(item) {
    emit("schedule", item);
}

// ========================================
// WATCHERS Y REACTIVIDAD
// ========================================
let updateTimeout = null;

function debounceUpdate(fn, delay = 150) {
    return (...args) => {
        clearTimeout(updateTimeout);

        const dataSize = args[0]?.length || 0;
        let actualDelay;

        if (dataSize > 5000) actualDelay = 800;
        else if (dataSize > 2000) actualDelay = 500;
        else if (dataSize > 1000) actualDelay = 300;
        else if (dataSize > 500) actualDelay = 200;
        else actualDelay = 100;

        updateTimeout = setTimeout(() => fn(...args), actualDelay);
    };
}

const debouncedUpdateGrid = debounceUpdate((newRows) => {
    if (!gridApi || !Array.isArray(newRows) || isUpdatingGrid.value) return;

    isUpdatingGrid.value = true;

    try {
        const processedRows = newRows.length > 100 ? markRaw(newRows) : newRows;

        const isAppending =
            newRows.length > rowDataInternal.value.length &&
            rowDataInternal.value.length > 0 &&
            newRows.length < 1000 &&
            newRows
                .slice(0, Math.min(rowDataInternal.value.length, 10))
                .every((item, i) => item.id === rowDataInternal.value[i]?.id);

        if (!newRows || newRows.length === 0) {
            rowDataInternal.value = markRaw([]);
            gridApi.setGridOption("rowData", []);
        } else if (isAppending) {
            const added = newRows.slice(rowDataInternal.value.length);
            rowDataInternal.value = markRaw([
                ...rowDataInternal.value,
                ...added,
            ]);
            gridApi.applyTransaction({ add: markRaw(added) });
        } else {
            rowDataInternal.value = processedRows;
            gridApi.setGridOption("rowData", processedRows);
        }

        gridApi.hideOverlay();

        if (newRows.length < 200) {
            gridApi.refreshCells();
        }
    } catch (error) {
        console.warn("Error updating grid:", error);
    } finally {
        isUpdatingGrid.value = false;
    }
}, 300);

watch(
    () => props.rows,
    (newRows, oldRows) => {
        if (newRows === oldRows) return;

        cardColumnsData.value = [];

        const newLength = newRows?.length || 0;
        const oldLength = oldRows?.length || 0;

        if (newLength > 1000 && oldLength > 1000) {
            if (
                newLength === oldLength &&
                newRows[0]?.id === oldRows[0]?.id &&
                newRows[newLength - 1]?.id === oldRows[oldLength - 1]?.id
            ) {
                return;
            }
        }

        debouncedUpdateGrid(newRows);
    },
    { immediate: true, deep: false, flush: "post" }
);

// ========================================
// CONFIGURACIÓN Y MONTAJE DE AG-GRID
// ========================================
onMounted(async () => {
    // Verificar que AG-Grid esté disponible
    if (!window.agGrid?.createGrid) {
        console.warn("AG-Grid no está disponible");
        return;
    }

    // Esperar a que el DOM esté completamente renderizado
    await nextTick();

    // Verificar que el contenedor del grid existe y está en el DOM
    if (!gridContainer.value) {
        console.warn("Contenedor del grid no encontrado");
        return;
    }

    // Verificar que el contenedor está conectado al DOM
    if (!gridContainer.value.isConnected) {
        console.warn("Contenedor del grid no está conectado al DOM");
        return;
    }

    try {
        const gridOptions = {
            columnDefs,
            rowData: markRaw(props.rows || []),
            defaultColDef,
            rowSelection: "multiple",
            suppressRowClickSelection: false,
            rowHeight: 37,
            headerHeight: 35,
            enableRangeSelection: false,
            animateRows: false,
            suppressCellFocus: true,
            suppressColumnVirtualisation: false,
            suppressRowVirtualisation: false,
            rowBuffer: 10,
            maxBlocksInCache: 2,
            maxConcurrentDatasourceRequests: 1,
            suppressAggFuncInHeader: true,
            suppressMenuHide: true,
            suppressMovableColumns: true,
            onGridReady: (params) => {
                gridApi = params.api;

                const gridElement = gridContainer.value;
                if (
                    gridElement &&
                    gridElement.offsetWidth > 0 &&
                    props.viewMode === "grid"
                ) {
                    setTimeout(() => {
                        if (
                            gridApi &&
                            !gridApi.isDestroyed?.() &&
                            gridElement.offsetWidth > 0
                        ) {
                            gridApi.sizeColumnsToFit();
                        }
                    }, 100);
                }

                gridApi.addEventListener("selectionChanged", emitSelectedRows);

                if (
                    params.columnApi &&
                    typeof params.columnApi.getAllColumns === "function"
                ) {
                    const allColumnIds = [];
                    params.columnApi.getAllColumns().forEach((column) => {
                        allColumnIds.push(column.getId());
                    });

                    if (
                        allColumnIds.length <= 8 &&
                        gridElement.offsetWidth > 0 &&
                        props.viewMode === "grid"
                    ) {
                        setTimeout(() => {
                            if (
                                params.columnApi &&
                                gridElement.offsetWidth > 0
                            ) {
                                params.columnApi.autoSizeColumns(
                                    allColumnIds,
                                    false
                                );
                            }
                        }, 150);
                    }
                }

                gridApi.hideOverlay();
            },
        };

        const { createGrid } = window.agGrid;
        gridApi = createGrid(gridContainer.value, gridOptions);

        // Verificar que el grid se creó correctamente
        if (!gridApi) {
            console.warn("No se pudo crear el grid AG-Grid");
            return;
        }

        // Eventos personalizados - solo si el grid se creó exitosamente
        if (gridContainer.value) {
            gridContainer.value.addEventListener("mousedown", handleMouseDown);
        }

        document.addEventListener("mousemove", handleMouseMove);
        document.addEventListener("mouseup", handleMouseUp);

        // Event listener para resize de ventana
        window.addEventListener("resize", throttledResize);
    } catch (error) {
        console.error("Error al crear AG-Grid:", error);
        // No propagar el error para evitar que se rompa la aplicación
    }
});

// ========================================
// FUNCIONES DE RESIZE
// ========================================
let resizeTimeout = null;
let resizeRequestId = null;

const throttledResize = () => {
    if (resizeTimeout) return;

    if (resizeRequestId) {
        cancelAnimationFrame(resizeRequestId);
    }

    resizeTimeout = setTimeout(() => {
        resizeRequestId = requestAnimationFrame(() => {
            handleResize();
            resizeTimeout = null;
            resizeRequestId = null;
        });
    }, 350);
};

function handleResize() {
    windowWidth.value = window.innerWidth;
    if (gridApi && !isUpdatingGrid.value && props.viewMode === "grid") {
        const gridElement = gridContainer.value;
        if (gridElement && gridElement.offsetWidth > 0) {
            gridApi.sizeColumnsToFit();
        }
    }
}

// ========================================
// WATCHER PARA CAMBIO DE VISTA
// ========================================
watch(
    () => props.viewMode,
    async (newMode, oldMode) => {
        if (newMode === oldMode) return;

        if (newMode === "cards") {
            isLoadingViewSwitch.value = true;
            cardColumnsData.value = [];

            await new Promise((resolve) => setTimeout(resolve, 50));
            await prepareColumnData();

            const gridSelected = gridApi?.getSelectedRows() || [];
            selectedItems.value.clear();
            gridSelected.forEach((item) => selectedItems.value.add(item.id));
            emitSelectedFromCards();

            setTimeout(() => {
                isLoadingViewSwitch.value = false;
            }, 100);
        } else if (newMode === "grid") {
            gridApi?.deselectAll();
            const selectedIds = Array.from(selectedItems.value);
            gridApi?.forEachNode((node) => {
                if (selectedIds.includes(node.data.id)) {
                    node.setSelected(true);
                }
            });
            emitSelectedRows();
        }
    }
);

// ========================================
// LIMPIEZA Y DESMONTAJE
// ========================================
onUnmounted(() => {
    try {
        // Limpiar timeouts
        if (updateTimeout) clearTimeout(updateTimeout);
        if (resizeTimeout) clearTimeout(resizeTimeout);
        if (resizeRequestId) cancelAnimationFrame(resizeRequestId);

        // Limpiar grid API
        if (gridApi && !gridApi.isDestroyed?.()) {
            try {
                gridApi.destroy();
            } catch (error) {
                console.warn("Error al destruir AG-Grid:", error);
            }
        }

        // Remover event listeners
        if (gridContainer.value) {
            gridContainer.value.removeEventListener(
                "mousedown",
                handleMouseDown
            );
        }

        document.removeEventListener("mousemove", handleMouseMove);
        document.removeEventListener("mouseup", handleMouseUp);
        window.removeEventListener("resize", throttledResize);

        // Limpiar referencias
        gridApi = null;
    } catch (error) {
        console.warn("Error durante la limpieza del componente:", error);
    }
});
</script>

<style scoped>
.ag-center-cols {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}

.btn {
    padding: 0.4rem 0.8rem;
    background-color: #007bff;
    color: white;
    font-size: 0.9rem;
    border-radius: 6px;
    transition: all 0.2s;
}
.btn:hover {
    background-color: #0056b3;
}
.modern-btn {
    background: rgba(255, 255, 255, 0.85);
    border: none;
    border-radius: 50%;
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
    box-shadow: none;
    outline: none;
}
.modern-btn:hover {
    background: #eef2ff;
    transform: scale(1.12);
}
.ag-theme-alpine {
    min-height: 700px;
    font-size: 13px;
    --ag-header-background-color: #f8f9fa;
    --ag-header-foreground-color: #333;
    --ag-selected-row-background-color: #ffcc99; /* ← naranja suave */
}

/* Animación simple para details/summary - ULTRA RÁPIDA */
.details-content {
    max-height: 0;
    opacity: 0;
    overflow: hidden;
    padding-top: 0;
    padding-bottom: 0;
    transition: max-height 0.2s ease-out, opacity 0.15s ease-out,
        padding 0.2s ease-out;
}

.details-animated[open] .details-content {
    max-height: 400px; /* Reducido para menos cálculos */
    opacity: 1;
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}

.details-inner {
    transform: translateY(-5px); /* Menos movimiento */
    opacity: 0;
    transition: all 0.15s ease-out;
}

.details-animated[open] .details-inner {
    transform: translateY(0);
    opacity: 1;
    transition-delay: 0.05s; /* Delay mínimo */
}

/* Cada columna individual mantiene su espacio independiente */
.flex-1 {
    min-width: 0; /* Permite que flex-1 se comprima correctamente */
}

/* Las tarjetas dentro de cada columna se mueven suavemente */
.space-y-4 > * {
    transition: margin-top 0.25s ease-out, transform 0.25s ease-out,
        height 0.25s ease-out;
    will-change: auto;
}

/* Asegurar que las tarjetas no se expandan más allá del contenedor */
.flex-1 > div {
    width: 100%;
    overflow: hidden;
}

/* Optimización para mejor rendimiento en las animaciones */
.space-y-4 {
    will-change: auto;
    transition: height 0.25s ease-out;
}

/* Optimización adicional para transiciones de tarjetas */
.space-y-4 > .animated-card {
    will-change: auto;
    transform-origin: top center;
    transition: all 0.25s ease-out;
}

/* Columna de tarjetas con posición relativa para transiciones */
.card-column {
    position: relative;
    transition: height 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

/* Transiciones para TransitionGroup - movimiento de tarjetas */
.card-move-move,
.card-move-enter-active,
.card-move-leave-active {
    transition: all 0.2s ease-out;
}

.card-move-enter-from {
    opacity: 0;
    transform: translateY(-10px) scale(0.98);
}

.card-move-leave-to {
    opacity: 0;
    transform: translateY(10px) scale(0.98);
}

.card-move-leave-active {
    position: absolute;
    width: calc(100% - 1rem);
    z-index: 0;
}

/* Clase específica para animaciones suaves de tarjetas */
.animated-card {
    transition: transform 0.15s ease-out, box-shadow 0.15s ease-out,
        border-color 0.15s ease-out, height 0.25s ease-out;
    will-change: auto;
    backface-visibility: hidden;
    overflow: hidden;
}

.animated-card:hover {
    transition: transform 0.1s ease-out, box-shadow 0.1s ease-out,
        border-color 0.1s ease-out;
}

/* Transiciones suaves para elementos internos de las tarjetas */
.animated-card > * {
    transition: all 0.15s ease-out;
}

/* Optimizar el espacio y movimiento suave */
.space-y-4 {
    position: relative;
    min-height: 0;
    transition: height 0.25s ease-out;
    will-change: auto;
}

/* Animación para el fade-in de tarjetas */
@keyframes fadeInCard {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>
