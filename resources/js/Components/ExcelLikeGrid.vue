<template>
    <div class="relative">
        <!-- Botones de selección modernos, esquina inferior derecha -->
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
                    ></path>
                </svg>
            </button>
        </div>

        <!-- Vista Grid (existente) -->
        <div v-show="props.viewMode === 'grid'" class="relative">
            <div
                ref="gridContainer"
                id="myGrid"
                :class="'ag-theme-alpine w-full rounded-lg shadow border'"
                :style="{ height: gridHeightStyle.height }"
            ></div>

            <transition name="fade">
                <div
                    v-if="
                        !isLoading && (!props.rows || props.rows.length === 0)
                    "
                    class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 animate__animated animate__fadeIn"
                    style="pointer-events: none"
                >
                    <div class="flex flex-col items-center gap-4">
                        <svg
                            class="h-16 w-16 text-gray-300"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        <span
                            class="text-gray-500 font-semibold text-lg text-center"
                        >
                            Sin registros para mostrar
                        </span>
                        <span class="text-gray-400 text-sm text-center">
                            No hay datos disponibles en este momento
                        </span>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Nueva Vista de Tarjetas -->
        <div v-show="props.viewMode === 'cards'" class="relative">
            <div :style="{ minHeight: '700px' }" class="flex flex-col">
                <div
                    v-if="
                        !isLoading && (!props.rows || props.rows.length === 0)
                    "
                    class="flex-1 flex flex-col items-center justify-center"
                >
                    <div class="flex flex-col items-center gap-4">
                        <svg
                            class="h-16 w-16 text-gray-300"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        <span
                            class="text-gray-500 font-semibold text-lg text-center"
                        >
                            Sin registros para mostrar
                        </span>
                        <span class="text-gray-400 text-sm text-center">
                            No hay datos disponibles en este momento
                        </span>
                    </div>
                </div>

                <div v-else class="w-full">
                    <!-- Vista continua de todas las tarjetas -->
                    <div class="w-full max-w-none">
                        <TransitionGroup
                            name="card-move"
                            tag="div"
                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
                        >
                            <div
                                v-for="(item, itemIndex) in props.rows"
                                :key="item.id"
                                @click="toggleCardSelection(item)"
                                :class="[
                                    'animated-card bg-white rounded-xl border cursor-pointer shadow-sm w-full max-w-full',
                                    selectedItems.has(item.id)
                                        ? 'border-blue-500 shadow-blue-100 shadow-lg ring-1 ring-blue-200'
                                        : 'border-gray-200 hover:border-gray-300 hover:shadow-md',
                                ]"
                            >
                                <!-- Header simplificado -->
                                <div
                                    :class="[
                                        'p-4 border-b flex items-center justify-between',
                                        selectedItems.has(item.id)
                                            ? 'bg-blue-50 border-blue-100'
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
                                                class="font-semibold text-gray-900 text-sm truncate"
                                            >
                                                {{
                                                    item.nombre_cliente ||
                                                    "Sin nombre"
                                                }}
                                            </h3>
                                            <p class="text-xs text-gray-500">
                                                ID: {{ item.id }}
                                            </p>
                                        </div>
                                    </div>
                                    <span
                                        :class="
                                            getSimpleStatusClass(
                                                item.trazabilidad
                                            )
                                        "
                                    >
                                        {{ item.trazabilidad || "—" }}
                                    </span>
                                </div>

                                <!-- Contenido compacto -->
                                <div class="p-4 space-y-3">
                                    <!-- Info principal en una sola fila -->
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
                                                >{{
                                                    item.dni_cliente || "—"
                                                }}</span
                                            >
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
                                                >{{
                                                    item.telefono_principal ||
                                                    "—"
                                                }}</span
                                            >
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
                                                >{{
                                                    item.orden_trabajo_anterior
                                                }}</span
                                            >
                                        </div>
                                    </div>

                                    <!-- Más detalles colapsables con animación -->
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

                                <!-- Footer minimalista -->
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
                        </TransitionGroup>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// ===== IMPORTS =====
import {
    onMounted,
    ref,
    watch,
    h,
    render,
    nextTick,
    computed,
    onUnmounted,
    TransitionGroup,
    shallowRef,
    markRaw,
} from "vue";
import Actions from "@/Components/Actions.vue";

// ===== FUNCIONES DE ANIMACIÓN MANUAL =====
function setupDetailAnimations() {
    // OPTIMIZACIÓN MÁXIMA: Solo para datasets muy pequeños
    if ((props.rows?.length || 0) > 100) return;

    nextTick(() => {
        const detailsElements = document.querySelectorAll(".details-animated");

        // Usar requestAnimationFrame para mejor rendimiento
        requestAnimationFrame(() => {
            detailsElements.forEach((details) => {
                details.removeEventListener("toggle", handleDetailsToggle);
                details.addEventListener("toggle", handleDetailsToggle, {
                    passive: true,
                });
            });
        });
    });
}

function handleDetailsToggle(event) {
    // Función ultra optimizada
    event.stopPropagation();
}

// ===== PROPS Y EMITS =====
const props = defineProps({
    rows: Array,
    columns: Array, // <-- agrega esta prop
    canViewGlobal: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
    canList: Boolean,
    isLoading: Boolean, // <-- para evitar el warning
    canViewHistory: Boolean, // <-- ¡AQUÍ!
    canSchedule: Boolean, // <-- Nueva prop para permiso agendar
    canEditRecord: Function, // <-- Nueva prop para función de validación
    canEditComplete: Boolean, // <-- Nueva prop para indicar si se puede editar completamente
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

// ===== VARIABLES REACTIVAS =====
const gridContainer = ref(null);
const selectedItems = ref(new Set()); // Para manejar selección en vista tarjetas
const windowWidth = ref(
    typeof window !== "undefined" ? window.innerWidth : 1280
);
// Usar shallowRef para mejor rendimiento con arrays grandes
const rowDataInternal = shallowRef([]);
const isUpdatingGrid = ref(false);
let gridApi = null;

// ===== ALTURA DINÁMICA DEL GRID =====
const gridHeightStyle = computed(() => {
    // Si no hay datos, altura mínima fija
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

// ===== FUNCIONES PARA VISTA DE TARJETAS =====
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
    // Solo se puede agendar si la trazabilidad es "completado"
    return item?.trazabilidad === "completado";
}

function getStatusClass(status) {
    const estado = (status || "—").toLowerCase();
    if (estado === "pendiente") {
        return "bg-yellow-100 text-yellow-800 px-1.5 py-0.5 rounded-full text-xs font-semibold";
    } else if (estado === "asignado") {
        return "bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full text-xs font-semibold";
    } else if (estado === "completado") {
        return "bg-green-100 text-green-700 px-1.5 py-0.5 rounded-full text-xs font-semibold";
    } else if (estado === "retornado") {
        return "bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full text-xs font-semibold";
    } else if (estado === "agendado") {
        return "bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded-full text-xs font-semibold";
    }
    return "bg-gray-200 text-gray-700 px-1.5 py-0.5 rounded-full text-xs font-semibold";
}

function getEnhancedStatusClass(status) {
    const estado = (status || "Sin estado").toLowerCase();
    if (estado === "pendiente") {
        return "bg-yellow-500 text-white px-3 py-1.5 rounded-full text-xs font-bold shadow-sm";
    } else if (estado === "asignado") {
        return "bg-blue-500 text-white px-3 py-1.5 rounded-full text-xs font-bold shadow-sm";
    } else if (estado === "completado") {
        return "bg-green-500 text-white px-3 py-1.5 rounded-full text-xs font-bold shadow-sm";
    } else if (estado === "retornado") {
        return "bg-red-500 text-white px-3 py-1.5 rounded-full text-xs font-bold shadow-sm";
    } else if (estado === "agendado") {
        return "bg-purple-500 text-white px-3 py-1.5 rounded-full text-xs font-bold shadow-sm";
    }
    return "bg-gray-400 text-white px-3 py-1.5 rounded-full text-xs font-bold shadow-sm";
}

function getSimpleStatusClass(status) {
    const estado = (status || "—").toLowerCase();
    if (estado === "pendiente") {
        return "bg-yellow-100 text-yellow-700 px-2 py-1 rounded-md text-xs font-medium";
    } else if (estado === "asignado") {
        return "bg-blue-100 text-blue-700 px-2 py-1 rounded-md text-xs font-medium";
    } else if (estado === "completado") {
        return "bg-green-100 text-green-700 px-2 py-1 rounded-md text-xs font-medium";
    } else if (estado === "retornado") {
        return "bg-red-100 text-red-700 px-2 py-1 rounded-md text-xs font-medium";
    } else if (estado === "agendado") {
        return "bg-purple-100 text-purple-700 px-2 py-1 rounded-md text-xs font-medium";
    }
    return "bg-gray-100 text-gray-700 px-2 py-1 rounded-md text-xs font-medium";
}

// ===== DEFINICIÓN DE COLUMNAS =====
const columnDefs = props.columns
    .map((col) => {
        // Mapea cada columna a su definición
        switch (col) {
            case "id":
                return {
                    field: "id",
                    headerName: "ID",
                    minWidth: 50,
                    maxWidth: 210,
                    width: 200,
                };
            case "upload_id":
                return {
                    field: "upload_id",
                    headerName: "Carga",
                    minWidth: 75,
                    maxWidth: 100,
                    width: 70,
                };
            case "created_at_formatted":
                return {
                    valueGetter: (params) =>
                        params.data?.created_at_formatted || "",
                    headerName: "Fecha de Carga",
                    minWidth: 130,
                    maxWidth: 180,
                    width: 150,
                };
            case "trazabilidad":
                return {
                    field: "trazabilidad",
                    headerName: "Trazabilidad",
                    minWidth: 100,
                    maxWidth: 340,
                    width: 220,
                    cellRenderer: ({ data }) => {
                        const estado = (data.trazabilidad || "—").toLowerCase();
                        let bg = "bg-gray-200",
                            text = "text-gray-700";
                        if (estado === "pendiente") {
                            bg = "bg-yellow-100";
                            text = "text-yellow-800";
                        } else if (estado === "asignado") {
                            bg = "bg-blue-100";
                            text = "text-blue-700";
                        } else if (estado === "completado") {
                            bg = "bg-green-100";
                            text = "text-green-700";
                        } else if (estado === "retornado") {
                            bg = "bg-red-100";
                            text = "text-red-700";
                        } else if (estado === "agendado") {
                            bg = "bg-purple-100";
                            text = "text-purple-700";
                        }
                        const span = document.createElement("span");
                        span.className = `${bg} ${text} inline-block px-2 py-1 rounded-full text-xs font-semibold min-w-[70px] text-center whitespace-nowrap`;
                        span.textContent = data.trazabilidad || "—";
                        // Tooltip nativo para mostrar el texto completo
                        span.title = data.trazabilidad || "—";
                        return span;
                    },
                };
            case "asignado_a":
                return {
                    headerName: "Asignado",
                    valueGetter: (params) =>
                        params.data?.asignado_a?.name || "—",
                };
            case "origen_base":
                return {
                    field: "origen_base",
                    headerName: "Origen de la Base",
                    minWidth: 100,
                    maxWidth: 200,
                    width: 150,
                    cellRenderer: ({ data }) => {
                        const origen = (data.origen_base || "—").toLowerCase();
                        let bg = "bg-gray-200",
                            text = "text-gray-700";
                        if (origen === "vodafone") {
                            bg = "bg-blue-100";
                            text = "text-blue-700";
                        } else if (origen === "movistar") {
                            bg = "bg-green-100";
                            text = "text-green-700";
                        } else if (origen === "orange") {
                            bg = "bg-orange-100";
                            text = "text-orange-700";
                        } else if (origen === "otros") {
                            bg = "bg-purple-100";
                            text = "text-purple-700";
                        }
                        const span = document.createElement("span");
                        span.className = `${bg} ${text} inline-block px-2 py-1 rounded-full text-xs font-semibold min-w-[70px] text-center whitespace-nowrap`;
                        span.textContent = data.origen_base || "—";
                        // Tooltip nativo para mostrar el texto completo
                        span.title = data.origen_base || "—";
                        return span;
                    },
                };
            case "marca_base":
                return { field: "marca_base", headerName: "Marca de la Base" };
            case "origen_motivo_cancelacion":
                return {
                    field: "origen_motivo_cancelacion",
                    headerName: "Origen Cancelación",
                };
            case "nombre_cliente":
                return {
                    field: "nombre_cliente",
                    headerName: "Nombre del Cliente",
                };
            case "dni_cliente":
                return { field: "dni_cliente", headerName: "DNI Cliente" };
            case "orden_trabajo_anterior":
                return {
                    field: "orden_trabajo_anterior",
                    headerName: "Orden Trabajo Anterior",
                };
            case "telefono_principal":
                return {
                    field: "telefono_principal",
                    headerName: "Teléfono Principal",
                };
            case "telefono_adicional":
                return {
                    field: "telefono_adicional",
                    headerName: "Teléfono Adicional",
                };
            case "correo_referencia":
                return {
                    field: "correo_referencia",
                    headerName: "Correo de Referencia",
                };
            case "direccion_historico":
                return {
                    field: "direccion_historico",
                    headerName: "Dirección Histórico",
                };
            case "observaciones":
                return { field: "observaciones", headerName: "Observaciones" };
            case "user":
                return {
                    headerName: "Usuario",
                    valueGetter: (p) => p.data.user?.name || "Sin usuario",
                };
            default:
                return null;
        }
    })
    .filter(Boolean);
const hasActions =
    props.canEdit ||
    props.canDelete ||
    props.canList ||
    props.canViewHistory ||
    props.canSchedule ||
    props.canEditComplete;
if (hasActions) {
    columnDefs.push({
        headerName: "Acciones",
        field: "acciones",
        pinned: "right",
        minWidth: 120,
        maxWidth: 160,
        width: 160,
        resizable: false,
        flex: undefined, // No flex para que no se expanda
        cellRenderer: (params) => {
            const container = document.createElement("div");
            container.className =
                "flex items-center justify-center gap-1 h-full";

            // Verificar si el usuario puede editar este registro específico
            const canEditThisRecord = props.canEditRecord
                ? props.canEditRecord(params.data)
                : true;

            // El botón está habilitado si puede editar Y (no es completado O tiene permisos especiales)
            const isEditEnabled = canEditThisRecord;

            // Verificar si se puede agendar este registro específico
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
    minWidth: 100, // ancho mínimo recomendado
    maxWidth: 250, // ancho máximo recomendado
    cellClass: "ag-center-cols", // centra el contenido de las celdas
};

// ===== FUNCIONES DE SELECCIÓN POR ARRASTRE =====
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
    const maxSpeed = 10; // más suave

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

// ===== WATCHERS Y REACTIVIDAD =====
// Optimización: Usar debounce para evitar actualizaciones excesivas
let updateTimeout = null;

function debounceUpdate(fn, delay = 150) {
    return (...args) => {
        clearTimeout(updateTimeout);

        // OPTIMIZACIÓN ULTRA: Delays adaptativos más agresivos
        const dataSize = args[0]?.length || 0;
        let actualDelay;

        if (dataSize > 5000) actualDelay = 800; // Datasets masivos: 800ms
        else if (dataSize > 2000) actualDelay = 500; // Datasets grandes: 500ms
        else if (dataSize > 1000) actualDelay = 300; // Datasets medianos: 300ms
        else if (dataSize > 500) actualDelay = 200; // Datasets pequeños: 200ms
        else actualDelay = 100; // Datasets muy pequeños: 100ms

        updateTimeout = setTimeout(() => fn(...args), actualDelay);
    };
}

const debouncedUpdateGrid = debounceUpdate(async (newRows) => {
    if (!gridApi || !Array.isArray(newRows) || isUpdatingGrid.value) return;

    isUpdatingGrid.value = true;

    try {
        // OPTIMIZACIÓN ULTRA: markRaw para todos los datasets grandes
        const processedRows = newRows.length > 100 ? markRaw(newRows) : newRows;

        // Detectar si son nuevos (scroll infinito o reemplazo)
        const isAppending =
            newRows.length > rowDataInternal.value.length &&
            rowDataInternal.value.length > 0 &&
            newRows.length < 1000 && // Solo para datasets pequeños
            newRows
                .slice(0, Math.min(rowDataInternal.value.length, 10)) // Solo comparar primeros 10
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
            // Reemplazo completo optimizado
            rowDataInternal.value = processedRows;
            gridApi.setGridOption("rowData", processedRows);
        }

        // OPTIMIZACIÓN: Solo hacer nextTick para datasets pequeños
        if (newRows.length < 500) {
            await nextTick();
        }

        gridApi.hideOverlay();

        // OPTIMIZACIÓN ULTRA: Solo refrescar celdas si es realmente necesario
        if (newRows.length < 200) {
            gridApi.refreshCells();
        }

        // Animaciones solo para datasets muy pequeños
        if (props.viewMode === "cards" && newRows.length < 50) {
            setupDetailAnimations();
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
        // OPTIMIZACIÓN ULTRA: Comparaciones más eficientes
        if (newRows === oldRows) return;

        // Para datasets grandes, comparación ultra rápida
        const newLength = newRows?.length || 0;
        const oldLength = oldRows?.length || 0;

        if (newLength > 1000 && oldLength > 1000) {
            // Solo comparar longitud y hash del primer y último elemento
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
    { immediate: true, deep: false, flush: "post" } // flush post para mejor rendimiento
);

// Watch para configurar animaciones cuando se cambia a vista de cards
watch(
    () => props.viewMode,
    (newMode) => {
        if (newMode === "cards" && (props.rows?.length || 0) < 500) {
            nextTick(() => {
                setupDetailAnimations();
            });
        }
    }
);

// ===== MONTAJE Y EVENTOS =====
onMounted(() => {
    if (!window.agGrid?.createGrid) return;

    const gridOptions = {
        columnDefs,
        rowData: markRaw(props.rows || []),
        defaultColDef,
        rowSelection: "multiple",
        suppressRowClickSelection: false,
        rowHeight: 37,
        headerHeight: 35,
        enableRangeSelection: false,
        animateRows: false, // Desactivar animaciones para mejor rendimiento
        suppressCellFocus: true,
        // Optimizaciones de rendimiento
        suppressColumnVirtualisation: false,
        suppressRowVirtualisation: false,
        rowBuffer: 10,
        maxBlocksInCache: 10,
        maxConcurrentDatasourceRequests: 2,
        cacheBlockSize: 100,
        // Desactivar funciones que consumen recursos
        suppressAggFuncInHeader: true,
        suppressMenuHide: true,
        suppressMovableColumns: true,
        onGridReady: (params) => {
            gridApi = params.api;

            gridApi.sizeColumnsToFit();
            gridApi.addEventListener("selectionChanged", emitSelectedRows);

            if (
                params.columnApi &&
                typeof params.columnApi.getAllColumns === "function"
            ) {
                const allColumnIds = [];
                params.columnApi.getAllColumns().forEach((column) => {
                    allColumnIds.push(column.getId());
                });
                // Solo auto-size si hay pocas columnas
                if (allColumnIds.length <= 8) {
                    params.columnApi.autoSizeColumns(allColumnIds, false);
                }
            }

            // No mostrar overlay de loading de ag-Grid, el overlay propio ya se muestra
            gridApi.hideOverlay();
        },
    };

    const { createGrid } = window.agGrid;
    gridApi = createGrid(gridContainer.value, gridOptions);

    // Eventos personalizados
    gridContainer.value.addEventListener("mousedown", handleMouseDown);
    document.addEventListener("mousemove", handleMouseMove);
    document.addEventListener("mouseup", handleMouseUp);

    // Event listener para resize de ventana con throttle
    window.addEventListener("resize", throttledResize);

    // Configurar animaciones de detalles en cards solo si hay pocos datos
    if (props.viewMode === "cards" && (props.rows?.length || 0) < 500) {
        nextTick(() => {
            setupDetailAnimations();
        });
    }
});

// Función para manejar cambios de tamaño de ventana con throttle ULTRA
let resizeTimeout = null;
let resizeRequestId = null;

const throttledResize = () => {
    if (resizeTimeout) return;

    // Cancelar frame anterior si existe
    if (resizeRequestId) {
        cancelAnimationFrame(resizeRequestId);
    }

    resizeTimeout = setTimeout(() => {
        resizeRequestId = requestAnimationFrame(() => {
            handleResize();
            resizeTimeout = null;
            resizeRequestId = null;
        });
    }, 350); // Delay más largo para menos llamadas
};

function handleResize() {
    windowWidth.value = window.innerWidth;
    if (gridApi && !isUpdatingGrid.value) {
        gridApi.sizeColumnsToFit();
    }
}

// ===== ACCIONES EXTRA =====
function selectAll() {
    if (props.viewMode === "grid") {
        gridApi?.selectAll();
        emitSelectedRows();
    } else {
        // Vista tarjetas - seleccionar todas
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
        // Vista tarjetas - limpiar selección
        selectedItems.value.clear();
        emitSelectedFromCards();
    }
}

// Watcher para sincronizar cuando se cambia de vista
watch(
    () => props.viewMode,
    (newMode) => {
        if (newMode === "cards") {
            // Sincronizar selección del grid a las tarjetas
            const gridSelected = gridApi?.getSelectedRows() || [];
            selectedItems.value.clear();
            gridSelected.forEach((item) => selectedItems.value.add(item.id));
            emitSelectedFromCards();
        } else {
            // Sincronizar selección de tarjetas al grid
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

// Limpiar event listeners al desmontar
onUnmounted(() => {
    // Limpiar timeouts y frames
    if (updateTimeout) clearTimeout(updateTimeout);
    if (resizeTimeout) clearTimeout(resizeTimeout);
    if (resizeRequestId) cancelAnimationFrame(resizeRequestId);

    document.removeEventListener("mousemove", handleMouseMove);
    document.removeEventListener("mouseup", handleMouseUp);
    window.removeEventListener("resize", throttledResize);
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

/* Animaciones keyframes personalizadas para forzar el comportamiento */
@keyframes expandDetails {
    0% {
        max-height: 0;
        opacity: 0;
        padding-top: 0;
        padding-bottom: 0;
    }
    50% {
        opacity: 0.5;
    }
    100% {
        max-height: 800px;
        opacity: 1;
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }
}

@keyframes collapseDetails {
    0% {
        max-height: 800px;
        opacity: 1;
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }
    50% {
        opacity: 0.5;
    }
    100% {
        max-height: 0;
        opacity: 0;
        padding-top: 0;
        padding-bottom: 0;
    }
}

@keyframes slideDown {
    0% {
        transform: translateY(-25px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideUp {
    0% {
        transform: translateY(0);
        opacity: 1;
    }
    100% {
        transform: translateY(-25px);
        opacity: 0;
    }
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

/* Las tarjetas dentro de cada columna se mueven suavemente - ULTRA RÁPIDO */
.space-y-4 > * {
    transition: margin-top 0.25s ease-out, transform 0.25s ease-out,
        height 0.25s ease-out;
    will-change: auto; /* Cambiar a auto para mejor rendimiento */
}

/* Asegurar que las tarjetas no se expandan más allá del contenedor */
.flex-1 > div {
    width: 100%;
    overflow: hidden;
}

/* Optimización para mejor rendimiento en las animaciones - ULTRA RÁPIDO */
.space-y-4 {
    will-change: auto;
    transition: height 0.25s ease-out;
}

/* Optimización adicional para transiciones de tarjetas - ULTRA RÁPIDO */
.space-y-4 > .animated-card {
    will-change: auto;
    transform-origin: top center;
    transition: all 0.25s ease-out;
}

/* Columna de tarjetas con posición relativa para transiciones - MÁS RÁPIDO */
.card-column {
    position: relative;
    transition: height 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

/* Mejorar la respuesta de las columnas a cambios de altura - MÁS RÁPIDO */
.flex-1 {
    min-width: 0; /* Permite que flex-1 se comprima correctamente */
    transition: height 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

/* Transiciones para TransitionGroup - movimiento de tarjetas ULTRA RÁPIDO */
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

/* Columna de tarjetas con posición relativa para transiciones - ULTRA RÁPIDO */
.card-column {
    position: relative;
    transition: height 0.25s ease-out;
}

/* Clase específica para animaciones suaves de tarjetas - ULTRA RÁPIDA */
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

/* Transiciones suaves para elementos internos de las tarjetas - ULTRA RÁPIDO */
.animated-card > * {
    transition: all 0.15s ease-out;
}

/* Optimizar el espacio y movimiento suave - ULTRA RÁPIDO */
.space-y-4 {
    position: relative;
    min-height: 0;
    transition: height 0.25s ease-out;
    will-change: auto;
}

/* Eliminado no-vertical-scroll para permitir scroll vertical natural de ag-Grid */
</style>
