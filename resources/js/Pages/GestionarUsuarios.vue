<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ModalXts from "@/Components/ModalXts.vue";
import Actions from "@/Components/Actions.vue";
import InputField from "@/Components/InputField.vue";
import StatusBadge from "@/Components/StatusBadge.vue";
import { Head, usePage, router } from "@inertiajs/vue3";
import { reactive, ref, computed, onMounted, watch } from "vue";
import Swal from "sweetalert2";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";

const users = usePage().props.users;
const success = usePage().props.success;
const carteras = usePage().props.carteras || [];

const showModal = ref(false);
const usuarioEditar = ref(null);
const tabActiva = ref("basicos");

const usuarioForm = reactive({
    name: "",
    email: "",
    password: "",
    active: true,
    carteras: [],
    reportes: [],
});

const reportesFiltradosPorCarteras = computed(() => {
    if (!usuarioForm.carteras || !usuarioForm.carteras.length) return [];
    const reportes = usuarioForm.carteras.flatMap((c) =>
        c && c.reportes ? c.reportes : []
    );
    return Array.from(new Map(reportes.map((r) => [r.id, r])).values());
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
    Object.assign(usuarioForm, {
        name: "",
        email: "",
        password: "",
        active: true,
        carteras: [],
        reportes: [],
    });
    usuarioEditar.value = null;
    tabActiva.value = "basicos";
    showModal.value = true;
}

function abrirModalEditar(user) {
    usuarioEditar.value = user;

    // Obtener carteras seleccionadas
    const carterasSeleccionadas = carteras.filter((c) =>
        user.carteras?.some((uc) => uc.id === c.id)
    );

    // Obtener reportes seleccionados desde esas carteras (usando los objetos originales)
    const reportesDeCarteras = carterasSeleccionadas.flatMap(
        (c) => c.reportes || []
    );

    const reportesSeleccionados = reportesDeCarteras.filter((r) =>
        user.reportes.some((ur) => ur.id === r.id)
    );

    Object.assign(usuarioForm, {
        name: user.name,
        email: user.email,
        password: "",
        active: !!user.active,
        carteras: carterasSeleccionadas,
        reportes: reportesSeleccionados, // Esto ya estará sincronizado por id y referencia
    });

    tabActiva.value = "basicos";
    showModal.value = true;
}

function cerrarModal() {
    showModal.value = false;
    usuarioEditar.value = null;
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
    router.visit(route("users.index"), {
        preserveScroll: true,
        only: ["users", "success"],
        onFinish: () => cerrarModal(),
    });
}

function eliminarUsuario(user) {
    Swal.fire({
        title: "¿Eliminar usuario?",
        text: `¿Estás seguro de eliminar a ${user.name}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/users/${user.id}`, {
                onSuccess: () => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Usuario eliminado",
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
                        title: "No se pudo eliminar el usuario",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    });
                },
            });
        }
    });
}

watch(
    () => usuarioForm.carteras,
    (nuevasCarteras) => {
        const nuevosReportesDisponibles = nuevasCarteras.length
            ? nuevasCarteras.flatMap((c) => c.reportes || [])
            : [];
        const nuevosIds = new Set(nuevosReportesDisponibles.map((r) => r.id));
        usuarioForm.reportes = usuarioForm.reportes.filter((r) =>
            nuevosIds.has(r.id)
        );
    }
);
// --- Métodos para selección granular de

function seleccionarTodosReportesCartera(cartera) {
    cartera.reportes.forEach((r) => {
        if (!usuarioForm.reportes.some((sel) => sel.id === r.id))
            usuarioForm.reportes.push(r);
    });
}
function toggleReporteCartera(reporte, checked) {
    if (checked) {
        if (!usuarioForm.reportes.some((r) => r.id === reporte.id))
            usuarioForm.reportes.push(reporte);
    } else {
        usuarioForm.reportes = usuarioForm.reportes.filter(
            (r) => r.id !== reporte.id
        );
    }
}
</script>

<template>
    <Head title="Gestión de Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">
                    Gestión de Usuarios
                </h2>
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
                    Agregar Usuario
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl">
                <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                    <div class="p-6">
                        <ModalXts
                            :show="showModal"
                            :title="
                                usuarioEditar
                                    ? 'Editar Usuario'
                                    : 'Agregar Usuario'
                            "
                            :submitLabel="
                                usuarioEditar ? 'Actualizar' : 'Registrar'
                            "
                            :form="usuarioForm"
                            :endpoint="
                                usuarioEditar
                                    ? `/users/${usuarioEditar.id}`
                                    : '/users'
                            "
                            :method="usuarioEditar ? 'put' : 'post'"
                            :transform="
                                (form) => ({
                                    ...form,
                                    carteras: form.carteras.map((c) => c.id),
                                    reportes: form.reportes.map((r) => r.id),
                                })
                            "
                            @close="cerrarModal"
                            @success="handleSuccess"
                        >
                            <template #default="{ form, errors }">
                                <!-- Tabs -->
                                <div class="mb-6">
                                    <nav
                                        class="flex gap-2 w-full bg-indigo-50 rounded-lg p-1 mx-auto shadow-sm"
                                    >
                                        <button
                                            @click="tabActiva = 'basicos'"
                                            type="button"
                                            :class="[
                                                'px-6 py-2 w-full rounded-md text-sm font-semibold transition-all duration-200 focus:outline-none',
                                                tabActiva === 'basicos'
                                                    ? 'bg-white text-indigo-700 shadow border border-indigo-200'
                                                    : 'bg-transparent text-gray-500 hover:bg-white hover:text-indigo-600',
                                            ]"
                                        >
                                            Datos Básicos
                                        </button>
                                        <button
                                            @click="tabActiva = 'carteras'"
                                            type="button"
                                            :class="[
                                                'px-6 py-2 w-full rounded-md text-sm font-semibold transition-all duration-200 focus:outline-none',
                                                tabActiva === 'carteras'
                                                    ? 'bg-white text-indigo-700 shadow border border-indigo-200'
                                                    : 'bg-transparent text-gray-500 hover:bg-white hover:text-indigo-600',
                                            ]"
                                        >
                                            Carteras y Reportes
                                        </button>
                                    </nav>
                                </div>

                                <!-- Contenido del tab -->
                                <div v-if="tabActiva === 'basicos'">
                                    <InputField
                                        label="Nombre"
                                        v-model="form.name"
                                        :error="errors.name"
                                    />
                                    <InputField
                                        label="Email"
                                        v-model="form.email"
                                        :error="errors.email"
                                    />
                                    <InputField
                                        :label="
                                            usuarioEditar
                                                ? 'Nueva contraseña (opcional)'
                                                : 'Contraseña'
                                        "
                                        v-model="form.password"
                                        type="password"
                                        :error="errors.password"
                                    />
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                        >
                                            Estado
                                        </label>
                                        <select
                                            v-model="form.active"
                                            class="w-full border rounded px-3 py-2"
                                        >
                                            <option :value="true">
                                                Activo
                                            </option>
                                            <option :value="false">
                                                Inactivo
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div v-if="tabActiva === 'carteras'">
                                    <div class="mb-4">
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                            >Selecciona carteras</label
                                        >
                                        <Multiselect
                                            v-model="form.carteras"
                                            :options="carteras"
                                            :multiple="true"
                                            :track-by="'id'"
                                            :label="'nombre'"
                                            placeholder="Selecciona carteras"
                                            class="w-full"
                                        />
                                    </div>
                                    <div
                                        v-if="form.carteras.length"
                                        class="space-y-6"
                                        style="
                                            max-height: 217px;
                                            overflow-y: auto;
                                        "
                                    >
                                        <div
                                            v-for="cartera in form.carteras"
                                            :key="cartera.id"
                                            class="border border-indigo-100 rounded-lg p-4 bg-indigo-50/50 shadow-sm"
                                        >
                                            <div
                                                class="flex items-center justify-between mb-2"
                                            >
                                                <span
                                                    class="font-semibold text-indigo-700 text-sm flex items-center gap-2"
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
                                                </span>
                                                <button
                                                    v-if="
                                                        cartera.reportes &&
                                                        cartera.reportes.length
                                                    "
                                                    type="button"
                                                    class="text-xs px-2 py-1 bg-emerald-500 text-white rounded hover:bg-emerald-600 transition"
                                                    @click="
                                                        seleccionarTodosReportesCartera(
                                                            cartera
                                                        )
                                                    "
                                                >
                                                    Seleccionar todos
                                                </button>
                                            </div>
                                            <div
                                                v-if="
                                                    cartera.reportes &&
                                                    cartera.reportes.length
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
                                                                    (r) =>
                                                                        r.id ===
                                                                        reporte.id
                                                                )
                                                            "
                                                            @change="
                                                                toggleReporteCartera(
                                                                    reporte,
                                                                    $event
                                                                        .target
                                                                        .checked
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
                                                            :title="
                                                                reporte.nombre
                                                            "
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
                                                Esta cartera no tiene reportes
                                                disponibles.
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        v-if="form.reportes.length"
                                        class="mt-2 flex items-center justify-start"
                                    >
                                        <label
                                            class="block text-xs font-semibold text-gray-500 mb-1 mr-2"
                                        >
                                            Cantidad de reportes asignados:
                                        </label>
                                        <span
                                            class="bg-indigo-100 text-indigo-700 rounded px-2 py-1 text-xs"
                                        >
                                            {{ form.reportes.length }}
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </ModalXts>

                        <!-- Listado de usuarios -->
                        <div class="mt-6">
                            <h1
                                class="text-xl font-semibold text-gray-800 mb-4"
                            >
                                Listado de Usuarios
                            </h1>
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
                                                Email
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
                                            v-for="(user, $index) in users"
                                            :key="user.id"
                                            :class="[
                                                $index % 2 === 0
                                                    ? 'bg-white'
                                                    : 'bg-indigo-50',
                                                'hover:bg-indigo-100 transition',
                                            ]"
                                        >
                                            <td
                                                class="px-4 py-2 text-gray-700 text-[13px]"
                                            >
                                                {{ user.id }}
                                            </td>
                                            <td
                                                class="px-4 py-2 text-gray-700 text-[13px]"
                                            >
                                                {{ user.name }}
                                            </td>
                                            <td
                                                class="px-4 py-2 text-gray-700 text-[13px]"
                                            >
                                                {{ user.email }}
                                            </td>
                                            <td
                                                class="px-4 py-2 text-gray-700 text-[13px]"
                                            >
                                                <StatusBadge
                                                    :active="!!user.active"
                                                />
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                <Actions
                                                    :edit="true"
                                                    :remove="true"
                                                    @edit="
                                                        abrirModalEditar(user)
                                                    "
                                                    @delete="
                                                        eliminarUsuario(user)
                                                    "
                                                />
                                            </td>
                                        </tr>
                                        <tr v-if="users.length === 0">
                                            <td
                                                colspan="5"
                                                class="text-center py-4 text-gray-400 text-lg"
                                            >
                                                No hay usuarios registrados.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
