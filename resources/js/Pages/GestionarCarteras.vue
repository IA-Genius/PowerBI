<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ModalGestion from "@/Components/ModalGestion.vue";
import GestReportes from "@/Components/GestReportes.vue";
import Actions from "@/Components/Actions.vue";
import InputField from "@/Components/InputField.vue";
import StatusBadge from "@/Components/StatusBadge.vue";
import { Head, usePage, router } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";

const carteras = ref(usePage().props.carteras);
const reportes = usePage().props.reportes;
const success = usePage().props.success;

const showModal = ref(false);
const showModalReportes = ref(false);
const keyReportes = ref(Date.now());
const reportesFiltrados = ref([]);
const carteraSeleccionada = ref(null);

const carteraEditar = ref(null);
const carteraForm = ref({
    nombre: "",
    descripcion: "",
    orden: 0,
    estado: true,
});

onMounted(() => {
    if (success) {
        Swal.fire({
            toast: true,
            position: "top-end",
            icon: "success",
            title: success,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
        });
    }
});

function abrirModalAgregar() {
    carteraEditar.value = null;
    carteraForm.value = {
        nombre: "",
        descripcion: "",
        orden: 0,
        estado: true,
    };
    showModal.value = true;
}

function abrirModalEditar(cartera) {
    carteraEditar.value = cartera;
    carteraForm.value = {
        nombre: cartera.nombre,
        descripcion: cartera.descripcion,
        orden: cartera.orden,
        estado: !!cartera.estado,
    };
    showModal.value = true;
}

function cerrarModal() {
    showModal.value = false;
    carteraEditar.value = null;
}

function handleSuccess(message) {
    Swal.fire({
        toast: true,
        position: "top-end",
        icon: "success",
        title: message,
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
    });
    recargar();
}

function recargar() {
    router.visit(route("carteras.index"), {
        preserveScroll: true,
        only: ["carteras", "success"],
        onFinish: () => {
            carteras.value = usePage().props.carteras;
            cerrarModal();
        },
    });
}

function eliminarCartera(cartera) {
    Swal.fire({
        title: "¿Eliminar cartera?",
        text: `¿Estás seguro de eliminar "${cartera.nombre}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/carteras/${cartera.id}`, {
                onSuccess: () => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Cartera eliminada",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    });
                    recargar();
                },
                onError: () => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "error",
                        title: "No se pudo eliminar la cartera",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    });
                },
            });
        }
    });
}

function abrirModalReportes(cartera = null) {
    // Siempre usar los props más recientes
    const allReportes = usePage().props.reportes;
    if (cartera) {
        carteraSeleccionada.value = cartera;
        reportesFiltrados.value = allReportes.filter(
            (r) => r.cartera_id === cartera.id
        );
    } else {
        carteraSeleccionada.value = null;
        reportesFiltrados.value = allReportes;
    }
    showModalReportes.value = true;
}

function cerrarModalReportes() {
    showModalReportes.value = false;
}

function recargarReportes() {
    router.visit(route("carteras.index"), {
        preserveScroll: true,
        preserveState: true,
        only: ["reportes", "carteras", "success"],
        onFinish: () => {
            const allReportes = usePage().props.reportes;

            if (carteraSeleccionada.value) {
                reportesFiltrados.value = allReportes.filter(
                    (r) => r.cartera_id === carteraSeleccionada.value.id
                );
            } else {
                reportesFiltrados.value = allReportes;
            }

            showModalReportes.value = true;
        },
    });
}
</script>

<template>
    <Head title="Gestión de Carteras" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h2
                        class="text-xl font-semibold leading-tight text-gray-800"
                    >
                        Gestión de Carteras
                    </h2>
                    <span
                        class="text-white text-xs px-3 py-1 rounded-full ml-2"
                        style="background-color: #5f61ff"
                    >
                        {{ carteras.length }} carteras
                    </span>
                </div>
                <button
                    @click="abrirModalAgregar"
                    class="flex items-center gap-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-5 py-2 rounded-lg shadow-md hover:shadow-lg hover:brightness-110 hover:-translate-y-0.5 transition-all duration-300 ease-out"
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
                    Agregar Cartera
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto w-full">
                <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                    <div class="p-6">
                        <ModalGestion
                            :show="showModal"
                            :title="
                                carteraEditar
                                    ? 'Editar Cartera'
                                    : 'Agregar Cartera'
                            "
                            submitLabel="Guardar"
                            :initialForm="carteraForm"
                            :endpoint="
                                carteraEditar
                                    ? `/carteras/${carteraEditar.id}`
                                    : '/carteras'
                            "
                            :method="carteraEditar ? 'put' : 'post'"
                            @close="cerrarModal"
                            @success="handleSuccess"
                        >
                            <template #default="{ form, errors }">
                                <InputField
                                    label="Nombre"
                                    v-model="form.nombre"
                                    name="cartera_nombre"
                                    placeholder="Nombre"
                                    :error="errors.nombre"
                                />
                                <InputField
                                    label="Descripción"
                                    v-model="form.descripcion"
                                    name="cartera_descripcion"
                                    type="textarea"
                                    placeholder="Descripción"
                                    :error="errors.descripcion"
                                />
                                <InputField
                                    label="Orden"
                                    v-model="form.orden"
                                    name="cartera_orden"
                                    type="number"
                                    :min="0"
                                    :error="errors.orden"
                                />
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        for="estado"
                                        >Estado</label
                                    >
                                    <select
                                        id="estado"
                                        v-model="form.estado"
                                        class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                    >
                                        <option :value="true">Activa</option>
                                        <option :value="false">Inactiva</option>
                                    </select>
                                </div>
                            </template>
                        </ModalGestion>

                        <GestReportes
                            :key="keyReportes"
                            :show="showModalReportes"
                            :carteras="carteras"
                            :reportes="reportesFiltrados"
                            :cartera-seleccionada="carteraSeleccionada"
                            @close="cerrarModalReportes"
                            @recargarReportes="recargarReportes"
                        />

                        <div class="flex items-center justify-between mb-4">
                            <h1 class="text-xl font-semibold text-gray-800">
                                Listado de Carteras
                            </h1>
                            <button
                                @click="abrirModalReportes(null)"
                                class="flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-indigo-700 text-white px-4 py-1.5 rounded-md shadow-md hover:shadow-lg hover:brightness-110 hover:-translate-y-0.5 transition-all duration-300 ease-out"
                            >
                                <span class="font-bold text-xs tracking-wide"
                                    >VER TODOS REPORTES</span
                                >
                            </button>
                        </div>

                        <div
                            class="overflow-x-auto rounded-xl border border-gray-100 bg-gray-50"
                        >
                            <table
                                class="min-w-full divide-y divide-gray-200 text-sm"
                            >
                                <thead class="bg-indigo-100">
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-bold text-indigo-700 uppercase"
                                        >
                                            ID
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-bold text-indigo-700 uppercase"
                                        >
                                            Nombre
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-bold text-indigo-700 uppercase"
                                        >
                                            Descripción
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-bold text-indigo-700 uppercase"
                                        >
                                            Orden
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-bold text-indigo-700 uppercase"
                                        >
                                            Estado
                                        </th>
                                        <th
                                            class="px-4 py-2 text-center text-xs font-bold text-indigo-700 uppercase"
                                        >
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="cartera in carteras"
                                        :key="cartera.id"
                                        :class="[
                                            'bg-white',
                                            'even:bg-indigo-50',
                                            'hover:bg-indigo-100 transition',
                                        ]"
                                    >
                                        <td
                                            class="px-4 py-2 text-gray-700 text-[13px]"
                                        >
                                            {{ cartera.id }}
                                        </td>
                                        <td
                                            class="px-4 py-2 text-gray-800 text-[13px]"
                                        >
                                            {{ cartera.nombre }}
                                        </td>
                                        <td
                                            class="px-4 py-2 text-gray-700 text-[13px]"
                                        >
                                            {{ cartera.descripcion }}
                                        </td>
                                        <td
                                            class="px-4 py-2 text-gray-700 text-[13px]"
                                        >
                                            {{ cartera.orden }}
                                        </td>
                                        <td
                                            class="px-4 py-2 text-gray-700 text-[13px]"
                                        >
                                            <StatusBadge
                                                :active="!!cartera.estado"
                                            />
                                        </td>
                                        <td
                                            class="px-4 py-2 text-center text-[13px]"
                                        >
                                            <Actions
                                                :edit="true"
                                                :remove="true"
                                                :list="true"
                                                @edit="
                                                    abrirModalEditar(cartera)
                                                "
                                                @delete="
                                                    eliminarCartera(cartera)
                                                "
                                                @list="
                                                    abrirModalReportes(cartera)
                                                "
                                            />
                                        </td>
                                    </tr>
                                    <tr v-if="carteras.length === 0">
                                        <td
                                            colspan="6"
                                            class="text-center py-4 text-gray-400 text-lg"
                                        >
                                            No hay carteras registradas.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
