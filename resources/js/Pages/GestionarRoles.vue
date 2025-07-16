<script setup>
import CarteraReportesAccordion from "@/Components/CarteraReportesAccordion.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ModalXts from "@/Components/ModalXts.vue";
import Multiselect from "vue-multiselect";
import { Head, usePage, router } from "@inertiajs/vue3";
import { ref, reactive, onMounted } from "vue";
import Swal from "sweetalert2";
import "vue-multiselect/dist/vue-multiselect.css";
import Actions from "@/Components/Actions.vue";
const { roles, carteras, reportes, permissions, success } = usePage().props;

const showModal = ref(false);
const rolEditar = ref(null);


import { computed } from "vue";

const rolForm = reactive({
    name: "",
    carteras: [],
    reportes: [],
    permissions: [],
});

// modelValue para reportes, usando computed para asegurar reactividad
const modelValue = computed({
    get: () => rolForm.reportes,
    set: (val) => {
        rolForm.reportes = val;
    },
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
    // Trabajar siempre con IDs únicos y objetos consistentes
    if (checked) {
        if (!rolForm.reportes.some((r) => r.id === reporte.id)) {
            rolForm.reportes = [...rolForm.reportes, { ...reporte }];
        }
    } else {
        rolForm.reportes = rolForm.reportes.filter((r) => r.id !== reporte.id);
    }
}
</script>

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
        <div class="py-8">
            <div class="mx-auto w-full">
                <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                    <div class="p-6">
                        <!-- Modal para agregar/editar rol -->
                        <ModalXts
                            :show="showModal"
                            :title="rolEditar ? 'Editar Rol' : 'Agregar Rol'"
                            :submitLabel="
                                rolEditar ? 'Actualizar' : 'Registrar'
                            "
                            :form="rolForm"
                            :endpoint="
                                rolEditar ? `/roles/${rolEditar.id}` : '/roles'
                            "
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
                                    permissions: form.permissions,
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
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Nombre del rol</label
                                        >
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            required
                                            class="mt-1 block w-full border-gray-300 rounded shadow-sm"
                                        />
                                        <p
                                            v-if="errors.name"
                                            class="text-red-600 text-sm"
                                        >
                                            {{ errors.name }}
                                        </p>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                        >
                                            Permisos
                                        </label>
                                        <div
                                            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2"
                                        >
                                            <label
                                                v-for="perm in permissions"
                                                :key="perm.id"
                                                class="flex items-center gap-2 bg-gray-50 rounded px-2 py-1"
                                            >
                                                <input
                                                    type="checkbox"
                                                    :value="perm.name"
                                                    v-model="form.permissions"
                                                    class="accent-indigo-600"
                                                />
                                                <span
                                                    class="text-gray-700 uppercase text-[12px] font-bold"
                                                >
                                                    {{ perm.name }}
                                                </span>
                                            </label>
                                        </div>
                                        <p
                                            v-if="errors.permissions"
                                            class="text-red-600 text-sm"
                                        >
                                            {{ errors.permissions }}
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
                                        <p
                                            v-if="errors.carteras"
                                            class="text-red-600 text-sm"
                                        >
                                            {{ errors.carteras }}
                                        </p>
                                    </div>
                                  <CarteraReportesAccordion
                                    v-if="form.carteras.length"
                                     :carteras="form.carteras"
                                     :modelValue="form.reportes"
                                    @update:modelValue="form.reportes = $event"
                                />

                                    <div


                                         v-if="form.reportes.length"
                                        class="mt-2 flex items-center justify-start"
                                    >
                                        <label
                                            class="block text-xs font-semibold text-gray-500 mb-1 mr-2"
                                            >Cantidad de reportes
                                            asignados:</label
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
                        <div>
                            <h1
                                class="text-xl font-semibold text-gray-800 mb-4 tracking-tight"
                            >
                                Listado de Roles
                            </h1>
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
                            >
                                <div
                                    v-for="role in roles"
                                    :key="role.id"
                                    class="rounded-lg border border-gray-200 bg-white/90 shadow-sm hover:shadow-md transition-all p-4 flex flex-col min-h-[110px]"
                                >
                                    <div
                                        class="flex justify-between items-center mb-2"
                                    >
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center text-base font-bold shadow"
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
                                                        d="M16 7a4 4 0 01-8 0m8 0a4 4 0 01-8 0m8 0V5a4 4 0 00-8 0v2m8 0v2a4 4 0 01-8 0V7"
                                                    ></path>
                                                </svg>
                                            </span>
                                            <span
                                                class="text-base font-medium text-gray-900 truncate max-w-[110px]"
                                                :title="role.name"
                                            >
                                                {{ role.name }}
                                            </span>
                                        </div>
                                        <Actions
                                            :edit="true"
                                            :remove="true"
                                            @edit="abrirModalEditar(role)"
                                            @delete="eliminarRol(role)"
                                        />
                                    </div>
                                    <div class="flex flex-wrap gap-1 mb-1">
                                        <template v-if="role.carteras.length">
                                            <span
                                                v-for="c in role.carteras"
                                                :key="c.id"
                                                class="flex items-center bg-gray-100 border border-gray-200 rounded-full px-2 py-0.5 text-xs text-gray-700 font-medium"
                                                :title="c.nombre"
                                            >
                                                <svg
                                                    class="w-3 h-3 mr-1 text-indigo-400"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M3 7h18"
                                                    ></path>
                                                </svg>
                                                {{ c.nombre }}
                                                <span
                                                    class="ml-1 w-4 h-4 rounded-full bg-indigo-50 text-indigo-700 text-[10px] font-bold flex items-center justify-center border border-indigo-100"
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
                                            class="text-gray-400 italic text-xs"
                                        >
                                            Sin carteras
                                        </span>
                                    </div>
                                    <div
                                        class="flex justify-between items-center border-t border-gray-100 pt-2 mt-auto"
                                    >
                                        <span class="text-[11px] text-gray-400">
                                            ID: {{ role.id }}
                                        </span>
                                        <span
                                            v-if="
                                                role.permissions &&
                                                role.permissions.length
                                            "
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100"
                                        >
                                            {{ role.permissions.length }}
                                            permiso{{
                                                role.permissions.length > 1
                                                    ? "s"
                                                    : ""
                                            }}
                                        </span>
                                    </div>
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

