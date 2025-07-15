<template>
    <Head title="Gestión de Roles" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">
                    Gestión de Roles
                </h2>
                <button
                    @click="abrirModalAgregar"
                    class="flex items-center gap-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-5 py-2 rounded-lg shadow-md hover:shadow-lg hover:brightness-110 hover:-translate-y-0.5 transition-all duration-300 ease-out"
                >
                    Agregar Rol
                </button>
            </div>
        </template>

        <!-- Modal para agregar/editar rol -->
        <ModalXts
            :show="showModal"
            :title="rolEditar ? 'Editar Rol' : 'Agregar Rol'"
            :submitLabel="rolEditar ? 'Actualizar' : 'Registrar'"
            :form="rolForm"
            :endpoint="rolEditar ? `/roles/${rolEditar.id}` : '/roles'"
            :method="rolEditar ? 'put' : 'post'"
            :transform="
                (form) => ({
                    ...form,
                    carteras: Array.isArray(form.carteras)
                        ? form.carteras.map((c) => c.id ?? c)
                        : [],
                    reportes: Array.isArray(form.reportes)
                        ? form.reportes.map((r) => r.id ?? r)
                        : [],
                })
            "
            :carteras="carterasConReportes"
            :reportes="reportes"
            @close="cerrarModal"
            @success="handleSuccess"
        >
            <template #default="{ form, errors }">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Nombre del rol</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="mt-1 block w-full border-gray-300 rounded shadow-sm"
                        />
                        <p v-if="errors.name" class="text-red-600 text-sm">
                            {{ errors.name }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Selecciona carteras</label
                        >
                        <Multiselect
                            v-model="form.carteras"
                            :options="carterasConReportes"
                            :multiple="true"
                            :track-by="'id'"
                            :label="'nombre'"
                            placeholder="Selecciona carteras"
                            class="w-full"
                        />
                        <p v-if="errors.carteras" class="text-red-600 text-sm">
                            {{ errors.carteras }}
                        </p>
                    </div>
                    <div
                        v-if="form.carteras.length"
                        class="space-y-6"
                        style="max-height: 217px; overflow-y: auto"
                    >
                        <div
                            v-for="cartera in form.carteras"
                            :key="cartera.id"
                            class="border border-indigo-100 rounded-lg p-4 bg-indigo-50/50 shadow-sm"
                        >
                            <div
                                class="font-semibold text-indigo-700 text-sm flex items-center gap-2 mb-2"
                            >
                                <svg
                                    class="w-4 h-4 text-indigo-400"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3 7h18"
                                    />
                                </svg>
                                {{ cartera.nombre }}
                            </div>
                            <div
                                v-if="
                                    cartera.reportes && cartera.reportes.length
                                "
                            >
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2"
                                >
                                    <div
                                        v-for="reporte in cartera.reportes"
                                        :key="reporte.id"
                                        class="flex items-center gap-2 bg-white rounded px-2 py-1 shadow-sm"
                                    >
                                        <input
                                            type="checkbox"
                                            :id="
                                                'reporte-' +
                                                cartera.id +
                                                '-' +
                                                reporte.id
                                            "
                                            :checked="
                                                form.reportes.some(
                                                    (r) => r.id === reporte.id
                                                )
                                            "
                                            @change="
                                                toggleReporteCartera(
                                                    reporte,
                                                    $event.target.checked
                                                )
                                            "
                                            class="accent-indigo-500"
                                        />
                                        <label
                                            :for="
                                                'reporte-' +
                                                cartera.id +
                                                '-' +
                                                reporte.id
                                            "
                                            class="text-xs text-gray-700 cursor-pointer truncate max-w-[120px]"
                                            :title="reporte.nombre"
                                        >
                                            {{ reporte.nombre }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div
                                v-else
                                class="text-xs text-gray-400 italic mt-2"
                            >
                                Esta cartera no tiene reportes disponibles.
                            </div>
                        </div>
                    </div>
                    <div
                        v-if="form.reportes.length"
                        class="mt-2 flex items-center justify-start"
                    >
                        <label
                            class="block text-xs font-semibold text-gray-500 mb-1 mr-2"
                            >Cantidad de reportes asignados:</label
                        >
                        <span
                            class="bg-indigo-100 text-indigo-700 rounded px-2 py-1 text-xs"
                            >{{ form.reportes.length }}</span
                        >
                    </div>
                </div>
            </template>
        </ModalXts>

        <!-- Listado de roles -->
        <div class="mt-6">
            <h1 class="text-xl font-semibold text-gray-800 mb-4">
                Listado de Roles
            </h1>
            <div
                class="overflow-x-auto rounded-xl border border-gray-100 bg-gray-50"
            >
                <table class="min-w-full divide-y divide-gray-200 text-sm">
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
                                Carteras
                            </th>
                            <th
                                class="px-4 py-2 text-left text-xs font-bold text-indigo-700 uppercase"
                            >
                                Reportes
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
                            v-for="(role, $index) in roles"
                            :key="role.id"
                            :class="[
                                $index % 2 === 0 ? 'bg-white' : 'bg-indigo-50',
                                'hover:bg-indigo-100 transition',
                            ]"
                        >
                            <td class="px-4 py-2 text-gray-700 text-[13px]">
                                {{ role.id }}
                            </td>
                            <td class="px-4 py-2 text-gray-700 text-[13px]">
                                {{ role.name }}
                            </td>
                            <td class="px-4 py-2 text-gray-700 text-[13px]">
                                <span
                                    v-for="c in role.carteras"
                                    :key="c.id"
                                    class="inline-block bg-indigo-100 text-indigo-700 rounded px-2 py-1 text-xs mr-1 mb-1"
                                    >{{ c.nombre }}</span
                                >
                            </td>
                            <td class="px-4 py-2 text-gray-700 text-[13px]">
                                <span
                                    v-for="r in role.reportes"
                                    :key="r.id"
                                    class="inline-block bg-blue-100 text-blue-700 rounded px-2 py-1 text-xs mr-1 mb-1"
                                    >{{ r.nombre }}</span
                                >
                            </td>
                            <td class="px-4 py-2 text-center">
                                <button
                                    @click="abrirModalEditar(role)"
                                    class="text-indigo-600 hover:text-indigo-900 mr-2"
                                >
                                    Editar
                                </button>
                                <button
                                    @click="eliminarRol(role)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                        <tr v-if="roles.length === 0">
                            <td
                                colspan="5"
                                class="text-center py-4 text-gray-400 text-lg"
                            >
                                No hay roles registrados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ModalXts from "@/Components/ModalXts.vue";
import Multiselect from "vue-multiselect";
import { Head, usePage, router } from "@inertiajs/vue3";
import { ref, reactive, onMounted } from "vue";
import Swal from "sweetalert2";
import "vue-multiselect/dist/vue-multiselect.css";

const roles = usePage().props.roles;
const carteras = usePage().props.carteras || [];
const reportes = usePage().props.reportes || [];
const success = usePage().props.success;

const showModal = ref(false);
const rolEditar = ref(null);

const rolForm = reactive({
    name: "",
    carteras: [],
    reportes: [],
});

const carterasConReportes = carteras.map((c) => ({
    ...c,
    reportes: reportes.filter((r) => r.cartera_id === c.id),
}));

console.log("Carteras:", carteras);
console.log("Reportes:", reportes);
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
    Object.assign(rolForm, {
        name: "",
        carteras: [],
        reportes: [],
    });
    rolEditar.value = null;
    showModal.value = true;
}

function abrirModalEditar(role) {
    rolEditar.value = role;
    // Obtener carteras seleccionadas
    const carterasSeleccionadas = carteras.filter((c) =>
        role.carteras?.some((uc) => uc.id === c.id)
    );
    // Obtener reportes seleccionados desde esas carteras (usando los objetos originales)
    const reportesDeCarteras = carterasSeleccionadas.flatMap(
        (c) => c.reportes || []
    );
    const reportesSeleccionados = reportesDeCarteras.filter((r) =>
        role.reportes.some((ur) => ur.id === r.id)
    );
    Object.assign(rolForm, {
        name: role.name,
        carteras: carterasSeleccionadas,
        reportes: reportesSeleccionados,
    });
    showModal.value = true;
}

function cerrarModal() {
    showModal.value = false;
    rolEditar.value = null;
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
    router.visit(route("roles.index"), {
        preserveScroll: true,
        only: ["roles", "success"],
        onFinish: () => cerrarModal(),
    });
}

function eliminarRol(role) {
    Swal.fire({
        title: "¿Eliminar rol?",
        text: `¿Estás seguro de eliminar el rol ${role.name}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/roles/${role.id}`, {
                onSuccess: () => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Rol eliminado",
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
                        title: "No se pudo eliminar el rol",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    });
                },
            });
        }
    });
}

function toggleReporteCartera(reporte, checked) {
    if (checked) {
        if (!rolForm.reportes.some((r) => r.id === reporte.id))
            rolForm.reportes.push(reporte);
    } else {
        rolForm.reportes = rolForm.reportes.filter((r) => r.id !== reporte.id);
    }
}
</script>
