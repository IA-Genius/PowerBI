<script setup>
// =======================
// 1. IMPORTS
// =======================
import { ref, computed, onMounted, watch, nextTick, watchEffect } from "vue";
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
// 2. PAGE PROPS & PERMISOS
// =======================
const pageProps = usePage().props;
const success = pageProps.success;
const canViewGlobal = pageProps.canViewGlobal;
const can = pageProps.can || {};
const canDo = (key) => !!can[key];
const canViewHistory = !!can["vodafone.ver-historial"];
const usuariosAsignables = pageProps.usuariosAsignables || [];

// =======================
// 3. ESTADO PRINCIPAL
// =======================
const items = ref([]);
const selectedRows = ref([]);
const currentPage = ref(1);
const lastPage = ref(1);
const isLoading = ref(false);
const scrollContainer = ref(null);
const isLoadingAsignacion = ref(false);

// =======================
// 4. MODALES
// =======================
const showModal = ref(false);
const registroEditar = ref(null);
const showInportarModal = ref(false);
const InportarTitulo = ref(null);
const showAsignacionModal = ref(false);
const asignacionTitulo = ref(null);
// Manejo de errores generales del backend
const generalError = ref(null);
// Historial de asignaciones
const showHistorialModal = ref(false);
const historialSeleccionado = ref([]);
const registroHistorial = ref(null);

function abrirHistorial(item) {
    historialSeleccionado.value = item.asignaciones_historial || [];
    registroHistorial.value = item;
    showHistorialModal.value = true;
}
function cerrarHistorial() {
    showHistorialModal.value = false;
    historialSeleccionado.value = [];
    registroHistorial.value = null;
}

// =======================
// 5. FORMULARIOS
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
// 6. FUNCIONES DE MODALES
// =======================
function abrirModalAgregar() {
    registroEditar.value = null;
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
    showModal.value = true;
}
function abrirModalInportar() {
    showInportarModal.value = true;
    // Solo reiniciar archivo, NO descripcion
    formImportar.value.archivo = null;
    fileName.value = null;
    excelFile.value = null;
    allPreviewRows.value = [];
    previewRows.value = [];
    modoDuplicados.value = "omitir";
    importStatus.value = "";
    importError.value = "";
    totalRegistros.value = 0;
    totalDuplicados.value = 0;
    totalNuevos.value = 0;
    progress.value = 0;
}
function abrirModalAsignacion() {
    if (selectedRows.value.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "Ningún registro seleccionado",
            text: "Por favor selecciona al menos un registro para asignar.",
            confirmButtonText: "Entendido",
        });
        return;
    }
    showAsignacionModal.value = true;
}

onMounted(() => {
    inicializarItems();
});
function abrirModalEditar(item) {
    registroEditar.value = item;
    form.value = { ...item };
    showModal.value = true;
    // Limpiar error general al abrir
    generalError.value = null;
}
function cerrarModal() {
    showModal.value = false;
    registroEditar.value = null;
    showInportarModal.value = false;
    InportarTitulo.value = null;
    showAsignacionModal.value = false;
    asignacionTitulo.value = null;
    generalError.value = null;
}

// =======================
// 7. CRUD Y UTILIDADES
// =======================
function handleSuccess(msg) {
    Swal.fire({
        toast: true,
        icon: "success",
        title: msg,
        position: "top-end",
        timer: 2000,
        showConfirmButton: false,
    });
    cerrarModal();
    router.visit(route("vodafone.index"), {
        only: ["items", "success"],
    });
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
                onError: () =>
                    Swal.fire({
                        toast: true,
                        icon: "error",
                        title: "Error al eliminar",
                        position: "top-end",
                        timer: 2000,
                        showConfirmButton: false,
                    }),
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

// =======================
// 8. INICIALIZACIÓN Y WATCHERS
// =======================
function inicializarItems() {
    items.value = Array.isArray(pageProps.items) ? pageProps.items : [];
    currentPage.value = 1;
    lastPage.value = 1;
}
onMounted(() => {
    inicializarItems();
    if (success) {
        Swal.fire({
            toast: true,
            icon: "success",
            title: success,
            position: "top-end",
            timer: 2000,
            showConfirmButton: false,
        });
    }
    // Mostrar error general si viene de backend
    if (pageProps.errors && pageProps.errors.general) {
        generalError.value = pageProps.errors.general;
    }
});

// Mostrar SweetAlert si hay error general y el modal de edición está abierto
watchEffect(() => {
    if (showModal.value && generalError.value) {
        Swal.fire({
            icon: "error",
            title: "No se puede editar",
            text: generalError.value,
            confirmButtonText: "Entendido",
        });
        // Cerrar el modal automáticamente
        showModal.value = false;
        registroEditar.value = null;
    }
});
watch(
    () => pageProps.items,
    () => {
        items.value = Array.isArray(pageProps.items) ? pageProps.items : [];
        currentPage.value = 1;
        lastPage.value = 1;
    }
);

// =======================
// 9. FILTROS Y BÚSQUEDA
// =======================
const search = ref("");
const showFiltro = ref(false);
const fechaDesde = ref(pageProps.fechaDesde || "");
const fechaHasta = ref(pageProps.fechaHasta || "");

const filtrosActivos = ref(
    canDo("vodafone.asignar")
        ? (() => {
              // Intenta cargar filtros desde localStorage
              try {
                  const saved = localStorage.getItem("vodafoneFiltros");
                  if (saved) {
                      return JSON.parse(saved);
                  }
              } catch (e) {}
              // Si no hay nada guardado, valor por defecto
              return { trazabilidad: ["pendiente"] };
          })()
        : { trazabilidad: [] }
);

watch(
    filtrosActivos,
    (val) => {
        if (canDo("vodafone.asignar")) {
            try {
                localStorage.setItem("vodafoneFiltros", JSON.stringify(val));
            } catch (e) {}
        }
    },
    { deep: true }
);

const rawItems = computed(() => items.value);

const filtrosDisponibles = computed(() => {
    const list = rawItems.value;
    const filtros = {
        operador_actual: [
            ...new Set(list.map((i) => i.operador_actual).filter(Boolean)),
        ],
        trazabilidad: [
            "pendiente",
            "asignado",
            "irrelevante",
            "completado",
            "agendado",
        ],
        upload_id: [...new Set(list.map((i) => i.upload_id).filter(Boolean))]
            .sort((a, b) => b - a)
            .slice(0, 15)
            .sort((a, b) => a - b),
    };
    // Solo los filtros con opciones (trazabilidad siempre)
    return Object.fromEntries(
        Object.entries(filtros).filter(
            ([key, arr]) =>
                key === "trazabilidad" || (Array.isArray(arr) && arr.length > 0)
        )
    );
});

const filteredItems = computed(() => {
    const term = filtrosActivos.value.search?.toLowerCase() || "";
    let data = [...rawItems.value];
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
    for (const [key, values] of Object.entries(filtrosActivos.value)) {
        if (key === "search") continue;
        if (!Array.isArray(values) || values.length === 0) continue;
        data = data.filter((item) => values.includes(item[key]));
    }
    return data;
});
function aplicarBusquedaInstantanea(val) {
    filtrosActivos.value = { ...filtrosActivos.value, search: val };
}
async function aplicarFiltrosDesdeFlotante(f) {
    filtrosActivos.value = { ...filtrosActivos.value, ...f };
    if (f.fecha_desde !== undefined) fechaDesde.value = f.fecha_desde;
    if (f.fecha_hasta !== undefined) fechaHasta.value = f.fecha_hasta;
    isLoading.value = true;
    try {
        const response = await axios.get(route("vodafone.page"), {
            params: {
                ...filtrosActivos.value,
                fecha_desde: fechaDesde.value,
                fecha_hasta: fechaHasta.value,
            },
        });
        items.value = response.data.items;
    } catch (e) {
        Swal.fire("Error", "No se pudo filtrar por fecha", "error");
    } finally {
        isLoading.value = false;
    }
}

// =======================
// 10. IMPORTACIÓN DE EXCEL
// =======================
const excelFile = ref(null);
const fileName = ref(null);
const allPreviewRows = ref([]);
const previewRows = ref([]);
const modoDuplicados = ref("omitir");
const importStatus = ref("");
const importError = ref("");
const totalRegistros = ref(0);
const totalDuplicados = ref(0);
const totalNuevos = ref(0);
const previewTruncado = ref(false); // <-- NUEVO
const previewTotal = ref(0); // <-- NUEVO
const formImportar = ref({
    archivo: null,
});
const progress = ref(0);
const importando = ref(false); // <-- Agrega esta línea

function handleFileUpload(event) {
    const file = event.target.files[0];
    if (file) {
        fileName.value = file.name;
        excelFile.value = file;
        formImportar.value.archivo = file;
    }
}
watch(fileName, () => {
    formImportar.value.archivo = excelFile.value;
});

async function importarArchivo(_, close) {
    try {
        importStatus.value = "Subiendo archivo...";
        importError.value = "";
        progress.value = 0;
        const payload = new FormData();
        const archivo = formImportar.value.archivo;
        if (!(archivo instanceof File)) {
            importError.value = "Debes seleccionar un archivo Excel válido.";
            return;
        }
        payload.append("archivo", archivo);
        payload.append("descripcion", formImportar.value.descripcion);

        // Subida con barra de progreso
        const response = await axios.post(route("vodafone.preview"), payload, {
            headers: { "Content-Type": "multipart/form-data" },
            onUploadProgress: (event) => {
                if (event.lengthComputable) {
                    progress.value = Math.round(
                        (event.loaded * 100) / event.total
                    );
                }
            },
        });

        // Al terminar la subida, mostrar "Procesando..."
        importStatus.value = "Procesando archivo...";
        progress.value = 100;
        // Guardar truncado y total
        previewTruncado.value = !!response.data.truncado;
        previewTotal.value = response.data.total || 0;

        // Asignar los valores reales enviados por el backend
        totalRegistros.value = response.data.total_registros || 0;
        totalDuplicados.value = response.data.total_duplicados || 0;
        totalNuevos.value = response.data.total_nuevos || 0;

        // Solo para la tabla de preview
        if (Array.isArray(response.data?.preview)) {
            allPreviewRows.value = response.data.preview;
            previewRows.value = allPreviewRows.value.filter(
                (r) => r.duplicado === true
            );
            importStatus.value = `Se analizaron ${totalRegistros.value} registros`;
        } else {
            allPreviewRows.value = [];
            previewRows.value = [];
        }
    } catch (error) {
        importError.value =
            error.response?.data?.message || "Error al importar archivo";
        importStatus.value = "";
        progress.value = 0;
        previewTruncado.value = false;
        previewTotal.value = 0;
    }
}

function confirmarImportacion() {
    if (previewRows.value.length > 0) {
        Swal.fire({
            title: "Duplicados detectados",
            text: "¿Qué deseas hacer con los registros duplicados?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Actualizar duplicados",
            cancelButtonText: "Omitir duplicados",
            reverseButtons: true,
        }).then((result) => {
            modoDuplicados.value = result.isConfirmed ? "actualizar" : "omitir";
            enviarImportacion();
        });
    } else {
        modoDuplicados.value = null;
        enviarImportacion();
    }
}

async function enviarImportacion() {
    const datosAEnviar = Array.isArray(allPreviewRows.value)
        ? allPreviewRows.value
        : Object.values(allPreviewRows.value);

    importando.value = true;
    importStatus.value = "Procesando importación...";

    try {
        const response = await axios.post(
            route("vodafone.importarConfirmado"),
            {
                datos: datosAEnviar,
                modo: modoDuplicados.value,
                descripcion: formImportar.value.descripcion,
            }
        );
        const logId = response.data.log_id;
        await esperarImportacion(logId);
    } catch (error) {
        importError.value =
            error.response?.data?.message || "Error al importar";
        importando.value = false;
        importStatus.value = "";
    }
}

async function esperarImportacion(logId) {
    let intentos = 0;
    const maxIntentos = 60; // espera hasta 60 segundos
    while (intentos < maxIntentos) {
        intentos++;
        try {
            const resp = await axios.get(
                route("vodafone.obtenerErroresLog", logId)
            );
            const estado = resp.data.estado;
            const errores = resp.data.errores ?? [];

            if (estado === "finalizado") {
                importando.value = false;
                importStatus.value = "";
                if (errores.length) {
                    importError.value = "Importación completada con errores.";
                    console.warn("Errores de importación:", errores);
                } else {
                    Swal.fire({
                        icon: "success",
                        title: "Importación completada",
                        text: "Los registros se importaron correctamente.",
                        timer: 2000,
                        showConfirmButton: false,
                        position: "top-end",
                        toast: true,
                    });
                    handleSuccess("Importación realizada correctamente.");
                }
                return;
            } else if (estado === "procesando") {
                importStatus.value = "Procesando registros...";
            } else {
                importStatus.value =
                    "Esperando a que inicie el procesamiento...";
            }
        } catch (e) {
            console.warn("Error verificando estado:", e);
        }

        await new Promise((r) => setTimeout(r, 1000)); // Espera 1s
    }

    importando.value = false;
    importStatus.value = "";
    importError.value = "La importación está tardando demasiado.";
}

const columnasGrid = computed(() => {
    if (canDo("vodafone.asignar")) {
        return [
            "id",
            "upload_id",
            "created_at_formatted",
            "trazabilidad",
            "asignado_a",
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
            "user",
        ];
    }

    if (canDo("vodafone.recibe-asignacion")) {
        return [
            "id",
            "asignado_a",
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
    }
    return [
        "id",
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
});

// =======================
// 11. ASIGNACIÓN DE REGISTROS
// =======================
async function asignarRegistros(slotForm) {
    isLoadingAsignacion.value = true;
    try {
        await axios.post(route("vodafone.asignar"), {
            ids: slotForm.ids,
            asignado_a_id: slotForm.asignado_a_id,
        });
        handleSuccess("Registros asignados correctamente.");
        cerrarModal();
    } catch (e) {
        Swal.fire("Error", "No se pudo asignar registros", "error");
    } finally {
        isLoadingAsignacion.value = false;
    }
}

// =======================
// 12. CONTADOR DE FILTROS ACTIVOS
// =======================

const activeFilterOptions = computed(() => {
    const filtros = filtrosActivos.value || {};
    let opciones = [];
    Object.keys(filtros).forEach((k) => {
        if (
            k !== "search" &&
            Array.isArray(filtros[k]) &&
            filtros[k].length > 0
        ) {
            opciones = opciones.concat(filtros[k]);
        }
    });
    return opciones;
});
const cabecerasHistorial = computed(() => {
    const ult = registroHistorial.value?.ultima_asignacion
        ? [
              {
                  ...registroHistorial.value.ultima_asignacion,
                  auditoria: registroHistorial.value.auditoria_historial || [],
              },
          ]
        : [];
    const resto = historialSeleccionado.value
        .filter(
            (hh) =>
                !registroHistorial.value?.ultima_asignacion ||
                hh.id !== registroHistorial.value.ultima_asignacion.id
        )
        .map((hh) => ({ ...hh, auditoria: hh.auditoria_historial || [] }));
    return [...ult, ...resto];
});
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
                    <div class="modalFiltros" v-if="canDo('vodafone.asignar')">
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
                                    <button
                                        type="button"
                                        class="px-2 py-0.5 bg-orange-100 text-red-600 rounded hover:bg-orange-200 transition text-xs"
                                        @click.stop.prevent="
                                            fileName = null;
                                            excelFile = null;
                                            formImportar.value.archivo = null;
                                        "
                                    >
                                        Quitar
                                    </button>
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

                    <!-- Previsualización de duplicados -->
                    <transition name="fade">
                        <div
                            v-if="previewRows.length > 0"
                            class="mt-4 animate__animated animate__fadeInUp"
                        >
                            <div
                                class="overflow-x-auto border rounded-xl shadow-sm"
                                style="max-height: 400px; overflow-y: auto"
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
