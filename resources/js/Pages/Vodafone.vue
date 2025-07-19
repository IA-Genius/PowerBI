<template>
    <AuthenticatedLayout class="relleno">
        <!-- Header -->
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold tituloPag">
                    Gestionar Vodafone
                </h2>
                <button
                    @click="abrirModalAgregar"
                    class="flex items-center gap-2 bg-green-500 text-white px-5 py-2 rounded shadow hover:bg-green-600 transition"
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
            </div>
        </template>

        <!-- Tabla -->
        <div class="py-6">
            <div
                class="overflow-x-auto rounded-xl border border-gray-100 bg-gray-50"
            >
                <table class="min-w-full text-sm text-gray-800">
                    <thead class="text-white text-xs bgPrincipal">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Cliente</th>
                            <th class="px-4 py-2 text-left">Teléfono</th>
                            <th class="px-4 py-2 text-left">Operador</th>
                            <th class="px-4 py-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in items"
                            :key="item.id"
                            class="bg-white even:bg-indigo-50 hover:bg-indigo-100 transition"
                        >
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.id }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.nombre_cliente }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.telefono_contacto }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.operador_actual }}
                            </td>
                            <td class="px-4 py-1.5 text-center text-[13px]">
                                <Actions
                                    :edit="true"
                                    :remove="true"
                                    @edit="abrirModalEditar(item)"
                                    @delete="eliminar(item)"
                                />
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td
                                colspan="5"
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
                registroEditar ? `/vodafone/${registroEditar.id}` : '/vodafone'
            "
            :method="registroEditar ? 'put' : 'post'"
            @close="cerrarModal"
            @success="handleSuccess"
        >
            <template #default="{ form: slotForm, errors }">
                <InputField
                    label="Nombre Cliente"
                    v-model="slotForm.nombre_cliente"
                    name="nombre_cliente"
                    :error="errors.nombre_cliente"
                    class="modalInputs"
                    required
                />
                <InputField
                    label="DNI / NIF / CIF"
                    v-model="slotForm.dni_nif_cif"
                    name="dni_nif_cif"
                    class="modalInputs"
                    :error="errors.dni_nif_cif"
                />
                <InputField
                    label="Teléfono"
                    v-model="slotForm.telefono_contacto"
                    name="telefono_contacto"
                    class="modalInputs"
                    :error="errors.telefono_contacto"
                />
                <InputField
                    label="Dirección"
                    v-model="slotForm.direccion_instalacion"
                    name="direccion_instalacion"
                    class="modalInputs"
                    :error="errors.direccion_instalacion"
                />
                <InputField
                    label="Operador Actual"
                    v-model="slotForm.operador_actual"
                    name="operador_actual"
                    class="modalInputs"
                    :error="errors.operador_actual"
                />
                <InputField
                    label="Oferta Comercial"
                    v-model="slotForm.oferta_comercial"
                    name="oferta_comercial"
                    class="modalInputs"
                    :error="errors.oferta_comercial"
                />
                <InputField
                    label="Observación SMART"
                    v-model="slotForm.observacion_smart"
                    name="observacion_smart"
                    class="modalInputs"
                    :error="errors.observacion_smart"
                />
                <InputField
                    label="Tipificaciones"
                    v-model="slotForm.tipificaciones"
                    name="tipificaciones"
                    class="modalInputs"
                    :error="errors.tipificaciones"
                />
            </template>
        </ModalGestion>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import Swal from "sweetalert2";

// Layout y componentes
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ModalGestion from "@/Components/ModalGestion.vue";
import InputField from "@/Components/InputField.vue";
import Actions from "@/Components/Actions.vue";

// Props de backend
const pagination = ref(usePage().props.items);
const items = computed(() => pagination.value?.data || []);

const success = usePage().props.success;

// Control de modal y formulario
const showModal = ref(false);
const registroEditar = ref(null);

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

// Toast al cargar si hay success
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

// Funciones de acción
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
    if (!item.id) return;
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
    recargar();
}

function recargar() {
    router.visit(route("vodafone.index"), {
        preserveScroll: true,
        only: ["items", "success"],
        onFinish: () => {
            items.value = usePage().props.items;
            cerrarModal();
        },
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
</script>
