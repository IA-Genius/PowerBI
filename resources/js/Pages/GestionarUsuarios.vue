<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ModalGestion from "@/Components/ModalGestion.vue";
import { Head, usePage, router } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";

const users = usePage().props.users;
const success = usePage().props.success;
const showModal = ref(false);
const usuarioEditar = ref(null);
const usuarioForm = ref({
    name: "",
    email: "",
    password: "",
    active: true,
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
    usuarioEditar.value = null;
    usuarioForm.value = {
        name: "",
        email: "",
        password: "",
        active: true,
    };
    showModal.value = true;
}
function abrirModalEditar(user) {
    usuarioEditar.value = user;
    usuarioForm.value = {
        name: user.name,
        email: user.email,
        password: "",
        active: !!user.active,
    };
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
        onFinish: () => {
            cerrarModal();
        },
    });
}

// Método para eliminar usuario with confirmación y toast
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
</script>

<template>
    <Head title="Gestión de Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h2
                        class="text-xl font-semibold leading-tight text-gray-800"
                    >
                        Gestión de Usuarios
                    </h2>
                    <span
                        class="text-white text-xs px-3 py-1 rounded-full ml-2"
                        style="background-color: #5f61ff"
                    >
                        {{ users.length }} usuarios
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
                    Agregar Usuario
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl">
                <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                    <div class="p-6">
                        <!-- Modal genérico para agregar/editar usuario -->
                        <ModalGestion
                            :show="showModal"
                            :title="
                                usuarioEditar
                                    ? 'Editar Usuario'
                                    : 'Agregar Usuario'
                            "
                            :submitLabel="
                                usuarioEditar ? 'Actualizar' : 'Registrar'
                            "
                            :initialForm="usuarioForm"
                            :endpoint="
                                usuarioEditar && usuarioEditar.id
                                    ? `/users/${usuarioEditar.id}`
                                    : '/users'
                            "
                            :method="
                                usuarioEditar && usuarioEditar.id
                                    ? 'put'
                                    : 'post'
                            "
                            @close="cerrarModal"
                            @success="handleSuccess"
                        >
                            <template #default="{ form, errors }">
                                <input
                                    type="text"
                                    name="fakeusernameremembered"
                                    style="display: none"
                                    autocomplete="off"
                                />
                                <input
                                    type="password"
                                    name="fakepasswordremembered"
                                    style="display: none"
                                    autocomplete="off"
                                />
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Nombre</label
                                    >
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        name="user_name"
                                        placeholder="Nombre"
                                        autocomplete="off"
                                        class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                    />
                                    <div
                                        v-if="errors.name"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ errors.name }}
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Email</label
                                    >
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        name="user_email"
                                        placeholder="Email"
                                        autocomplete="off"
                                        class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                    />
                                    <div
                                        v-if="errors.email"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ errors.email }}
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        {{
                                            usuarioEditar
                                                ? "Nueva contraseña (opcional)"
                                                : "Contraseña"
                                        }}
                                    </label>

                                    <!-- Inputs falsos para evitar autocompletado -->
                                    <input
                                        type="text"
                                        name="fakeusernameremembered"
                                        style="display: none"
                                        autocomplete="off"
                                    />
                                    <input
                                        type="password"
                                        name="fakepasswordremembered"
                                        style="display: none"
                                        autocomplete="off"
                                    />

                                    <input
                                        v-model="form.password"
                                        type="password"
                                        :name="
                                            usuarioEditar
                                                ? 'new_user_password'
                                                : 'user_password'
                                        "
                                        :placeholder="
                                            usuarioEditar
                                                ? 'Dejar vacío para mantener la contraseña actual'
                                                : 'Contraseña'
                                        "
                                        autocomplete="new-password"
                                        readonly
                                        @focus="
                                            (e) =>
                                                e.target.removeAttribute(
                                                    'readonly'
                                                )
                                        "
                                        class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                    />

                                    <div
                                        v-if="errors.password"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ errors.password }}
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
                                        v-model="form.active"
                                        class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                    >
                                        <option :value="true">Activo</option>
                                        <option :value="false">Inactivo</option>
                                    </select>
                                </div>
                            </template>
                        </ModalGestion>

                        <div class="flex items-center justify-between mb-4">
                            <h1 class="text-xl font-semibold text-gray-800">
                                Listado de Usuarios
                            </h1>
                            <button
                                class="flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-indigo-700 text-white px-4 py-1.5 rounded-md shadow-md hover:shadow-lg hover:brightness-110 hover:-translate-y-0.5 transition-all duration-300 ease-out"
                                title="Ver listado de reportes"
                            >
                                <span class="font-bold text-xs tracking-wide">
                                    VER ASIGNACIONES
                                </span>
                                <span
                                    class="ml-2 bg-white text-indigo-700 font-bold px-2 py-0.5 rounded-full text-xs shadow-sm"
                                >
                                    1
                                </span>
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
                                            <span
                                                :class="
                                                    user.active
                                                        ? 'bg-green-100 text-green-700'
                                                        : 'bg-red-100 text-red-700'
                                                "
                                                class="inline-block px-3 py-1 rounded-full text-xs font-semibold"
                                            >
                                                {{
                                                    user.active
                                                        ? "Activo"
                                                        : "Inactivo"
                                                }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <div
                                                class="flex justify-center gap-2"
                                            >
                                                <div
                                                    class="flex justify-center gap-2"
                                                >
                                                    <button
                                                        @click="
                                                            abrirModalEditar(
                                                                user
                                                            )
                                                        "
                                                        class="p-1.5 rounded-full bg-yellow-400 hover:bg-yellow-500 text-white shadow-md transition transform hover:scale-105"
                                                        title="Editar usuario"
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
                                                            eliminarUsuario(
                                                                user
                                                            )
                                                        "
                                                        class="p-1.5 rounded-full bg-red-600 hover:bg-red-700 text-white shadow-md transition transform hover:scale-105"
                                                        title="Eliminar usuario"
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
                                            </div>
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
    </AuthenticatedLayout>
</template>
