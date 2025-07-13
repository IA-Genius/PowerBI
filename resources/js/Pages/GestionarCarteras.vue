<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ModalGestion from "@/Components/ModalGestion.vue";
import GestReportes from "@/Components/GestReportes.vue";
import { Head, usePage, router } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";

// --- State ---
const carteras = ref(usePage().props.carteras);
const reportes = usePage().props.reportes;
const success = usePage().props.success;
const showModal = ref(false);
const showModalReportes = ref(false);
const keyReportes = ref(Date.now());
const carteraEditar = ref(null);
const carteraForm = ref({
    nombre: "",
    descripcion: "",
    orden: 0,
    estado: true,
});

// --- Lifecycle ---
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

// --- Methods ---
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

function abrirModalReportes() {
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
            <div class="mx-auto max-w-7xl">
                <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                    <div class="p-6">
                        <!-- Modal para agregar/editar cartera -->
                        <ModalGestion
                            :show="showModal"
                            :title="
                                carteraEditar
                                    ? 'Editar Cartera'
                                    : 'Agregar Cartera'
                            "
                            :submitLabel="'Guardar'"
                            :initialForm="carteraForm"
                            :endpoint="
                                carteraEditar && carteraEditar.id
                                    ? `/carteras/${carteraEditar.id}`
                                    : '/carteras'
                            "
                            :method="
                                carteraEditar && carteraEditar.id
                                    ? 'put'
                                    : 'post'
                            "
                            @close="cerrarModal"
                            @success="handleSuccess"
                        >
                            <template #default="{ form, errors }">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Nombre</label
                                    >
                                    <input
                                        v-model="form.nombre"
                                        type="text"
                                        name="cartera_nombre"
                                        placeholder="Nombre"
                                        autocomplete="off"
                                        class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                    />
                                    <div
                                        v-if="errors.nombre"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ errors.nombre }}
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Descripción</label
                                    >
                                    <textarea
                                        v-model="form.descripcion"
                                        name="cartera_descripcion"
                                        placeholder="Descripción"
                                        autocomplete="off"
                                        class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none resize-none"
                                    ></textarea>
                                    <div
                                        v-if="errors.descripcion"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ errors.descripcion }}
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Orden</label
                                    >
                                    <input
                                        v-model="form.orden"
                                        type="number"
                                        name="cartera_orden"
                                        min="0"
                                        autocomplete="off"
                                        class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                    />
                                    <div
                                        v-if="errors.orden"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ errors.orden }}
                                    </div>
                                </div>
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
                            :reportes="reportes"
                            @close="cerrarModalReportes"
                            @recargarReportes="recargarReportes"
                        />

                        <div class="flex items-center justify-between mb-4">
                            <h1 class="text-xl font-semibold text-gray-800">
                                Listado de Carteras
                            </h1>
                            <button
                                class="flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-indigo-700 text-white px-4 py-1.5 rounded-md shadow-md hover:shadow-lg hover:brightness-110 hover:-translate-y-0.5 transition-all duration-300 ease-out"
                                title="Ver listado de reportes"
                                @click="abrirModalReportes"
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
                                        class="bg-white even:bg-indigo-50 hover:bg-indigo-100 transition"
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
                                            <span
                                                :class="
                                                    cartera.estado
                                                        ? 'bg-green-100 text-green-700'
                                                        : 'bg-red-100 text-red-700'
                                                "
                                                class="inline-block px-3 py-1 rounded-full text-xs font-semibold"
                                            >
                                                {{
                                                    cartera.estado
                                                        ? "Activa"
                                                        : "Inactiva"
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-4 py-2 text-center text-[13px]"
                                        >
                                            <div
                                                class="flex justify-center gap-2"
                                            >
                                                <button
                                                    @click="
                                                        abrirModalEditar(
                                                            cartera
                                                        )
                                                    "
                                                    class="p-1.5 rounded-full bg-yellow-400 hover:bg-yellow-500 text-white shadow-md transition transform hover:scale-105"
                                                    title="Editar Cartera"
                                                >
                                                    <svg
                                                        class="w-4 h-4"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M15.232 5.232l3.536 3.536M9 13l6.293-6.293a1 1 0 011.414 0l3.586 3.586a1 1 0 010 1.414L13 17H9v-4z"
                                                        />
                                                    </svg>
                                                </button>
                                                <button
                                                    @click="
                                                        eliminarCartera(cartera)
                                                    "
                                                    class="p-1.5 rounded-full bg-red-600 hover:bg-red-700 text-white shadow-md transition transform hover:scale-105"
                                                    title="Eliminar Cartera"
                                                >
                                                    <svg
                                                        class="w-4 h-4"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M6 18L18 6M6 6l12 12"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>
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
