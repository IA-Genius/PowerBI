<script setup>
import CarteraReportesAccordion from "@/Components/CarteraReportesAccordion.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ModalXts from "@/Components/ModalXts.vue";
import Multiselect from "vue-multiselect";
import { Head, usePage, router } from "@inertiajs/vue3";
import { ref, reactive, onMounted, watch } from "vue";
import Swal from "sweetalert2";
import "vue-multiselect/dist/vue-multiselect.css";
import Actions from "@/Components/Actions.vue";
import InputField from "@/Components/InputField.vue";
import ModuloPermisosAccordion from "@/Components/ModuloPermisosAccordion.vue";

//Permisos
const page = usePage();
const { can } = page.props;
const canDo = (key) => !!can[key];

const { roles, carteras, reportes, permissions, success } = usePage().props;

const showModal = ref(false);
const rolEditar = ref(null);

const rolForm = reactive({
    name: "",
    carteras: [],
    reportes: [],
    permissions: [],
});

const carterasConReportes = carteras.map((c) => ({
    ...c,
    reportes: reportes.filter((r) => r.cartera_id === c.id),
}));

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

    // Habilitar scroll horizontal con la rueda del mouse
    document.querySelectorAll(".scrollbar-ghost").forEach((el) => {
        el.addEventListener("wheel", function (e) {
            if (e.deltaY !== 0) {
                e.preventDefault();
                el.scrollBy({
                    left: e.deltaY,
                    behavior: "smooth",
                });
            }
        });
    });
});

function abrirModalAgregar() {
    Object.assign(rolForm, {
        name: "",
        carteras: [],
        reportes: [],
        permissions: [],
    });
    rolEditar.value = null;
    showModal.value = true;
}

function abrirModalEditar(role) {
    rolEditar.value = role;

    const carterasSeleccionadas = carterasConReportes.filter((c) =>
        role.carteras.some((uc) => uc.id === c.id)
    );

    const reportesSeleccionados = [];
    carterasSeleccionadas.forEach((c) => {
        c.reportes.forEach((r) => {
            if (role.reportes.some((ur) => ur.id === r.id)) {
                reportesSeleccionados.push(r);
            }
        });
    });

    Object.assign(rolForm, {
        name: role.name,
        carteras: carterasSeleccionadas,
        reportes: reportesSeleccionados,
        permissions: role.permissions.map((p) => p.name), // ← Aquí
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
    cerrarModal();
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
            router.delete(route("roles.destroy", role.id), {
                onSuccess: () => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: `Rol «${role.name}» eliminado`,
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

watch(
    () => rolForm.carteras,
    (nuevasCarteras) => {
        const nuevasCarterasIds = nuevasCarteras.map((c) => c.id);
        rolForm.reportes = rolForm.reportes.filter((r) =>
            nuevasCarterasIds.includes(r.cartera_id)
        );
    },
    { deep: true }
);

const tabActiva = ref("basico");

import { UserGroupIcon, CogIcon } from "@heroicons/vue/24/outline";

const tabs = [
    { value: "basico", label: "Datos Básicos", icon: UserGroupIcon },
    { value: "avanzado", label: "Permisos Avanzados", icon: CogIcon },
];

function transformarForm(form) {
    return {
        ...form,
        carteras: form.carteras.map((c) => c.id),
        reportes: form.reportes.map((r) => r.id),
    };
}
</script>

<template>
    <Head title="Roles" />
    <AuthenticatedLayout class="relleno">
        <div class="flex items-center justify-between">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold tituloPag">Gestionar Roles</h2>
                <span
                    class="ml-4 px-3 py-1 hidden sm:inline text-[11px] font-bold uppercase rounded-full shadow-sm text-white bgPrincipal"
                >
                    {{ roles.length }} roles
                </span>
            </div>
            <button
                v-if="canDo('roles.crear')"
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
        <div class="py-6">
            <div class="mx-auto w-full">
                <!-- Modal para agregar/editar rol -->
                <ModalXts
                    :show="showModal"
                    :title="rolEditar ? 'Editar Rol' : 'Agregar Rol'"
                    :submitLabel="rolEditar ? 'Actualizar' : 'Registrar'"
                    :form="rolForm"
                    :endpoint="
                        rolEditar
                            ? route('roles.update', rolEditar.id)
                            : route('roles.store')
                    "
                    :method="rolEditar ? 'put' : 'post'"
                    :transform="transformarForm"
                    :carteras="carterasConReportes"
                    :reportes="reportes"
                    @close="cerrarModal"
                    @success="handleSuccess"
                    v-model:tabActiva="tabActiva"
                    :tabs="tabs"
                >
                    <template #default="{ form, errors }">
                        <div v-if="tabActiva === 'basico'" class="space-y-4">
                            <InputField
                                class="modalInputs"
                                label="Nombre del rol"
                                v-model="form.name"
                                placeholder="role name"
                                :error="errors.name"
                            />

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
                                    :searchable="false"
                                    placeholder="Selecciona carteras"
                                    class="w-full"
                                />
                                <p
                                    v-if="errors.carteras"
                                    class="text-red-600 text-sm"
                                >
                                    {{ errors.carteras }}
                                </p>
                            </div>

                            <div>
                                <CarteraReportesAccordion
                                    v-if="form.carteras.length"
                                    :carteras="form.carteras"
                                    :modelValue="form.reportes"
                                    @update:modelValue="form.reportes = $event"
                                />
                                <p class="mt-2 text-xs text-gray-600">
                                    Total reportes asignados:
                                    {{ form.reportes.length }}
                                </p>
                            </div>
                        </div>

                        <div v-if="tabActiva === 'avanzado'" class="space-y-4">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Permisos</label
                            >
                            <ModuloPermisosAccordion
                                v-model="form.permissions"
                                :permissions="permissions"
                            />
                            <p
                                v-if="errors.permissions"
                                class="text-red-600 text-sm"
                            >
                                {{ errors.permissions }}
                            </p>
                        </div>
                    </template>
                </ModalXts>

                <!-- Listado de roles -->
                <div>
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
                    >
                        <div
                            v-for="role in roles"
                            :key="role.id"
                            class="bg-white rounded-xl border cursor-pointer transition-all duration-200 shadow-sm w-full max-w-full border-gray-200 hover:border-gray-300 hover:shadow-md flex flex-col overflow-hidden"
                        >
                            <!-- Header simplificado -->
                            <div
                                class="p-4 border-b bg-white border-gray-100 flex items-center gap-3 min-h-0"
                            >
                                <!-- Avatar con icono -->
                                <div
                                    class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center shadow-md flex-shrink-0"
                                >
                                    <svg
                                        class="w-5 h-5 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                    </svg>
                                </div>
                                <!-- Nombre del rol -->
                                <div class="min-w-0 flex-1">
                                    <h3
                                        class="text-sm font-semibold text-gray-900 truncate"
                                        :title="role.name"
                                    >
                                        {{ role.name }}
                                    </h3>
                                    <p class="text-xs text-gray-500 truncate">
                                        ID: {{ role.id }}
                                    </p>
                                </div>
                            </div>

                            <!-- Cuerpo de la tarjeta -->
                            <div
                                class="p-4 flex-1 flex flex-col space-y-3 min-h-0"
                            >
                                <!-- Carteras -->
                                <div>
                                    <h4
                                        class="text-xs font-medium text-gray-600 mb-2 truncate"
                                    >
                                        Carteras asignadas
                                    </h4>
                                    <div class="flex flex-wrap gap-1.5">
                                        <template v-if="role.carteras.length">
                                            <span
                                                v-for="c in role.carteras"
                                                :key="c.id"
                                                class="inline-flex items-center bg-blue-50 text-blue-700 px-2 py-1 rounded-md text-xs font-medium border border-blue-100 truncate max-w-full"
                                                :title="c.nombre"
                                            >
                                                <span class="truncate">{{
                                                    c.nombre
                                                }}</span>
                                                <span
                                                    class="ml-1 w-3.5 h-3.5 rounded-full bg-blue-600 text-white text-[9px] font-bold flex items-center justify-center flex-shrink-0"
                                                >
                                                    {{
                                                        role.reportes.filter(
                                                            (r) =>
                                                                r.cartera_id ===
                                                                c.id
                                                        ).length
                                                    }}
                                                </span>
                                            </span>
                                        </template>
                                        <span
                                            v-else
                                            class="text-gray-400 italic text-xs truncate"
                                        >
                                            Sin carteras asignadas
                                        </span>
                                    </div>
                                </div>
                                <!-- Reportes totales -->
                                <div>
                                    <h4
                                        class="text-xs font-medium text-gray-600 mb-1 truncate"
                                    >
                                        Reportes totales
                                    </h4>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="inline-flex items-center bg-green-50 text-green-700 px-2 py-1 rounded-md text-xs font-medium border border-green-100"
                                        >
                                            {{
                                                role.reportes
                                                    ? role.reportes.length
                                                    : 0
                                            }}
                                            reportes
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div
                                class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between min-h-0"
                            >
                                <div
                                    class="text-xs text-gray-500 min-w-0 flex-1 mr-2"
                                >
                                    <div class="flex items-center gap-2">
                                        <svg
                                            class="w-3 h-3 text-gray-400 flex-shrink-0"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                            />
                                        </svg>
                                        <span class="truncate">
                                            {{
                                                role.permissions &&
                                                role.permissions.length
                                                    ? role.permissions.length
                                                    : 0
                                            }}
                                            permisos
                                        </span>
                                    </div>
                                </div>

                                <div class="flex-shrink-0">
                                    <Actions
                                        :edit="canDo('roles.editar')"
                                        :remove="canDo('roles.eliminar')"
                                        @edit="abrirModalEditar(role)"
                                        @delete="eliminarRol(role)"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style scoped>
/* width */
.scrollbar-ghost::-webkit-scrollbar {
    width: 4px !important;
    height: 3px !important;
}

/* Track */
.scrollbar-ghost::-webkit-scrollbar-track {
    border-radius: 10px;
    background: transparent;
}
.scrollbar-ghost:hover::-webkit-scrollbar-track {
    box-shadow: inset 0 0 5px #7a7a7a;
}

/* Handle */
.scrollbar-ghost::-webkit-scrollbar-thumb {
    border-radius: 10px;
    background: transparent;
    transition: background 0.4s;
    animation: thumbPulse 2s ease-in-out infinite alternate;
}

/* Hover más intenso */
.scrollbar-ghost:hover::-webkit-scrollbar-thumb {
    background: rgba(99, 102, 241, 0.8);
}

/* Definimos la animación */
@keyframes thumbPulse {
    from {
        background: rgba(255, 255, 255, 0.1);
    }
    to {
        background: rgba(99, 102, 241, 0.3);
    }
}
</style>
