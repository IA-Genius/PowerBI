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
        <!-- Grid principal -->
        <div class="relative">
            <div
                ref="gridContainer"
                id="myGrid"
                :class="'ag-theme-alpine w-full rounded-lg shadow border'"
                :style="{ height: gridHeightStyle.height }"
            ></div>
            <transition name="fade">
                <div
                    v-if="isLoading"
                    class="absolute inset-0 z-30 flex flex-col items-center justify-center backdrop-blur-sm bg-black/40 animate__animated animate__fadeIn"
                    style="pointer-events: none"
                >
                    <div class="flex flex-col items-center gap-3">
                        <svg
                            class="animate-spin h-10 w-10 text-indigo-500"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                            ></path>
                        </svg>
                        <span
                            class="text-indigo-600 font-semibold text-lg text-center drop-shadow"
                        >
                            Cargando registros...
                        </span>
                        <span class="text-gray-300 text-xs text-center">
                            Por favor espera, obteniendo registros.
                        </span>
                    </div>
                </div>
            </transition>
            <transition name="fade">
                <div
                    v-if="
                        !isLoading && (!props.rows || props.rows.length === 0)
                    "
                    class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 animate__animated animate__fadeIn"
                    style="pointer-events: none"
                >
                    <div class="flex flex-col items-center gap-2">
                        <svg
                            class="h-10 w-10 text-gray-300"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span
                            class="text-gray-500 font-semibold text-base text-center"
                            >Sin registros para mostrar</span
                        >
                    </div>
                </div>
            </transition>
        </div>
    </div>
</template>

<script setup>
// ===== IMPORTS =====
import { onMounted, ref, watch, h, render, nextTick } from "vue";
import Actions from "@/Components/Actions.vue";

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
    canEditRecord: Function, // <-- Nueva prop para función de validación
    canEditComplete: Boolean, // <-- Nueva prop para indicar si se puede editar completamente
});

const emit = defineEmits([
    "edit",
    "delete",
    "list",
    "update:selected",
    "showHistory",
]);

// ===== VARIABLES REACTIVAS =====
const gridContainer = ref(null);
let gridApi = null;

// ===== Altura dinámica del grid =====
import { computed } from "vue";
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
                    maxWidth: 60,
                    width: 50,
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
    props.canEditComplete;
if (hasActions) {
    columnDefs.push({
        headerName: "Acciones",
        field: "acciones",
        pinned: "right",
        minWidth: 120,
        maxWidth: 140,
        width: 130,
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

            const vnode = h(Actions, {
                edit: props.canEdit,
                editDisabled: props.canEdit && !isEditEnabled,
                remove: props.canDelete,
                list: props.canList,
                canViewHistory: props.canViewHistory,
                onEdit: () => emit("edit", params.data),
                onDelete: () => emit("delete", params.data),
                onHistory: () => emit("showHistory", params.data),
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
const rowDataInternal = ref([]);

watch(
    () => props.rows,
    async (newRows) => {
        if (!gridApi || !Array.isArray(newRows)) return;

        // Detectar si son nuevos (scroll infinito o reemplazo)
        const isAppending =
            newRows.length > rowDataInternal.value.length &&
            rowDataInternal.value.length > 0 &&
            newRows
                .slice(0, rowDataInternal.value.length)
                .every((item, i) => item.id === rowDataInternal.value[i].id);

        if (!newRows || newRows.length === 0) {
            rowDataInternal.value = [];
            gridApi.setGridOption("rowData", []);
            gridApi.hideOverlay();
        } else if (isAppending) {
            const added = newRows.slice(rowDataInternal.value.length);
            rowDataInternal.value = [...rowDataInternal.value, ...added];
            gridApi.applyTransaction({ add: added });
        } else {
            // Reemplazo completo (filtro nuevo, búsqueda, etc.)
            rowDataInternal.value = [...newRows];
            gridApi.setGridOption("rowData", newRows);
        }

        await nextTick();
        gridApi.hideOverlay();
        gridApi.refreshCells();
    },
    { immediate: true }
);

// ===== MONTAJE Y EVENTOS =====
onMounted(() => {
    if (!window.agGrid?.createGrid) return;

    const gridOptions = {
        columnDefs,
        rowData: props.rows,
        defaultColDef,
        rowSelection: "multiple",
        suppressRowClickSelection: false,
        rowHeight: 37,
        headerHeight: 35,
        enableRangeSelection: false,
        animateRows: true,
        suppressCellFocus: true,
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
                params.columnApi.autoSizeColumns(allColumnIds, false);
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
});

// ===== ACCIONES EXTRA =====
function selectAll() {
    gridApi?.selectAll();
    emitSelectedRows();
}
function clearSelection() {
    gridApi?.deselectAll();
    emitSelectedRows();
}
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
/* Eliminado no-vertical-scroll para permitir scroll vertical natural de ag-Grid */
</style>
