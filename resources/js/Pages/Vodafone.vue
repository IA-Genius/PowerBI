<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ModalGestion from "@/Components/ModalGestion.vue";
import InputField from "@/Components/InputField.vue";
import Actions from "@/Components/Actions.vue";
import Dropdown from "@/Components/Dropdown.vue";

const pageProps = usePage().props;

const items = computed(() => pageProps.items || []);
const success = pageProps.success;
const canViewGlobal = pageProps.canViewGlobal;
const can = usePage().props.can || {};
const canDo = (key) => !!can[key];
import FiltroFlotante from "@/Components/FiltroFlotante.vue";

const showModal = ref(false);
const registroEditar = ref(null);

const filters = usePage().props.filters || {};

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

function abrirModalEditar(item) {
    registroEditar.value = item;
    form.value = { ...item };
    showModal.value = true;
}

function cerrarModal() {
    showModal.value = false;
    registroEditar.value = null;
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

console.log(items.value);

const showFiltro = ref(false);
const filtrosActivos = ref({});

const rawItems = computed(() => pageProps.items || []);

const filtrosDisponibles = computed(() => {
    const list = rawItems.value;
    return {
        operador_actual: [
            ...new Set(list.map((i) => i.operador_actual).filter(Boolean)),
        ],
        tipificaciones: [
            ...new Set(list.map((i) => i.tipificaciones).filter(Boolean)),
        ],
        trasavilidad: [
            ...new Set(list.map((i) => i.trasavilidad).filter(Boolean)),
        ],
        estado: [...new Set(list.map((i) => i.estado).filter(Boolean))],
        asignado: [...new Set(list.map((i) => i.asignado).filter(Boolean))],
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

                <!-- Botón y modal de filtros -->
                <div class="relative">
                    <button
                        @click="showFiltro = !showFiltro"
                        class="bg-white px-3 py-1 border rounded shadow text-sm"
                    >
                        Filtros
                    </button>
                    <FiltroFlotante
                        v-if="showFiltro"
                        :filtros="filtrosDisponibles"
                        @filtrar="aplicarFiltros"
                        @close="showFiltro = false"
                    />
                </div>

                <!-- Input con buscador y spinner -->
                <div
                    class="flex flex-col sm:flex-row items-stretch gap-3 sm:gap-4 mt-4 sm:mt-0"
                >
                    <!-- Botón agregar -->
                    <button
                        v-if="canDo('vodafone.crear')"
                        @click="abrirModalAgregar"
                        class="flex items-center justify-center gap-2 bg-green-500 text-white px-5 py-2 rounded-md shadow hover:bg-green-600 transition text-sm font-semibold"
                    >
                        <svg
                            class="w-5 h-5"
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
                        Agregar
                    </button>

                    <!-- Botón agregar -->
                    <button
                        v-if="canDo('vodafone.crear')"
                        @click="abrirModalAgregar"
                        class="flex items-center justify-center gap-2 bg-green-500 text-white px-5 py-2 rounded-md shadow hover:bg-green-600 transition text-sm font-semibold"
                    >
                        <svg
                            class="w-5 h-5"
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
                        Importa
                    </button>

                    <!-- Botón agregar -->
                    <button
                        v-if="canDo('vodafone.crear')"
                        @click="abrirModalAgregar"
                        class="flex items-center justify-center gap-2 bg-green-500 text-white px-5 py-2 rounded-md shadow hover:bg-green-600 transition text-sm font-semibold"
                    >
                        <svg
                            class="w-5 h-5"
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
                        Asignar Filtrador
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div
                class="overflow-x-auto rounded-xl border border-gray-100 bg-gray-50"
            >
                <table class="min-w-full text-sm text-gray-800">
                    <thead class="text-white text-xs bgPrincipal">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Nro. Carga</th>
                            <th class="px-4 py-2 text-left">Fecha de Carga</th>
                            <th class="px-4 py-2 text-left">DNI</th>
                            <th class="px-4 py-2 text-left">Nombre</th>
                            <th class="px-4 py-2 text-left">Teléfono</th>
                            <th class="px-4 py-2 text-left">Estado</th>
                            <th class="px-4 py-2 text-left">Correo</th>
                            <th class="px-4 py-2 text-left">Dirección</th>
                            <th class="px-4 py-2 text-left">Contacto</th>
                            <th class="px-4 py-2 text-left">Asignado</th>
                            <th class="px-4 py-2 text-left">Fecha Asignada</th>
                            <th class="px-4 py-2 text-left">Operador Actual</th>
                            <th class="px-4 py-2 text-left">Trasavilidad</th>
                            <th class="px-4 py-2 text-left">
                                Oferta Comercial
                            </th>
                            <th class="px-4 py-2 text-left">
                                Observación SMART
                            </th>
                            <th class="px-4 py-2 text-left">Tipificaciones</th>
                            <th
                                v-if="canViewGlobal"
                                class="px-4 py-2 text-center"
                            >
                                Usuario
                            </th>
                            <th class="px-4 py-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in filteredItems"
                            :key="item.id"
                            class="bg-white even:bg-indigo-50 hover:bg-indigo-100 transition"
                        >
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.id }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.dni_nif_cif }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.nombre_cliente }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.telefono_contacto }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.direccion_instalacion }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.operador_actual }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.oferta_comercial }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.observacion_smart }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.tipificaciones }}
                            </td>
                            <td
                                v-if="canViewGlobal"
                                class="px-4 py-1.5 text-center"
                            >
                                {{ item.user ? item.user.name : "Sin usuario" }}
                                <button
                                    v-if="item.user"
                                    @click="mostrarInfoUsuario(item.user)"
                                    class="text-blue-600 hover:text-blue-800"
                                    title="Ver info usuario"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 inline"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            fill="none"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 16v-4m0-4h.01"
                                        />
                                    </svg>
                                </button>
                            </td>
                            <td class="px-4 py-1.5 text-center text-[13px]">
                                <Actions
                                    :edit="canDo('vodafone.editar')"
                                    :remove="canDo('vodafone.eliminar')"
                                    @edit="abrirModalEditar(item)"
                                    @delete="eliminar(item)"
                                />
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td
                                :colspan="canViewGlobal ? 11 : 10"
                                class="text-center py-4 text-gray-400 text-lg"
                            >
                                No hay registros.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
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
    </AuthenticatedLayout>
</template>
