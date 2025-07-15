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

const { users, carteras, roles, success } = usePage().props;

const showModal = ref(false);
const usuarioEditar = ref(null);
const tabActiva = ref("basicos");

const usuarioForm = reactive({
    name: "",
    email: "",
    password: "",
    active: true,
    roles: null, // ahora es un solo objeto rol
    customCarteras: [],
    customReportes: [],
});

// Reportes heredados del rol seleccionado
const inheritedReportes = computed(() => {
    if (!usuarioForm.roles) return [];
    return usuarioForm.roles.reportes || [];
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
function resetearACarterasYReportesPorDefecto() {
    if (!usuarioForm.roles) return;

    Swal.fire({
        title: "¿Restablecer permisos?",
        text: "Se reemplazarán las carteras y reportes actuales por los del rol seleccionado. ¿Deseas continuar?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, restablecer",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            const carterasRol = usuarioForm.roles.carteras || [];

            const carterasCompletas = carterasRol.map((cartera) => {
                const encontrada = carteras.find((c) => c.id === cartera.id);
                return encontrada ? { ...encontrada } : { ...cartera };
            });

            const reportesDesdeCarteras = carterasCompletas
                .flatMap((c) => c.reportes || [])
                .filter(
                    (r, i, self) => self.findIndex((x) => x.id === r.id) === i
                );

            usuarioForm.customCarteras = carterasCompletas;
            usuarioForm.customReportes = reportesDesdeCarteras;

            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Permisos restablecidos",
                showConfirmButton: false,
                timer: 1500,
            });
        }
    });
}

watch(
    () => usuarioForm.roles,
    (nuevoRol) => {
        if (!nuevoRol || cargandoDesdeEdicion.value) return;

        const carterasRol = nuevoRol.carteras || [];

        const carterasCompletas = carterasRol.map((cartera) => {
            const encontrada = carteras.find((c) => c.id === cartera.id);
            return encontrada ? { ...encontrada } : { ...cartera };
        });

        const reportesDesdeCarteras = carterasCompletas
            .flatMap((c) => c.reportes || [])
            .filter((r, i, self) => self.findIndex((x) => x.id === r.id) === i);

        usuarioForm.customCarteras = carterasCompletas;
        usuarioForm.customReportes = reportesDesdeCarteras;
    }
);

function abrirModalAgregar() {
    Object.assign(usuarioForm, {
        name: "",
        email: "",
        password: "",
        active: true,
        roles: null,
        customCarteras: [],
        customReportes: [],
    });
    usuarioEditar.value = null;
    tabActiva.value = "basicos";
    showModal.value = true;
}
const cargandoDesdeEdicion = ref(false);
function abrirModalEditar(user) {
    cargandoDesdeEdicion.value = true;

    usuarioEditar.value = user;

    const seleccionRol = roles.find((r) =>
        user.roles && user.roles.length ? user.roles[0].id === r.id : false
    );

    const seleccionCarteras = (user.carteras || []).map((cartera) => {
        const carteraCompleta = carteras.find((c) => c.id === cartera.id);
        return carteraCompleta ? { ...carteraCompleta } : { ...cartera };
    });

    const seleccionReportes = user.reportes || [];

    Object.assign(usuarioForm, {
        name: user.name,
        email: user.email,
        password: "",
        active: !!user.active,
        roles: seleccionRol || null,
        customCarteras: seleccionCarteras,
        customReportes: seleccionReportes,
    });

    tabActiva.value = "basicos";
    showModal.value = true;
    setTimeout(() => {
        cargandoDesdeEdicion.value = false;
    }, 50);
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
        onFinish: cerrarModal,
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
            });
        }
    });
}
function toggleReportePersonalizado(reporte, checked) {
    if (checked) {
        if (!usuarioForm.customReportes.some((r) => r.id === reporte.id)) {
            usuarioForm.customReportes.push(reporte);
        }
    } else {
        usuarioForm.customReportes = usuarioForm.customReportes.filter(
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
                <h2 class="text-xl font-semibold">Gestión de Usuarios</h2>
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
                    Agregar Usuario
                </button>
            </div>
        </template>

        <div class="py-8">
            <ModalXts
                :show="showModal"
                :title="usuarioEditar ? 'Editar Usuario' : 'Agregar Usuario'"
                :submitLabel="usuarioEditar ? 'Actualizar' : 'Registrar'"
                :form="usuarioForm"
                :endpoint="
                    usuarioEditar ? `/users/${usuarioEditar.id}` : '/users'
                "
                :method="usuarioEditar ? 'put' : 'post'"
                :transform="
                    (form) => ({
                        ...form,
                        roles: form.roles ? [form.roles.id] : [],
                        carteras: form.customCarteras.map((c) => c.id),
                        reportes: form.customReportes.map((r) => r.id),
                    })
                "
                @close="cerrarModal"
                @success="handleSuccess"
            >
                <template #default="{ form, errors }">
                    <!-- Tabs -->
                    <nav class="flex mb-6 bg-indigo-50 rounded p-1">
                        <button
                            @click="tabActiva = 'basicos'"
                            type="button"
                            :class="
                                tabActiva === 'basicos'
                                    ? 'bg-white text-indigo-700 shadow px-6 py-2 rounded'
                                    : 'text-gray-500 hover:bg-white hover:text-indigo-600 px-6 py-2 rounded'
                            "
                        >
                            Datos Básicos
                        </button>
                        <button
                            @click="tabActiva = 'avanzado'"
                            type="button"
                            :class="
                                tabActiva === 'avanzado'
                                    ? 'bg-white text-indigo-700 shadow px-6 py-2 rounded'
                                    : 'text-gray-500 hover:bg-white hover:text-indigo-600 px-6 py-2 rounded'
                            "
                        >
                            Avanzado
                        </button>
                    </nav>

                    <!-- Datos Básicos -->
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
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1"
                                >Estado</label
                            >
                            <select
                                v-model="form.active"
                                class="w-full border rounded px-3 py-2"
                            >
                                <option :value="true">Activo</option>
                                <option :value="false">Inactivo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Rol</label
                            >
                            <Multiselect
                                v-model="usuarioForm.roles"
                                :options="roles"
                                track-by="id"
                                label="name"
                                placeholder="Selecciona un rol"
                                class="w-full"
                            />

                            <p v-if="errors.roles" class="text-red-600 text-sm">
                                {{ errors.roles }}
                            </p>
                        </div>
                    </div>

                    <!-- Avanzado -->
                    <div v-if="tabActiva === 'avanzado'">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1"
                                >Carteras personalizadas</label
                            >
                            <Multiselect
                                v-model="form.customCarteras"
                                :options="carteras"
                                :multiple="true"
                                track-by="id"
                                label="nombre"
                                placeholder="Selecciona carteras…"
                                class="w-full"
                            />
                        </div>
                        <button
                            type="button"
                            @click="resetearACarterasYReportesPorDefecto"
                            class="text-xs text-indigo-600 hover:underline mt-2"
                        >
                            Usar valores por defecto del rol
                        </button>

                        <!-- Reportes personalizados agrupados por cartera -->
                        <div v-if="form.customCarteras.length">
                            <label class="block text-sm font-medium mb-1">
                                Reportes personalizados
                            </label>
                            <div
                                v-for="cartera in form.customCarteras"
                                :key="cartera.id"
                                class="border border-indigo-100 rounded-lg p-3 mb-3 bg-indigo-50/50 shadow-sm"
                            >
                                <div
                                    class="font-semibold text-indigo-700 text-sm mb-2 flex items-center gap-2"
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
                                                    form.customReportes.some(
                                                        (r) =>
                                                            r.id === reporte.id
                                                    )
                                                "
                                                @change="
                                                    toggleReportePersonalizado(
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
                                                <span
                                                    v-if="
                                                        inheritedReportes.some(
                                                            (r) =>
                                                                r.id ===
                                                                reporte.id
                                                        )
                                                    "
                                                    class="text-[10px] ml-1 text-gray-400"
                                                    title="Herencia del rol"
                                                >
                                                    (por rol)
                                                </span>
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
                            <p class="mt-2 text-xs text-gray-600">
                                Total reportes asignados:
                                {{ form.customReportes.length }}
                            </p>
                        </div>
                    </div>
                </template>
            </ModalXts>

            <!-- Listado de Usuarios -->
            <div class="overflow-x-auto rounded border bg-gray-50 p-4">
                <h1 class="text-xl font-semibold mb-4">Listado de Usuarios</h1>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-indigo-100">
                        <tr>
                            <th class="px-4 py-2">ID</th>
                            <th class="px-4 py-2">Nombre</th>
                            <th class="px-4 py-2">Email</th>
                            <th class="px-4 py-2">Rol</th>
                            <th class="px-4 py-2">Estado</th>
                            <th class="px-4 py-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(user, i) in users"
                            :key="user.id"
                            :class="i % 2 === 0 ? 'bg-white' : 'bg-indigo-50'"
                            class="hover:bg-indigo-100 transition"
                        >
                            <td class="px-4 py-2">{{ user.id }}</td>
                            <td class="px-4 py-2">{{ user.name }}</td>
                            <td class="px-4 py-2">{{ user.email }}</td>
                            <td class="px-4 py-2">
                                <span
                                    v-for="r in user.roles"
                                    :key="r.id"
                                    class="inline-block bg-indigo-100 text-indigo-700 rounded px-2 py-1 text-xs mr-1"
                                >
                                    {{ r.name }}
                                </span>
                                <span
                                    v-if="!user.roles.length"
                                    class="text-gray-400"
                                    >—</span
                                >
                            </td>
                            <td class="px-4 py-2">
                                <StatusBadge :active="!!user.active" />
                            </td>
                            <td class="px-4 py-2 text-center">
                                <Actions
                                    :edit="true"
                                    :remove="true"
                                    @edit="abrirModalEditar(user)"
                                    @delete="eliminarUsuario(user)"
                                />
                            </td>
                        </tr>
                        <tr v-if="!users.length">
                            <td
                                colspan="6"
                                class="py-4 text-center text-gray-400"
                            >
                                No hay usuarios registrados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
