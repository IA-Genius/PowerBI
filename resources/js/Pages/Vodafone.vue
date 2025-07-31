<script setup>
// =======================
// 1. IMPORTS
// =======================
import { ref, computed, onMounted, watch, watchEffect } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ModalGestion from "@/Components/ModalGestion.vue";
import InputField from "@/Components/InputField.vue";
import Dropdown from "@/Components/Dropdown.vue";
import FiltroFlotante from "@/Components/FiltroFlotante.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import ModalImportacion from "@/Components/ModalImportacion.vue";
import ExcelLikeGrid from "@/Components/ExcelLikeGrid.vue";
import axios from "axios";
import HistorialAsignaciones from "@/Components/HistorialAsignaciones.vue";

// =======================
// 2. COMPOSABLES Y UTILIDADES
// =======================
const pageProps = usePage().props;
const can = pageProps.can || {};

// Función helper para verificar permisos
const canDo = (key) => !!can[key];

// =======================
// 3. PROPIEDADES REACTIVAS - ESTADO PRINCIPAL
// =======================
const items = ref([]);
const selectedRows = ref([]);
const currentPage = ref(1);
const lastPage = ref(1);
const isLoading = ref(false);
const scrollContainer = ref(null);

// =======================
// 4. PROPIEDADES REACTIVAS - MODALES
// =======================
const modales = ref({
    showModal: false,
    showInportarModal: false,
    showAsignacionModal: false,
    showHistorialModal: false,
});

const modalData = ref({
    registroEditar: null,
    InportarTitulo: null,
    asignacionTitulo: null,
    generalError: null,
    historialSeleccionado: [],
    registroHistorial: null,
});

// =======================
// 5. PROPIEDADES REACTIVAS - FORMULARIOS
// =======================
const form = ref({
    nombre_cliente: "",
    dni_cliente: "",
    telefono_principal: "",
    telefono_adicional: "",
    correo_referencia: "",
    direccion_historico: "",
    marca_base: "",
    origen_motivo_cancelacion: "",
    orden_trabajo_anterior: "",
    observaciones: "",
});

// =======================
// 6. PROPIEDADES REACTIVAS - FILTROS
// =======================
const filtros = ref({
    search: "",
    showFiltro: false,
    fechaDesde: pageProps.fechaDesde || "",
    fechaHasta: pageProps.fechaHasta || "",
    filtrosActivos: {},
});

// =======================
// 7. PROPIEDADES REACTIVAS - IMPORTACIÓN
// =======================
const importacion = ref({
    excelFile: null,
    fileName: null,
    allPreviewRows: [],
    previewRows: [],
    modoDuplicados: "omitir",
    importStatus: "",
    importError: "",
    totalRegistros: 0,
    totalDuplicados: 0,
    totalNuevos: 0,
    previewTruncado: false,
    previewTotal: 0,
    formImportar: { archivo: null },
    progress: 0,
    importando: false,
    isLoadingAsignacion: false,
});

// =======================
// 8. PROPIEDADES COMPUTADAS - PERMISOS
// =======================
const permisos = computed(() => ({
    success: pageProps.success,
    canViewGlobal: pageProps.canViewGlobal,
    canViewHistory: canDo("vodafone.ver-historial"),
    canFiltrar: canDo("vodafone.filtrar"),
    usuariosAsignables: pageProps.usuariosAsignables || [],
    // Filtradores sin asignación no pueden cambiar fechas
    puedeSeleccionarFechas: !(
        canDo("vodafone.filtrar") && !canDo("vodafone.asignar")
    ),
}));

// =======================
// 9. PROPIEDADES COMPUTADAS - DATOS PROCESADOS
// =======================
const rawItems = computed(() => items.value);

const filtrosDisponibles = computed(() => {
    const list = rawItems.value;
    return getFiltrosDisponibles(list);
});

const filteredItems = computed(() => {
    const rawItemsData = rawItems.value;
    return getItemsFiltrados(rawItemsData);
});

const columnasGrid = computed(() => {
    return getColumnasGrid();
});

const activeFilterOptions = computed(() => {
    return getActiveFilterOptions();
});
// =======================
// 10. FUNCIONES AUXILIARES - INICIALIZACIÓN
// =======================
function inicializarItems() {
    items.value = Array.isArray(pageProps.items) ? pageProps.items : [];
    currentPage.value = 1;
    lastPage.value = 1;
}

function getFiltrosIniciales() {
    // Para usuarios sin permisos específicos, no aplicar filtros automáticos
    if (!canDo("vodafone.asignar") && !permisos.value.canFiltrar) {
        return {};
    }

    try {
        const saved = localStorage.getItem("vodafoneFiltros");
        if (saved) {
            const parsedFilters = JSON.parse(saved);
            return aplicarRestriccionesFiltros(parsedFilters);
        }
    } catch (e) {
        console.warn("Error parseando localStorage:", e);
    }

    // Valores por defecto según rol
    if (canDo("vodafone.asignar")) {
        return { trazabilidad: ["pendiente"] };
    } else if (permisos.value.canFiltrar) {
        return { trazabilidad: ["asignado", "completado"] };
    }

    // Sin filtros por defecto para otros usuarios
    return {};
}

function aplicarRestriccionesFiltros(filtros) {
    if (permisos.value.canFiltrar && !canDo("vodafone.asignar")) {
        // Filtrador sin permisos de asignar
        const opcionesPermitidas = ["asignado", "completado"];

        if (filtros.trazabilidad && Array.isArray(filtros.trazabilidad)) {
            // Filtrar solo opciones permitidas
            filtros.trazabilidad = filtros.trazabilidad.filter((opcion) =>
                opcionesPermitidas.includes(opcion)
            );
        } else if (!filtros.trazabilidad) {
            // Solo aplicar filtros por defecto si no existe el campo trazabilidad
            filtros.trazabilidad = ["asignado", "completado"];
        }

        // Si quedó vacía después del filtro, usar valores por defecto
        if (
            Array.isArray(filtros.trazabilidad) &&
            filtros.trazabilidad.length === 0
        ) {
            filtros.trazabilidad = ["asignado", "completado"];
        }

        // Limpiar otros filtros no permitidos para filtradores
        delete filtros.upload_id;
    }
    return filtros;
}

// =======================
// 11. FUNCIONES AUXILIARES - FILTROS Y BÚSQUEDA
// =======================
function getFiltrosDisponibles(list) {
    let trazabilidadOpciones = [];

    if (canDo("vodafone.asignar")) {
        trazabilidadOpciones = [
            "pendiente",
            "asignado",
            "irrelevante",
            "completado",
            "retornado",
        ];
    } else if (permisos.value.canFiltrar) {
        trazabilidadOpciones = ["asignado", "completado"];
    } else {
        trazabilidadOpciones = [
            "pendiente",
            "asignado",
            "irrelevante",
            "completado",
            "retornado",
        ];
    }

    const filtros = {
        operador_actual: [
            ...new Set(list.map((i) => i.operador_actual).filter(Boolean)),
        ],
        trazabilidad: trazabilidadOpciones,
        ...(canDo("vodafone.asignar") && {
            upload_id: [
                ...new Set(list.map((i) => i.upload_id).filter(Boolean)),
            ]
                .sort((a, b) => b - a)
                .slice(0, 15)
                .sort((a, b) => a - b),
        }),
    };

    return Object.fromEntries(
        Object.entries(filtros).filter(
            ([key, arr]) =>
                key === "trazabilidad" || (Array.isArray(arr) && arr.length > 0)
        )
    );
}

function getItemsFiltrados(rawItems) {
    const filtrosActivos = filtros.value.filtrosActivos || {};
    const searchValue = filtrosActivos.search;
    const term =
        (typeof searchValue === "string" ? searchValue.toLowerCase() : "") ||
        "";
    let data = [...rawItems];

    if (term) {
        data = data.filter((item) =>
            [
                item.nombre_cliente,
                item.dni_cliente,
                item.telefono_principal,
            ].some((field) =>
                (field || "").toString().toLowerCase().includes(term)
            )
        );
    }

    for (const [key, values] of Object.entries(filtrosActivos)) {
        if (key === "search" || !Array.isArray(values) || values.length === 0)
            continue;

        data = data.filter((item) => values.includes(item[key]));
    }

    return data;
}

function getColumnasGrid() {
    const columnasBase = [
        "id",
        "created_at_formatted",
        "trazabilidad",
        "marca_base",
        "origen_motivo_cancelacion",
        "nombre_cliente",
        "dni_cliente",
        "orden_trabajo_anterior",
        "telefono_principal",
        "telefono_adicional",
        "correo_referencia",
        "direccion_historico",
        "observaciones",
    ];

    if (canDo("vodafone.asignar")) {
        return [
            "id",
            "upload_id",
            ...columnasBase.slice(1),
            "asignado_a",
            "user",
        ];
    }

    if (permisos.value.canFiltrar) {
        return columnasBase;
    }

    if (canDo("vodafone.recibe-asignacion")) {
        return ["id", "trazabilidad", "asignado_a", ...columnasBase.slice(3)];
    }

    return columnasBase;
}

function getActiveFilterOptions() {
    const filtrosData = filtros.value.filtrosActivos || {};
    let opciones = [];

    Object.keys(filtrosData).forEach((k) => {
        if (
            k !== "search" &&
            Array.isArray(filtrosData[k]) &&
            filtrosData[k].length > 0
        ) {
            opciones = opciones.concat(filtrosData[k]);
        }
    });

    return opciones;
} // =======================
// 12. FUNCIONES DE MODALES - GESTIÓN
// =======================
function abrirModalAgregar() {
    resetearModal();
    form.value = {
        nombre_cliente: "",
        dni_cliente: "",
        telefono_principal: "",
        telefono_adicional: "",
        correo_referencia: "",
        direccion_historico: "",
        marca_base: "",
        origen_motivo_cancelacion: "",
        orden_trabajo_anterior: "",
        observaciones: "",
        trazabilidad: "pendiente",
    };
    modales.value.showModal = true;
}

function abrirModalEditar(item) {
    modalData.value.registroEditar = item;
    form.value = { ...item };
    modales.value.showModal = true;
    modalData.value.generalError = null;
}

function abrirModalInportar() {
    modales.value.showInportarModal = true;
    resetearImportacion();
}

function abrirModalAsignacion() {
    if (selectedRows.value.length === 0) {
        mostrarAlerta(
            "warning",
            "Ningún registro seleccionado",
            "Por favor selecciona al menos un registro para asignar."
        );
        return;
    }
    modales.value.showAsignacionModal = true;
}

function abrirHistorial(item) {
    modalData.value.historialSeleccionado = item.asignaciones_historial || [];
    modalData.value.registroHistorial = item;
    modales.value.showHistorialModal = true;
}

function cerrarModal() {
    Object.keys(modales.value).forEach((key) => {
        modales.value[key] = false;
    });
    resetearModal();
}

function cerrarHistorial() {
    modales.value.showHistorialModal = false;
    modalData.value.historialSeleccionado = [];
    modalData.value.registroHistorial = null;
}

function resetearModal() {
    modalData.value.registroEditar = null;
    modalData.value.InportarTitulo = null;
    modalData.value.asignacionTitulo = null;
    modalData.value.generalError = null;
}

function resetearImportacion() {
    Object.assign(importacion.value, {
        fileName: null,
        excelFile: null,
        allPreviewRows: [],
        previewRows: [],
        modoDuplicados: "omitir",
        importStatus: "",
        importError: "",
        totalRegistros: 0,
        totalDuplicados: 0,
        totalNuevos: 0,
        progress: 0,
        formImportar: { archivo: null },
        previewTruncado: false,
        previewTotal: 0,
    });
}

// =======================
// 13. FUNCIONES DE CRUD Y UTILIDADES
// =======================
function handleSuccess(msg) {
    mostrarToast("success", msg);
    cerrarModal();
    router.visit(route("vodafone.index"), { only: ["items", "success"] });
}

function eliminar(item) {
    Swal.fire({
        title: "¿Eliminar registro?",
        text: `¿Estás seguro de eliminar a ${item.nombre_cliente}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
    }).then((res) => {
        if (res.isConfirmed) {
            router.delete(`/vodafone/${item.id}`, {
                onSuccess: () => handleSuccess("Registro eliminado"),
                onError: () => mostrarToast("error", "Error al eliminar"),
            });
        }
    });
}

function mostrarInfoUsuario(user) {
    Swal.fire({
        title: user.name,
        html: `<b>Email:</b> ${user.email}<br><b>Rol:</b> ${
            user.roles?.map((r) => r.name).join(", ") || "Sin rol"
        }`,
        icon: "info",
    });
}

function canEditRecord(record) {
    // Verificar permisos básicos de edición
    if (!canDo("vodafone.editar")) {
        return false;
    }

    // Si el registro está completado, verificar permisos especiales
    if (record?.trazabilidad === "completado") {
        return canDo("vodafone.editar-completados");
    }

    return true;
}

// =======================
// 14. FUNCIONES DE FILTROS
// =======================
function aplicarBusquedaInstantanea(val) {
    filtros.value.filtrosActivos = {
        ...filtros.value.filtrosActivos,
        search: val,
    };
}

async function aplicarFiltrosDesdeFlotante(f) {
    // Aplicar restricciones a los filtros recibidos
    const filtrosFiltrados = aplicarRestriccionesFiltros({ ...f });

    filtros.value.filtrosActivos = {
        ...filtros.value.filtrosActivos,
        ...filtrosFiltrados,
    };
    if (f.fecha_desde !== undefined) filtros.value.fechaDesde = f.fecha_desde;
    if (f.fecha_hasta !== undefined) filtros.value.fechaHasta = f.fecha_hasta;

    isLoading.value = true;
    try {
        const response = await axios.get(route("vodafone.page"), {
            params: {
                ...filtros.value.filtrosActivos,
                fecha_desde: filtros.value.fechaDesde,
                fecha_hasta: filtros.value.fechaHasta,
            },
        });
        items.value = response.data.items;
    } catch (e) {
        mostrarAlerta("error", "Error", "No se pudo filtrar por fecha");
    } finally {
        isLoading.value = false;
    }
}

// =======================
// 15. FUNCIONES DE IMPORTACIÓN
// =======================
function handleFileUpload(event) {
    const file = event.target.files[0];
    if (file) {
        importacion.value.fileName = file.name;
        importacion.value.excelFile = file;
        importacion.value.formImportar.archivo = file;
    }
}

async function importarArchivo(_, close) {
    try {
        importacion.value.importStatus = "Subiendo archivo...";
        importacion.value.importError = "";
        importacion.value.progress = 0;

        const payload = new FormData();
        const archivo = importacion.value.formImportar.archivo;

        if (!(archivo instanceof File)) {
            importacion.value.importError =
                "Debes seleccionar un archivo Excel válido.";
            return;
        }

        payload.append("archivo", archivo);
        payload.append(
            "descripcion",
            importacion.value.formImportar.descripcion
        );

        const response = await axios.post(route("vodafone.preview"), payload, {
            headers: { "Content-Type": "multipart/form-data" },
            onUploadProgress: (event) => {
                if (event.lengthComputable) {
                    importacion.value.progress = Math.round(
                        (event.loaded * 100) / event.total
                    );
                }
            },
        });

        procesarRespuestaImportacion(response);
    } catch (error) {
        manejarErrorImportacion(error);
    }
}
console.log("props de la pagina", pageProps);
function procesarRespuestaImportacion(response) {
    importacion.value.importStatus = "Procesando archivo...";
    importacion.value.progress = 100;
    importacion.value.previewTruncado = !!response.data.truncado;
    importacion.value.previewTotal = response.data.total || 0;
    importacion.value.totalRegistros = response.data.total_registros || 0;
    importacion.value.totalDuplicados = response.data.total_duplicados || 0;
    importacion.value.totalNuevos = response.data.total_nuevos || 0;

    if (Array.isArray(response.data?.preview)) {
        importacion.value.allPreviewRows = response.data.preview;
        importacion.value.previewRows = response.data.preview.filter(
            (r) => r.duplicado === true
        );
        importacion.value.importStatus = `Se analizaron ${importacion.value.totalRegistros} registros`;
    } else {
        importacion.value.allPreviewRows = [];
        importacion.value.previewRows = [];
    }
}

function manejarErrorImportacion(error) {
    importacion.value.importError =
        error.response?.data?.message || "Error al importar archivo";
    importacion.value.importStatus = "";
    importacion.value.progress = 0;
    importacion.value.previewTruncado = false;
    importacion.value.previewTotal = 0;
}

function confirmarImportacion() {
    if (importacion.value.previewRows.length > 0) {
        Swal.fire({
            title: "Duplicados detectados",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Reemplazar duplicados",
            cancelButtonText: "Omitir duplicados",
            reverseButtons: true,
        }).then((result) => {
            importacion.value.modoDuplicados = result.isConfirmed
                ? "actualizar"
                : "omitir";
            enviarImportacion();
        });
    } else {
        importacion.value.modoDuplicados = null;
        enviarImportacion();
    }
}

async function enviarImportacion() {
    const datosAEnviar = Array.isArray(importacion.value.allPreviewRows)
        ? importacion.value.allPreviewRows
        : Object.values(importacion.value.allPreviewRows);

    importacion.value.importando = true;
    importacion.value.importStatus = "Procesando importación...";

    try {
        const response = await axios.post(
            route("vodafone.importarConfirmado"),
            {
                datos: datosAEnviar,
                modo: importacion.value.modoDuplicados,
                descripcion: importacion.value.formImportar.descripcion,
            }
        );
        const logId = response.data.log_id;
        await esperarImportacion(logId);
    } catch (error) {
        importacion.value.importError =
            error.response?.data?.message || "Error al importar";
        importacion.value.importando = false;
        importacion.value.importStatus = "";
    }
}

async function esperarImportacion(logId) {
    let intentos = 0;
    const maxIntentos = 60;

    while (intentos < maxIntentos) {
        intentos++;
        try {
            const resp = await axios.get(
                route("vodafone.obtenerErroresLog", logId)
            );
            const estado = resp.data.estado;
            const errores = resp.data.errores ?? [];

            if (estado === "finalizado") {
                importacion.value.importando = false;
                importacion.value.importStatus = "";

                if (errores.length) {
                    importacion.value.importError =
                        "Importación completada con errores.";
                } else {
                    mostrarToast(
                        "success",
                        "Los registros se importaron correctamente."
                    );
                    handleSuccess("Importación realizada correctamente.");
                }
                return;
            } else if (estado === "procesando") {
                importacion.value.importStatus = "Procesando registros...";
            } else {
                importacion.value.importStatus =
                    "Esperando a que inicie el procesamiento...";
            }
        } catch (e) {
            console.warn("Error verificando estado:", e);
        }

        await new Promise((r) => setTimeout(r, 1000));
    }

    importacion.value.importando = false;
    importacion.value.importStatus = "";
    importacion.value.importError = "La importación está tardando demasiado.";
}

// =======================
// 16. FUNCIONES DE ASIGNACIÓN
// =======================
async function asignarRegistros(slotForm) {
    importacion.value.isLoadingAsignacion = true;
    try {
        await axios.post(route("vodafone.asignar"), {
            ids: slotForm.ids,
            asignado_a_id: slotForm.asignado_a_id,
        });
        handleSuccess("Registros asignados correctamente.");
        cerrarModal();
    } catch (e) {
        mostrarAlerta("error", "Error", "No se pudo asignar registros");
    } finally {
        importacion.value.isLoadingAsignacion = false;
    }
}

// =======================
// 17. FUNCIONES DE UTILIDAD
// =======================
function mostrarToast(icon, title) {
    Swal.fire({
        toast: true,
        icon,
        title,
        position: "top-end",
        timer: 2000,
        showConfirmButton: false,
    });
}

function mostrarAlerta(icon, title, text) {
    Swal.fire({
        icon,
        title,
        text,
        confirmButtonText: "Entendido",
    });
}

// =======================
// 18. WATCHERS Y CICLO DE VIDA
// =======================
watch(() => pageProps.items, inicializarItems);

watch(
    () => importacion.value.fileName,
    () => {
        importacion.value.formImportar.archivo = importacion.value.excelFile;
    }
);

watch(
    () => filtros.value.filtrosActivos,
    (val) => {
        if (!val) return; // Evitar problemas si aún no está inicializado

        if (canDo("vodafone.asignar") || permisos.value.canFiltrar) {
            try {
                let filtrosAGuardar = aplicarRestriccionesFiltros({ ...val });
                localStorage.setItem(
                    "vodafoneFiltros",
                    JSON.stringify(filtrosAGuardar)
                );
            } catch (e) {
                console.warn("Error guardando en localStorage:", e);
            }
        }
    },
    { deep: true }
);

watchEffect(() => {
    if (modales.value.showModal && modalData.value.generalError) {
        mostrarAlerta(
            "error",
            "No se puede editar",
            modalData.value.generalError
        );
        modales.value.showModal = false;
        modalData.value.registroEditar = null;
    }
});

onMounted(() => {
    // Inicializar filtros activos
    const filtrosIniciales = getFiltrosIniciales();

    filtros.value.filtrosActivos = filtrosIniciales;

    inicializarItems();
    if (permisos.value.success) {
        mostrarToast("success", permisos.value.success);
    }
    if (pageProps.errors && pageProps.errors.general) {
        modalData.value.generalError = pageProps.errors.general;
    }
}); // =======================
// 19. EXPOSICIÓN DE PROPIEDADES COMPUTADAS PARA EL TEMPLATE
// =======================
// Props individuales para compatibilidad con template existente
const success = computed(() => permisos.value.success);
const canViewGlobal = computed(() => permisos.value.canViewGlobal);
const canViewHistory = computed(() => permisos.value.canViewHistory);
const canFiltrar = computed(() => permisos.value.canFiltrar);
const usuariosAsignables = computed(() => permisos.value.usuariosAsignables);
const puedeSeleccionarFechas = computed(
    () => permisos.value.puedeSeleccionarFechas
);

// Estados de modales individuales para template
const showModal = computed(() => modales.value.showModal);
const showInportarModal = computed(() => modales.value.showInportarModal);
const showAsignacionModal = computed(() => modales.value.showAsignacionModal);
const showHistorialModal = computed(() => modales.value.showHistorialModal);

// Datos de modal individuales
const registroEditar = computed(() => modalData.value.registroEditar);
const InportarTitulo = computed(() => modalData.value.InportarTitulo);
const asignacionTitulo = computed(() => modalData.value.asignacionTitulo);
const generalError = computed(() => modalData.value.generalError);
const historialSeleccionado = computed(
    () => modalData.value.historialSeleccionado
);
const registroHistorial = computed(() => modalData.value.registroHistorial);

// Estados de filtros individuales
const search = computed(() => filtros.value.search);
const showFiltro = computed({
    get: () => filtros.value.showFiltro,
    set: (val) => (filtros.value.showFiltro = val),
});
const fechaDesde = computed(() => filtros.value.fechaDesde);
const fechaHasta = computed(() => filtros.value.fechaHasta);
const filtrosActivos = computed(() => filtros.value.filtrosActivos);

// Estados de importación individuales
const excelFile = computed(() => importacion.value.excelFile);
const fileName = computed(() => importacion.value.fileName);
const allPreviewRows = computed(() => importacion.value.allPreviewRows);
const previewRows = computed(() => importacion.value.previewRows);
const modoDuplicados = computed(() => importacion.value.modoDuplicados);
const importStatus = computed(() => importacion.value.importStatus);
const importError = computed(() => importacion.value.importError);
const totalRegistros = computed(() => importacion.value.totalRegistros);
const totalDuplicados = computed(() => importacion.value.totalDuplicados);
const totalNuevos = computed(() => importacion.value.totalNuevos);
const previewTruncado = computed(() => importacion.value.previewTruncado);
const previewTotal = computed(() => importacion.value.previewTotal);
const formImportar = computed(() => importacion.value.formImportar);
const progress = computed(() => importacion.value.progress);
const importando = computed(() => importacion.value.importando);
const isLoadingAsignacion = computed(
    () => importacion.value.isLoadingAsignacion
);
</script>

<!-- =======================
     TEMPLATE
======================= -->
<template>
    <Head title="Vodafone" />
    <AuthenticatedLayout class="relleno">
        <template #header>
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
            >
                <h2 class="text-xl font-semibold tituloPag">
                    Historial de Registros
                </h2>
                <div class="flex flex-col sm:flex-row gap-4 items-center">
                    <!-- Filtro de Fechas -->

                    <!-- Botón y modal de filtros -->
                    <div
                        class="modalFiltros"
                        v-if="canDo('vodafone.asignar') || canFiltrar"
                    >
                        <button
                            @click="showFiltro = !showFiltro"
                            class="flex items-center h-full gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-md shadow hover:bg-gray-50 transition"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 text-indigo-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707l-6.414 6.414A1 1 0 0013 13v5a1 1 0 01-.553.894l-2 1A1 1 0 009 19v-6a1 1 0 00-.293-.707L2.293 6.707A1 1 0 012 6V4z"
                                />
                            </svg>
                            <span class="text-sm font-medium text-gray-700"
                                >Filtros</span
                            >
                            <span
                                class="ml-2 px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold"
                                v-if="activeFilterOptions.length > 0"
                            >
                                {{ activeFilterOptions.length }}
                            </span>
                        </button>
                        <FiltroFlotante
                            v-show="showFiltro"
                            :filtros="filtrosDisponibles"
                            :selected="filtrosActivos"
                            :fechaDesdeProp="fechaDesde"
                            :fechaHastaProp="fechaHasta"
                            :mostrarFiltrosFecha="
                                permisos.puedeSeleccionarFechas
                            "
                            @filtrar="aplicarFiltrosDesdeFlotante"
                            @search="aplicarBusquedaInstantanea"
                            @close="showFiltro = false"
                        />
                    </div>

                    <!-- Dropdown de Operaciones -->
                    <div
                        class="relative"
                        v-if="
                            canDo('vodafone.crear') ||
                            canDo('vodafone.importar') ||
                            canDo('vodafone.asignar')
                        "
                    >
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-md shadow hover:bg-gray-50 transition"
                                >
                                    Listado de Operaciones
                                    <svg
                                        class="w-4 h-4 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M19 9l-7 7-7-7"
                                        />
                                    </svg>
                                </button>
                            </template>
                            <!-- Opciones -->
                            <template #content>
                                <ul class="divide-y divide-gray-100">
                                    <li v-if="canDo('vodafone.crear')">
                                        <button
                                            @click="abrirModalAgregar"
                                            class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition text-left"
                                        >
                                            <svg
                                                class="w-4 h-4 text-indigo-500 shrink-0"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M12 4v16m8-8H4"
                                                />
                                            </svg>
                                            <span class="font-medium"
                                                >Agregar</span
                                            >
                                        </button>
                                    </li>
                                    <li v-if="canDo('vodafone.importar')">
                                        <button
                                            @click="abrirModalInportar"
                                            class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition text-left"
                                        >
                                            <svg
                                                class="w-4 h-4 text-indigo-500 shrink-0"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 448 512"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill="currentColor"
                                                    d="M416 176c0 8.8 7.2 16 16 16s16-7.2 16-16l0-80c0-53-43-96-96-96L96 0C43 0 0 43 0 96l0 80c0 8.8 7.2 16 16 16s16-7.2 16-16l0-80c0-35.3 28.7-64 64-64l256 0c35.3 0 64 28.7 64 64l0 80zM212.7 507.3c6.2 6.2 16.4 6.2 22.6 0l144-144c6.2-6.2 6.2-16.4 0-22.6s-16.4-6.2-22.6 0L240 457.4 240 176c0-8.8-7.2-16-16-16s-16 7.2-16 16l0 281.4-116.7-116.7c-6.2-6.2-16.4-6.2-22.6 0s-6.2 16.4 0 22.6l144 144z"
                                                />
                                            </svg>
                                            <span class="font-medium"
                                                >Importar</span
                                            >
                                        </button>
                                    </li>
                                    <li v-if="canDo('vodafone.asignar')">
                                        <button
                                            @click="abrirModalAsignacion"
                                            class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition text-left"
                                        >
                                            <svg
                                                class="w-4 h-4 text-indigo-500 shrink-0"
                                                fill="currentColor"
                                                viewBox="0 0 640 512"
                                            >
                                                <path
                                                    fill="currentColor"
                                                    d="M256 224a96 96 0 1 0 0-192 96 96 0 1 0 0 192zM256 0a128 128 0 1 1 0 256 128 128 0 1 1 0-256zM208 336c-79.5 0-144 64.5-144 144l0 16c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-16c0-97.2 78.8-176 176-176l96 0c97.2 0 176 78.8 176 176l0 16c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-16c0-79.5-64.5-144-144-144l-96 0zm320-72l0-56-56 0c-8.8 0-16-7.2-16-16s7.2-16 16-16l56 0 0-56c0-8.8 7.2-16 16-16s16 7.2 16 16l0 56 56 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-56 0 0 56c0 8.8-7.2 16-16 16s-16-7.2-16-16z"
                                                />
                                            </svg>
                                            <span class="font-medium"
                                                >Asignar Filtrador</span
                                            >
                                        </button>
                                    </li>
                                </ul>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </div>
        </template>

        <!-- =======================
             TABLA PRINCIPAL
        ======================= -->
        <div class="py-6 relative">
            <div
                class="overflow-x-auto rounded-xl border border-gray-100 bg-gray-50 relative"
                ref="scrollContainer"
            >
                <ExcelLikeGrid
                    :rows="filteredItems"
                    :columns="columnasGrid"
                    :canViewGlobal="canViewGlobal"
                    :canEdit="canDo('vodafone.editar')"
                    :canEditRecord="canEditRecord"
                    :canEditComplete="canDo('vodafone.editar-completados')"
                    :canDelete="canDo('vodafone.eliminar')"
                    :isLoading="isLoading"
                    :canViewHistory="canViewHistory"
                    v-model:selected="selectedRows"
                    @edit="abrirModalEditar"
                    @delete="eliminar"
                    @showHistory="abrirHistorial"
                />
                <!-- =======================
             MODAL DE HISTORIAL DE ASIGNACIONES
        ======================= -->
                <ModalGestion
                    v-if="canViewHistory"
                    :show="showHistorialModal"
                    title="Historial de Asignaciones"
                    infoOnly
                    @close="cerrarHistorial"
                >
                    <template #default>
                        <HistorialAsignaciones
                            :cabeceras="
                                [
                                    registroHistorial?.ultima_asignacion
                                        ? {
                                              ...registroHistorial.ultima_asignacion,
                                              auditoria:
                                                  registroHistorial.auditoria_historial ||
                                                  [],
                                          }
                                        : null,
                                    ...historialSeleccionado
                                        .filter(
                                            (hh) =>
                                                !registroHistorial?.ultima_asignacion ||
                                                hh.id !==
                                                    registroHistorial
                                                        .ultima_asignacion.id
                                        )
                                        .map((hh) => ({
                                            ...hh,
                                            auditoria:
                                                hh.auditoria_historial || [],
                                        })),
                                ].filter(Boolean)
                            "
                        />
                    </template>
                </ModalGestion>
                <!-- Overlay de loading SOLO sobre la tabla -->
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
            </div>
        </div>

        <!-- =======================
             MODAL DE AGREGAR/EDITAR
        ======================= -->
        <ModalGestion
            :show="showModal"
            :title="registroEditar ? 'Editar Registro' : 'Nuevo Registro'"
            submitLabel="Guardar"
            :initialForm="form"
            :endpoint="
                registroEditar
                    ? route('vodafone.update', registroEditar.id)
                    : route('vodafone.store')
            "
            :method="registroEditar ? 'put' : 'post'"
            @close="cerrarModal"
            @success="handleSuccess"
            @general-error="
                (msg) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'No se puede editar',
                        text: msg,
                        confirmButtonText: 'Entendido',
                    });
                    showModal = false;
                    registroEditar = null;
                }
            "
        >
            <template #default="{ form: slotForm, errors }">
                <InputField
                    class="modalInputs"
                    label="Nombre del Cliente"
                    v-model="slotForm.nombre_cliente"
                    name="nombre_cliente"
                    :error="errors.nombre_cliente"
                    required
                />
                <div class="flex justify-between width-49">
                    <InputField
                        class="modalInputs"
                        label="DNI Cliente"
                        v-model="slotForm.dni_cliente"
                        name="dni_cliente"
                        :error="errors.dni_cliente"
                        required
                    />
                    <InputField
                        class="modalInputs"
                        label="Teléfono Principal"
                        v-model="slotForm.telefono_principal"
                        name="telefono_principal"
                        :error="errors.telefono_principal"
                    />
                </div>
                <div class="flex justify-between width-49">
                    <InputField
                        class="modalInputs"
                        label="Teléfono Adicional"
                        v-model="slotForm.telefono_adicional"
                        name="telefono_adicional"
                        :error="errors.telefono_adicional"
                    />
                    <InputField
                        class="modalInputs"
                        label="Correo de Referencia"
                        v-model="slotForm.correo_referencia"
                        name="correo_referencia"
                        :error="errors.correo_referencia"
                    />
                </div>
                <InputField
                    class="modalInputs"
                    label="Dirección Histórico"
                    v-model="slotForm.direccion_historico"
                    name="direccion_historico"
                    :error="errors.direccion_historico"
                />
                <InputField
                    class="modalInputs"
                    label="Marca de la Base"
                    v-model="slotForm.marca_base"
                    name="marca_base"
                    :error="errors.marca_base"
                />
                <InputField
                    class="modalInputs"
                    label="Origen Motivo Cancelación"
                    v-model="slotForm.origen_motivo_cancelacion"
                    name="origen_motivo_cancelacion"
                    :error="errors.origen_motivo_cancelacion"
                />
                <InputField
                    class="modalInputs"
                    label="Orden Trabajo Anterior"
                    v-model="slotForm.orden_trabajo_anterior"
                    name="orden_trabajo_anterior"
                    :error="errors.orden_trabajo_anterior"
                />
                <InputField
                    class="modalInputs"
                    label="Observaciones"
                    v-model="slotForm.observaciones"
                    name="observaciones"
                    :error="errors.observaciones"
                />
            </template>
        </ModalGestion>

        <!-- =======================
             MODAL DE IMPORTACIÓN
        ======================= -->
        <ModalImportacion
            :show="showInportarModal"
            title="Importar nuevos Registros"
            submitLabel="Importar"
            :initialForm="formImportar"
            :previewRows="previewRows"
            :allRows="allPreviewRows"
            :importando="importando"
            method="post"
            @close="cerrarModal"
            @submit="importarArchivo"
            @confirmar="confirmarImportacion"
        >
            <template #default="{ form: slotForm, errors }">
                <div class="space-y-8 text-sm text-gray-700">
                    <!-- Card de subida de archivo y estado -->
                    <div
                        class="bg-white border border-gray-200 p-4 rounded-xl shadow-sm animate__animated animate__fadeIn space-y-3"
                    >
                        <!-- Campo de descripción -->
                        <InputField
                            class="modalInputs"
                            label="Descripción de la carga"
                            v-model="slotForm.descripcion"
                            name="descripcion"
                            :error="errors.descripcion"
                            required
                        />

                        <!-- Carga de archivo -->
                        <label
                            class="flex items-center justify-between gap-3 cursor-pointer font-medium px-4 py-2 rounded-lg border bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100 hover:text-indigo-900 relative transition"
                        >
                            <div class="flex items-center gap-2">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5 transition-transform duration-300"
                                    :class="'text-indigo-500'"
                                    fill="currentColor"
                                    viewBox="0 0 640 640"
                                >
                                    <path
                                        d="M336 118.6V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V118.6l-84.7 84.7c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l112-112c6.2-6.2 16.4-6.2 22.6 0l112 112c6.2 6.2 6.2 16.4 0 22.6s-16.4 6.2-22.6 0L336 118.6zM256 384h-96c-17.7 0-32 14.3-32 32v64c0 17.7 14.3 32 32 32h320c17.7 0 32-14.3 32-32v-64c0-17.7-14.3-32-32-32h-96v-32h96c35.3 0 64 28.7 64 64v64c0 35.3-28.7 64-64 64H160c-35.3 0-64-28.7-64-64v-64c0-35.3 28.7-64 64-64h96v32z"
                                    />
                                </svg>
                                <span class="text-sm">
                                    {{
                                        fileName
                                            ? "Archivo seleccionado"
                                            : "Seleccionar archivo Excel (.xlsx)"
                                    }}
                                </span>
                            </div>

                            <input
                                type="file"
                                name="archivo"
                                class="hidden"
                                @change="handleFileUpload"
                                accept=".xlsx, .xls"
                            />

                            <!-- Mostrar nombre y botón quitar -->
                            <transition name="fade">
                                <div
                                    v-if="fileName"
                                    class="text-xs font-semibold animate__animated animate__fadeInRight flex items-center gap-2"
                                >
                                    {{ fileName }}
                                    <!-- <button
                                        type="button"
                                        class="px-2 py-0.5 bg-orange-100 text-red-600 rounded hover:bg-orange-200 transition text-xs"
                                        @click.stop.prevent="
                                            fileName = null;
                                            excelFile = null;
                                            formImportar.value.archivo = null;
                                        "
                                    >
                                        Quitar
                                    </button> -->
                                </div>
                            </transition>
                        </label>

                        <!-- Progreso -->
                        <transition name="fade">
                            <div
                                v-if="
                                    importStatus === 'Subiendo archivo...' ||
                                    importando ||
                                    importError
                                "
                                class="flex items-center gap-3 mt-2 w-full min-h-[24px]"
                            >
                                <div
                                    class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden shadow-inner"
                                >
                                    <div
                                        class="bg-gradient-to-r from-indigo-400 to-indigo-700 h-2 animate-pulse transition-all duration-500"
                                        :style="{ width: progress + '%' }"
                                    ></div>
                                </div>
                                <span
                                    v-if="
                                        importStatus === 'Subiendo archivo...'
                                    "
                                    class="text-xs font-semibold text-indigo-700 w-10 text-right"
                                    >{{ progress }}%</span
                                >
                                <svg
                                    v-if="importando"
                                    class="w-4 h-4 text-indigo-500 animate-spin ml-2"
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
                                    v-if="importando && !importError"
                                    class="text-xs text-indigo-700 font-semibold ml-1"
                                    >Procesando...</span
                                >
                                <span
                                    v-if="importError"
                                    class="text-xs text-red-600 animate__animated animate__shakeX ml-2"
                                    >{{ importError }}</span
                                >
                            </div>
                        </transition>
                    </div>
                    <div
                        class="bg-white border border-gray-200 p-4 rounded-xl shadow-sm animate__animated animate__fadeIn mt-0"
                    >
                        <label class="colorMorado"
                            >Descarga la plantilla Excel</label
                        >
                        <p>
                            <a
                                :href="route('vodafone.plantilla')"
                                download="plantilla_vodafone.xlsx"
                                class="flex items-center justify-start gap-3 cursor-pointer font-medium px-4 py-2 rounded-lg border bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100 hover:text-indigo-900 relative transition"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5 transition-transform duration-300"
                                    :class="'text-indigo-500'"
                                    viewBox="0 0 640 640"
                                >
                                    <!--! Font Awesome Pro 7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2025 Fonticons, Inc. -->
                                    <path
                                        fill="currentColor"
                                        d="M475.3 267.3L331.3 411.3C325.1 417.5 314.9 417.5 308.7 411.3L164.7 267.3C158.5 261.1 158.5 250.9 164.7 244.7C170.9 238.5 181.1 238.5 187.3 244.7L304 361.4L304 80C304 71.2 311.2 64 320 64C328.8 64 336 71.2 336 80L336 361.4L452.7 244.7C458.9 238.5 469.1 238.5 475.3 244.7C481.5 250.9 481.5 261.1 475.3 267.3zM128 400L128 480C128 515.3 156.7 544 192 544L448 544C483.3 544 512 515.3 512 480L512 400C512 391.2 519.2 384 528 384C536.8 384 544 391.2 544 400L544 480C544 533 501 576 448 576L192 576C139 576 96 533 96 480L96 400C96 391.2 103.2 384 112 384C120.8 384 128 391.2 128 400z"
                                    />
                                </svg>
                                <span class="text-sm">
                                    Descargar Plantilla Excel
                                </span>
                            </a>
                        </p>
                    </div>
                    <!-- Previsualización de duplicados -->
                    <transition name="fade">
                        <div
                            v-if="previewRows.length > 0"
                            class="mt-4 animate__animated animate__fadeInUp"
                        >
                            <div
                                class="overflow-x-auto border rounded-xl shadow-sm"
                                style="max-height: 300px; overflow-y: auto"
                            >
                                <table
                                    class="min-w-full text-xs text-left text-gray-700"
                                >
                                    <thead
                                        class="bg-indigo-50 sticky top-0 z-10"
                                    >
                                        <tr>
                                            <th class="px-3 py-2">#</th>
                                            <th class="px-3 py-2">Nombre</th>
                                            <th class="px-3 py-2">DNI</th>
                                            <th class="px-3 py-2">Teléfono</th>
                                            <th class="px-3 py-2">
                                                ¿Duplicado?
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="row in previewRows"
                                            :key="row.index"
                                            :class="
                                                row.duplicado
                                                    ? 'bg-yellow-50 animate__animated animate__flash'
                                                    : 'bg-green-50'
                                            "
                                            class="border-b border-gray-100"
                                        >
                                            <td class="px-3 py-2">
                                                {{ row.index }}
                                            </td>
                                            <td class="px-3 py-2">
                                                {{ row.nombre_cliente }}
                                            </td>
                                            <td class="px-3 py-2">
                                                {{ row.dni_cliente }}
                                            </td>
                                            <td class="px-3 py-2">
                                                {{ row.telefono_principal }}
                                            </td>
                                            <td
                                                class="px-3 py-2 font-semibold"
                                                :class="
                                                    row.duplicado
                                                        ? 'text-red-600'
                                                        : 'text-green-600'
                                                "
                                            >
                                                {{
                                                    row.duplicado ? "Sí" : "No"
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                v-if="previewTruncado"
                                class="text-xs text-orange-600 mt-2 text-center font-semibold animate__animated animate__fadeIn"
                            >
                                Mostrando solo los primeros
                                {{ allPreviewRows.length }} registros de
                                {{ previewTotal }} totales. Si necesitas
                                importar más, divide el archivo.
                            </div>
                        </div>
                    </transition>
                    <!-- Resumen y opciones -->
                    <transition name="fade">
                        <div
                            v-if="totalRegistros > 0"
                            class="mt-6 flex flex-wrap items-center gap-6 bg-indigo-50 rounded-xl p-4 border animate__animated animate__fadeIn"
                        >
                            <div class="flex gap-6 flex-wrap">
                                <span
                                    class="text-green-700 font-medium flex items-center gap-2"
                                >
                                    <svg
                                        class="inline w-4 h-4"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11H9v2h2V7zm0 4H9v2h2v-2z"
                                        />
                                    </svg>
                                    Nuevos: {{ totalNuevos }}
                                </span>
                                <span
                                    class="text-yellow-700 font-medium flex items-center gap-2"
                                >
                                    <svg
                                        class="inline w-4 h-4"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11H9v2h2V7zm0 4H9v2h2v-2z"
                                        />
                                    </svg>
                                    Duplicados: {{ totalDuplicados }}
                                </span>
                                <span
                                    class="text-gray-700 font-medium flex items-center gap-2"
                                >
                                    <svg
                                        class="inline w-4 h-4"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11H9v2h2V7zm0 4H9v2h2v-2z"
                                        />
                                    </svg>
                                    Total: {{ totalRegistros }}
                                </span>
                            </div>
                        </div>
                    </transition>
                </div>
            </template>
        </ModalImportacion>

        <!-- =======================
             MODAL DE ASIGNACIÓN
        ======================= -->
        <ModalGestion
            :show="showAsignacionModal"
            :title="asignacionTitulo ? 'ASIGNAR' : 'Asignación de Registros'"
            submitLabel="Asignar"
            :initialForm="{
                asignado_a_id: null,
                ids: selectedRows.map((r) => r.id),
            }"
            :endpoint="route('vodafone.asignar')"
            :method="'post'"
            :loading="isLoadingAsignacion"
            @close="cerrarModal"
            @submit="asignarRegistros"
            @success="handleSuccess"
        >
            <template #default="{ form: slotForm, errors }">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">
                    Filtrador a asignar
                </h3>

                <!-- Dropdown estilizado -->
                <div class="relative mb-4">
                    <Dropdown
                        align="center"
                        :minWidth="'455'"
                        :maxWidth="'400'"
                    >
                        <template #trigger>
                            <button
                                type="button"
                                class="flex items-center justify-between w-full px-4 py-2 text-sm bg-white text-gray-700 border border-gray-300 rounded-md shadow hover:bg-gray-50 transition"
                            >
                                {{
                                    slotForm.asignado_a_id
                                        ? usuariosAsignables.find(
                                              (u) =>
                                                  u.id ===
                                                  slotForm.asignado_a_id
                                          )?.name || "Seleccionar"
                                        : "Seleccionar filtrador"
                                }}
                                <svg
                                    class="w-4 h-4 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <ul class="divide-y divide-gray-100">
                                <li
                                    v-for="usuario in usuariosAsignables"
                                    :key="usuario.id"
                                >
                                    <button
                                        type="button"
                                        @click="
                                            slotForm.asignado_a_id = usuario.id
                                        "
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition"
                                    >
                                        {{ usuario.name }}
                                    </button>
                                </li>
                            </ul>
                        </template>
                    </Dropdown>
                </div>

                <!-- Error de validación si lo hay -->
                <p
                    v-if="errors.asignado_a_id"
                    class="text-sm text-red-600 mb-2"
                >
                    {{ errors.asignado_a_id }}
                </p>

                <!-- Cantidad de registros -->
                <div class="mt-4 text-sm text-gray-600">
                    <strong>Registros a asignar:</strong>
                    {{ selectedRows.length }} registro(s)
                </div>
            </template>
        </ModalGestion>
    </AuthenticatedLayout>
</template>
<style scoped>
@keyframes typing-dot {
    0%,
    80%,
    100% {
        transform: scale(0.8);
        opacity: 0.3;
    }
    40% {
        transform: scale(1.2);
        opacity: 1;
    }
}

.typing-dot {
    display: inline-block;
    width: 6px;
    height: 6px;
    background-color: currentColor;
    border-radius: 9999px;
    animation: typing-dot 1.2s infinite ease-in-out;
}

.typing-dot:nth-child(1) {
    animation-delay: 0s;
}
.typing-dot:nth-child(2) {
    animation-delay: 0.15s;
}
.typing-dot:nth-child(3) {
    animation-delay: 0.3s;
}
</style>
