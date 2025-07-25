<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ModalGestion from "@/Components/ModalGestion.vue";
import InputField from "@/Components/InputField.vue";
import Actions from "@/Components/Actions.vue";
import Dropdown from "@/Components/Dropdown.vue";
import ExcelLikeGrid from "@/Components/ExcelLikeGrid.vue";
const pageProps = usePage().props;
const selectedRows = ref([]);
import DropdownLink from "@/Components/DropdownLink.vue";
const search = ref("");
const items = computed(() => pageProps.items || []);
const success = pageProps.success;
const canViewGlobal = pageProps.canViewGlobal;
const can = usePage().props.can || {};
const canDo = (key) => !!can[key];
import FiltroFlotante from "@/Components/FiltroFlotante.vue";
const usuariosAsignables = pageProps.usuariosAsignables || [];
const showModal = ref(false);
const registroEditar = ref(null);

const showInportarModal = ref(false);
const InportarTitulo = ref(null);

const showAsignacionModal = ref(false);
const asignacionTitulo = ref(null);

const form = ref({
    nombre_cliente: "",
    dni_nif_cif: "",
    telefono_contacto: "",
    direccion_instalacion: "",
    operador_actual: "",
    oferta_comercial: "",
    observacion_smart: "",
    tipificaciones: "",
});

onMounted(() => {
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
});

function abrirModalAgregar() {
    registroEditar.value = null;
    form.value = {
        nombre_cliente: "",
        dni_nif_cif: "",
        telefono_contacto: "",
        direccion_instalacion: "",
        operador_actual: "",
        oferta_comercial: "",
        observacion_smart: "",
        tipificaciones: "",
    };
    showModal.value = true;
}

function abrirModalInportar() {
    showInportarModal.value = true;
}

function abrirModalAsignacion() {
    showAsignacionModal.value = true;
}

function abrirModalEditar(item) {
    registroEditar.value = item;
    form.value = { ...item };
    showModal.value = true;
}

function cerrarModal() {
    showModal.value = false;
    registroEditar.value = null;

    showInportarModal.value = false;
    InportarTitulo.value = null;

    showAsignacionModal.value = false;
    asignacionTitulo.value = null;
}

function handleSuccess(msg) {
    Swal.fire({
        toast: true,
        icon: "success",
        title: msg,
        position: "top-end",
        timer: 2000,
        showConfirmButton: false,
    });
    router.visit(route("vodafone.index"), {
        preserveScroll: true,
        only: ["items", "success"],
        onFinish: cerrarModal,
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

console.log(pageProps);

const showFiltro = ref(false);
const filtrosActivos = ref({});

const rawItems = computed(() => pageProps.items || []);

const filtrosDisponibles = computed(() => {
    const list = rawItems.value;

    return {
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
});

const filteredItems = computed(() => {
    const term = filtrosActivos.value.search?.toLowerCase() || "";
    let data = [...rawItems.value];

    // Filtro de texto
    if (term) {
        data = data.filter((item) =>
            [
                item.nombre_cliente,
                item.dni_nif_cif,
                item.telefono_contacto,
            ].some((field) =>
                (field || "").toString().toLowerCase().includes(term)
            )
        );
    }

    // Filtros exactos
    for (const [key, values] of Object.entries(filtrosActivos.value)) {
        if (key === "search") continue;
        if (!Array.isArray(values) || values.length === 0) continue;

        data = data.filter((item) => values.includes(item[key]));
    }

    return data;
});

function aplicarFiltros(f) {
    filtrosActivos.value = f;
}

const excelFile = ref(null);
const fileName = ref(null);

function handleFileUpload(event) {
    const file = event.target.files[0];
    if (file) {
        fileName.value = file.name;
        excelFile.value = file;
        form.value.archivo = file;
    }
}

function asignarRegistros(form, close) {
    if (!form.asignado_a_id || selectedRows.value.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "Faltan datos",
            text: "Debes seleccionar un filtrador y al menos un registro.",
            toast: true,
            position: "top-end",
            timer: 2500,
            showConfirmButton: false,
        });
        return;
    }

    const payload = {
        ...form,
        ids: selectedRows.value.map((row) => row.id),
    };

    router.post(route("vodafone.asignar"), payload, {
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props?.success) {
                Swal.fire({
                    toast: true,
                    icon: "success",
                    title: page.props.success,
                    position: "top-end",
                    timer: 2000,
                    showConfirmButton: false,
                });
            }

            selectedRows.value = []; // Limpia la selección
            close(); // Cierra modal
            router.visit(route("vodafone.index"), {
                preserveScroll: true,
                only: ["items", "success"],
            });
        },
        onError: (err) => {
            console.error("Errores en asignación:", err);
        },
    });
}
</script>

<template>
    <Head title="Vodafone" />
    <AuthenticatedLayout class="relleno">
        <template #header>
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
            >
                <!-- Título centrado en móvil -->
                <h2 class="text-xl font-semibold tituloPag">
                    Historial de Registros
                </h2>

                <div class="flex justify-between gap-4 itens-center">
                    <!-- Botón y modal de filtros -->
                    <div class="modalFiltros">
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
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707l-6.414 6.414A1 1 0 0013 13v5a1 1 0 01-.553.894l-2 1A1 1 0 019 19v-6a1 1 0 00-.293-.707L2.293 6.707A1 1 0 012 6V4z"
                                />
                            </svg>
                            <span class="text-sm font-medium text-gray-700"
                                >Filtros</span
                            >
                        </button>

                        <FiltroFlotante
                            v-show="showFiltro"
                            :filtros="filtrosDisponibles"
                            :selected="filtrosActivos"
                            v-model="search"
                            @filtrar="aplicarFiltros"
                            @close="showFiltro = false"
                        />
                    </div>

                    <!-- Dropdown de Operaciones -->

                    <div class="relative">
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

                                    <li>
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

                                    <li>
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

        <div class="py-6">
            <div
                class="overflow-x-auto rounded-xl border border-gray-100 bg-gray-50"
            >
                <ExcelLikeGrid
                    :rows="filteredItems"
                    :canViewGlobal="canViewGlobal"
                    :canEdit="canDo('vodafone.editar')"
                    :canDelete="canDo('vodafone.eliminar')"
                    v-model:selected="selectedRows"
                    @edit="abrirModalEditar"
                    @delete="eliminar"
                />
            </div>
        </div>
        <!-- Modal DE AGREGAR -->
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
        >
            <template #default="{ form: slotForm, errors }">
                <InputField
                    class="modalInputs"
                    label="Nombre Cliente"
                    v-model="slotForm.nombre_cliente"
                    name="nombre_cliente"
                    :error="errors.nombre_cliente"
                    required
                />
                <div class="flex justify-between width-49">
                    <InputField
                        class="modalInputs"
                        label="DNI / NIF / CIF"
                        v-model="slotForm.dni_nif_cif"
                        name="dni_nif_cif"
                        :error="errors.dni_nif_cif"
                        type="number"
                        required
                    />
                    <InputField
                        class="modalInputs"
                        label="Teléfono"
                        v-model="slotForm.telefono_contacto"
                        name="telefono_contacto"
                        type="number"
                        required
                        :error="errors.telefono_contacto"
                    />
                </div>
                <InputField
                    class="modalInputs"
                    label="Dirección"
                    v-model="slotForm.direccion_instalacion"
                    name="direccion_instalacion"
                    :error="errors.direccion_instalacion"
                    required
                />
                <InputField
                    class="modalInputs"
                    label="Operador Actual"
                    v-model="slotForm.operador_actual"
                    name="operador_actual"
                    :error="errors.operador_actual"
                />
                <InputField
                    class="modalInputs"
                    label="Oferta Comercial"
                    v-model="slotForm.oferta_comercial"
                    name="oferta_comercial"
                    :error="errors.oferta_comercial"
                />
                <div class="flex justify-between width-49">
                    <InputField
                        class="modalInputs"
                        label="Observación SMART"
                        v-model="slotForm.observacion_smart"
                        name="observacion_smart"
                        :error="errors.observacion_smart"
                    />
                    <InputField
                        class="modalInputs"
                        label="Tipificaciones"
                        v-model="slotForm.tipificaciones"
                        name="tipificaciones"
                        :error="errors.tipificaciones"
                    />
                </div>
            </template>
        </ModalGestion>

        <!-- Modal DE INPORTAR -->
        <ModalGestion
            :show="showInportarModal"
            :title="InportarTitulo ? 'Importar' : 'Importar nuevos Registros'"
            submitLabel="Importar"
            :initialForm="form"
            :endpoint="route('vodafone.import')"
            :method="'post'"
            @close="cerrarModal"
            @success="handleSuccess"
        >
            <template #default="{ form: slotForm, errors }">
                <div class="space-y-6">
                    <!-- Descripción -->
                    <InputField
                        class="modalInputs"
                        label="Descripción de la Carga"
                        name="descripcion"
                        :error="errors.descripcion"
                        required
                    />

                    <!-- Subida de archivo -->
                    <div
                        class="bg-gray-50 border border-gray-200 p-4 rounded-xl shadow-sm"
                    >
                        <h2 class="text-base font-semibold mb-2 text-gray-700">
                            Subir archivo Excel
                        </h2>
                        <label
                            class="flex items-center gap-3 cursor-pointer text-[var(--colorPrincipal)] hover:text-blue-700 transition"
                        >
                            <!-- Ícono -->
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                            >
                                <path
                                    d="M12 16c.55 0 1-.45 1-1V9.83l1.59 1.59L16 10l-4-4-4 4 1.41 1.41L11 9.83V15c0 .55.45 1 1 1zm6-10H6c-1.1 0-2 .9-2 2v12c0 
                            1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 
                            14H6V8h12v12z"
                                />
                            </svg>
                            <span class="font-medium"
                                >Seleccionar archivo Excel (.xlsx)</span
                            >
                            <input
                                type="file"
                                name="archivo"
                                class="hidden"
                                @change="handleFileUpload"
                                accept=".xlsx, .xls"
                            />
                        </label>
                        <p v-if="fileName" class="text-sm mt-2 text-gray-500">
                            Archivo seleccionado:
                            <strong>{{ fileName }}</strong>
                        </p>
                    </div>

                    <!-- Descarga plantilla -->
                    <div
                        class="bg-gray-50 border border-gray-200 p-4 rounded-xl shadow-sm"
                    >
                        <h2 class="text-base font-semibold mb-2 text-gray-700">
                            Plantilla de ejemplo
                        </h2>
                        <a
                            href="ruta/al/archivo.xlsx"
                            download="plantilla-vodafone.xlsx"
                            class="inline-flex items-center gap-2 text-[var(--colorPrincipal)] hover:text-blue-700 transition"
                        >
                            <svg
                                class="w-5 h-5"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 448 512"
                                fill="currentColor"
                            >
                                <path
                                    d="M416 176c0 8.8 7.2 16 16 16s16-7.2 16-16l0-80c0-53-43-96-96-96L96 0C43 0 0 43 0 96l0 80c0 8.8 7.2 16 16 16s16-7.2 16-16l0-80c0-35.3 
                            28.7-64 64-64l256 0c35.3 0 64 28.7 64 64l0 80zM212.7 507.3c6.2 6.2 16.4 6.2 22.6 0l144-144c6.2-6.2 6.2-16.4 
                            0-22.6s-16.4-6.2-22.6 0L240 457.4 240 176c0-8.8-7.2-16-16-16s-16 7.2-16 
                            16l0 281.4-116.7-116.7c-6.2-6.2-16.4-6.2-22.6 0s-6.2 16.4 0 22.6l144 144z"
                                />
                            </svg>
                            Descargar plantilla de Excel
                        </a>
                    </div>
                </div>
            </template>
        </ModalGestion>
        <!-- Modal DE ASIGNACION -->
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
            @close="cerrarModal"
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
