<template>
    <div>
        <!-- Botones de selección -->
        <div
            class="flex gap-2 flex-wrap"
            style="background-color: white; border: none"
        >
            <button
                @click="selectAll"
                class="btn bgPrincipal btnTablas flex gap-2 itens-center"
            >
                <!-- Icono seleccionar todo -->
                <svg
                    class="w-5 h-5"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 448 512"
                >
                    <!--! Font Awesome Pro 7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2025 Fonticons, Inc. -->
                    <path
                        fill="currentColor"
                        d="M64 64C46.3 64 32 78.3 32 96l0 320c0 17.7 14.3 32 32 32l320 0c17.7 0 32-14.3 32-32l0-320c0-17.7-14.3-32-32-32L64 64zM0 96C0 60.7 28.7 32 64 32l320 0c35.3 0 64 28.7 64 64l0 320c0 35.3-28.7 64-64 64L64 480c-35.3 0-64-28.7-64-64L0 96zM301.6 200.5l-80 128c-2.8 4.5-7.6 7.3-12.9 7.5s-10.3-2.2-13.5-6.4l-48-64c-5.3-7.1-3.9-17.1 3.2-22.4s17.1-3.9 22.4 3.2l34 45.3 67.6-108.2c4.7-7.5 14.6-9.8 22-5.1s9.8 14.6 5.1 22z"
                    />
                </svg>
                Seleccionar todo
            </button>
            <button
                @click="clearSelection"
                class="btn bgPrincipal btnTablas flex gap-2 itens-center"
            >
                <!-- Icono limpiar selección -->
                <svg
                    class="w-5 h-5"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 640 640"
                >
                    <!--! Font Awesome Pro 7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2025 Fonticons, Inc. -->
                    <path
                        fill="currentColor"
                        d="M605.7 45.7C608.8 42.6 608.8 37.5 605.7 34.4C602.6 31.3 597.5 31.3 594.4 34.4L378.4 250.4L354.2 226.2C334 206 302.8 201.6 277.9 215.5L48.4 342.9C38.3 348.5 32 359.2 32 370.8C32 379.2 35.4 387.4 41.3 393.3L246.7 598.7C252.7 604.7 260.8 608 269.3 608C280.9 608 291.6 601.7 297.2 591.6L424.6 362.2C438.5 337.2 434.1 306.1 413.9 285.9L389.7 261.7L605.7 45.7zM342.8 237.4L402.5 297.1C417.7 312.3 420.9 335.6 410.5 354.4L383.1 403.8L236.1 256.9L285.5 229.5C304.2 219.1 327.6 222.4 342.8 237.5zM221.6 264.9L375 418.4L283.1 583.8C280.3 588.9 275 592 269.2 592C265 592 260.9 590.3 257.9 587.3L128.9 458.3L189.6 397.6C192.7 394.5 192.7 389.4 189.6 386.3C186.5 383.2 181.4 383.2 178.3 386.3L117.6 447L52.6 382C49.6 379 47.9 375 47.9 370.7C47.9 364.9 51 359.6 56.1 356.8L221.5 264.9z"
                    />
                </svg>
                Limpiar
            </button>
        </div>
        <!-- Grid principal -->
        <div
            ref="gridContainer"
            id="myGrid"
            class="ag-theme-alpine w-full h-[700px] rounded-lg shadow border"
        ></div>
    </div>
</template>

<script setup>
// ===== IMPORTS =====
import { onMounted, ref, watch, h, render, nextTick } from "vue";
import Actions from "@/Components/Actions.vue";

// ===== PROPS Y EMITS =====
const props = defineProps({
    rows: Array,
    columns: Array, // ← Nueva prop para columnas personalizadas
    canViewGlobal: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
    canList: Boolean,
});

const emit = defineEmits(["edit", "delete", "list", "update:selected"]);

// ===== VARIABLES REACTIVAS =====
const gridContainer = ref(null);
let gridApi = null;

// ===== DEFINICIÓN DE COLUMNAS =====
const columnDefs = [
    { field: "id", headerName: "ID" },
    { field: "upload_id", headerName: "Nro. Carga" },
    {
        valueGetter: (params) => params.data?.created_at_formatted || "",
        headerName: "Fecha de Carga",
    },

    // Datos base del registro
    { field: "trazabilidad", headerName: "Trazabilidad" },
    {
        headerName: "Asignado",
        valueGetter: (params) => params.data?.asignado_a?.name || "—",
    },

    // Nuevos campos SmartClient y área de filtrado

    { field: "marca_base", headerName: "Marca de la Base" },
    { field: "origen_motivo_cancelacion", headerName: "Origen Cancelación" },
    { field: "nombre_cliente", headerName: "Nombre del Cliente" },
    { field: "dni_cliente", headerName: "DNI Cliente" },
    { field: "orden_trabajo_anterior", headerName: "Orden Trabajo Anterior" },
    { field: "telefono_principal", headerName: "Teléfono Principal" },
    { field: "telefono_adicional", headerName: "Teléfono Adicional" },
    { field: "correo_referencia", headerName: "Correo de Referencia" },
    { field: "direccion_historico", headerName: "Dirección Histórico" },
    { field: "observaciones", headerName: "Observaciones" },
];

if (props.canViewGlobal) {
    columnDefs.push({
        headerName: "Usuario",
        valueGetter: (p) => p.data.user?.name || "Sin usuario",
    });
}

columnDefs.push({
    headerName: "Acciones",
    field: "acciones",
    pinned: "right",
    cellRenderer: (params) => {
        const container = document.createElement("div");
        container.className = "flex items-center justify-center gap-1 h-full";

        const vnode = h(Actions, {
            edit: props.canEdit,
            remove: props.canDelete,
            onEdit: () => emit("edit", params.data),
            onDelete: () => emit("delete", params.data),
        });

        render(vnode, container);
        return container;
    },
});

const defaultColDef = {
    resizable: true,
    flex: 1,
    sortable: false,
    minWidth: 120, // ancho mínimo recomendado
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
            gridApi.showLoadingOverlay();
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

            // Mostrar loading si no hay datos
            if (!props.rows || props.rows.length === 0) {
                gridApi.showLoadingOverlay();
            }
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
.ag-theme-alpine {
    min-height: 700px;
    font-size: 13px;
    --ag-header-background-color: #f8f9fa;
    --ag-header-foreground-color: #333;
    --ag-selected-row-background-color: #ffcc99; /* ← naranja suave */
}
</style>
