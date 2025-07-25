<template>
    <div>
        <div class="mb-2 flex gap-2 flex-wrap">
            <button @click="selectAll" class="btn">🔄 Seleccionar todo</button>
            <button @click="clearSelection" class="btn">❌ Limpiar</button>
        </div>

        <div
            ref="gridContainer"
            id="myGrid"
            class="ag-theme-alpine w-full h-[700px] rounded-lg shadow border"
        ></div>
    </div>
</template>

<script setup>
import { onMounted, ref, watch, h, render } from "vue";

import Actions from "@/Components/Actions.vue";

const props = defineProps({
    rows: Array,
    columns: Array, // ← Nueva prop para columnas personalizadas
    canViewGlobal: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
    canList: Boolean,
});

const emit = defineEmits(["edit", "delete", "list"]);
const gridContainer = ref(null);
let gridApi = null;

const columnDefs = [
    { field: "id", headerName: "ID" },
    { field: "upload_id", headerName: "Nro. Carga" },
    {
        field: "created_at",
        headerName: "Fecha de Carga",
        valueFormatter: (p) =>
            p.value ? new Date(p.value).toLocaleString() : "—",
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

    // Auditoría
    {
        field: "updated_at",
        headerName: "Última Modificación",
        valueFormatter: (p) =>
            p.value ? new Date(p.value).toLocaleString() : "—",
    },
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
    width: 120,
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
};

// 🖱️ Selección por arrastre
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

    const viewport = gridContainer.value.querySelector(".ag-body-viewport");
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

// 🔁 Inicializar AG Grid
onMounted(() => {
    if (!window.agGrid?.createGrid) return;

    const gridOptions = {
        columnDefs,
        rowData: props.rows,
        defaultColDef,
        rowSelection: "multiple",
        suppressRowClickSelection: false,
        // suppressCellSelection: true, // ❌ quitar esto
        enableRangeSelection: false,
        animateRows: true,
        suppressCellFocus: true,
        onGridReady: (params) => {
            gridApi = params.api;
            gridApi.sizeColumnsToFit();
            gridApi.addEventListener("selectionChanged", emitSelectedRows);
        },
    };

    const { createGrid } = window.agGrid;
    gridApi = createGrid(gridContainer.value, gridOptions);

    // Eventos personalizados
    gridContainer.value.addEventListener("mousedown", handleMouseDown);
    document.addEventListener("mousemove", handleMouseMove);
    document.addEventListener("mouseup", handleMouseUp);
});

// 🔄 Reactividad en datos
watch(
    () => props.rows,
    (newRows) => {
        if (gridApi) gridApi.setRowData(newRows);
    }
);

// 🔘 Acciones extra
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
    font-size: 13px;
    --ag-header-background-color: #f8f9fa;
    --ag-header-foreground-color: #333;
    --ag-selected-row-background-color: #e3f2fd;
}
</style>
