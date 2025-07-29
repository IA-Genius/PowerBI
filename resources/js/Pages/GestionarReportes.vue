<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ModalGestion from "@/Components/ModalGestion.vue";
import Actions from "@/Components/Actions.vue";
import InputField from "@/Components/InputField.vue";
import { Head, usePage, router } from "@inertiajs/vue3";
import { ref, onMounted, computed } from "vue";
import Swal from "sweetalert2";
import Dropdown from "@/Components/Dropdown.vue";
import SvgUploader from "@/Components/SvgUploader.vue";
import LinkCopiable from "@/Components/LinkCopiable.vue";
const carteras = ref(usePage().props.carteras);
const reportes = ref(usePage().props.reportes);
const success = usePage().props.success;
const page = usePage();
const { can } = page.props;
const canDo = (key) => !!can[key];

const showModalReporte = ref(false);
const editandoReporte = ref(false);
const reporteForm = ref({
    id: null,
    nombre: "",
    link_desktop: "",
    link_mobile: "",
    icon: "",
    orden: 0,
    cartera_id: null,
});
window.addEventListener("resize", () => {
    esMobile.value = window.innerWidth < 640;
    if (esMobile.value) {
        vistaTabla.value = false;
    }
});

const vistaTabla = ref(false);
const esMobile = ref(false);
onMounted(() => {
    esMobile.value = window.innerWidth < 640;

    if (esMobile.value) {
        vistaTabla.value = false; // fuerza vista tarjetas en mobile
    }

    if (success) {
        Swal.fire({
            toast: true,
            position: "top-end",
            icon: "success",
            title: success,
            showConfirmButton: false,
            timer: 2000,
        });
    }
});

function abrirModalCrearReporte(carteraId = null) {
    editandoReporte.value = false;
    reporteForm.value = {
        id: null,
        nombre: "",
        link_desktop: "",
        link_mobile: "",
        icon: "",
        orden: 0,
        cartera_id: carteraId || null,
    };
    showModalReporte.value = true;
}

function abrirModalEditarReporte(reporte) {
    editandoReporte.value = true;
    reporteForm.value = { ...reporte };
    showModalReporte.value = true;
}

function cerrarModalReporte() {
    showModalReporte.value = false;
}

function handleSuccess(message) {
    Swal.fire({
        toast: true,
        position: "top-end",
        icon: "success",
        title: message,
        showConfirmButton: false,
        timer: 2000,
    });
    router.visit(route("reportes.index"), {
        preserveScroll: true,
        only: ["reportes", "success"],
    });
}

function eliminarReporte(reporte) {
    Swal.fire({
        title: "¿Eliminar reporte?",
        text: `¿Estás seguro de eliminar «${reporte.nombre}»?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route("reportes.destroy", reporte.id), {
                preserveScroll: true,
                only: ["reportes", "success"],
                onSuccess: () => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: `Reporte «${reporte.nombre}» eliminado`,
                        showConfirmButton: false,
                        timer: 2000,
                    });
                },
                onError: () => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "error",
                        title: `Error al eliminar el reporte «${reporte.nombre}»`,
                        showConfirmButton: false,
                        timer: 2000,
                    });
                },
            });
        }
    });
}

const carteraSeleccionada = ref(null);

const filtroNombreReporte = ref("");

const reportesFiltrados = computed(() => {
    let lista = reportes.value;

    if (carteraSeleccionada.value) {
        lista = lista.filter((r) => r.cartera_id === carteraSeleccionada.value);
    }

    if (filtroNombreReporte.value.trim() !== "") {
        const texto = filtroNombreReporte.value.toLowerCase();
        lista = lista.filter((r) => r.nombre.toLowerCase().includes(texto));
    }

    return lista;
});
</script>

<template>
    <Head title="Reportes" />
    <AuthenticatedLayout class="relleno">
        <template #header>
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
            >
                <div class="flex items-center">
                    <h2 class="text-xl font-semibold tituloPag">
                        Gestionar Reportes
                    </h2>
                    <span
                        class="ml-4 px-3 py-1 hidden sm:inline text-[11px] font-bold uppercase rounded-full shadow-sm text-white bgPrincipal"
                    >
                        {{ reportesFiltrados.length }} reportes
                    </span>
                </div>
                <div
                    class="flex flex-col sm:flex-row items-stretch gap-3 sm:gap-4 mt-4 sm:mt-0"
                >
                    <div class="relative sm:max-w-xs">
                        <input
                            v-model="filtroNombreReporte"
                            type="text"
                            placeholder="Buscar..."
                            class="w-full pl-10 pr-4 py-2 rounded-md border border-gray-200 bg-white shadow-sm text-sm placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:outline-none focus:border-indigo-300 transition"
                        />

                        <svg
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21 21l-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z"
                            />
                        </svg>
                    </div>

                    <Dropdown>
                        <template #trigger>
                            <button
                                class="w-full sm:max-w-[220px] flex items-center justify-between gap-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 px-4 py-2 rounded-md shadow-sm hover:bg-gray-100 transition"
                            >
                                {{
                                    carteraSeleccionada
                                        ? carteras.find(
                                              (c) =>
                                                  c.id === carteraSeleccionada
                                          )?.nombre
                                        : "Todas las carteras"
                                }}
                                <svg
                                    class="w-4 h-4 text-gray-500"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>
                            </button>
                        </template>
                        <template #content>
                            <ul class="text-sm text-gray-800">
                                <li>
                                    <a
                                        href="#"
                                        class="block px-4 py-2 hover:bg-gray-100"
                                        @click.prevent="
                                            carteraSeleccionada = null
                                        "
                                    >
                                        Todas las carteras
                                    </a>
                                </li>
                                <li
                                    v-for="cartera in carteras"
                                    :key="cartera.id"
                                >
                                    <a
                                        href="#"
                                        class="block px-4 py-2 hover:bg-gray-100"
                                        @click.prevent="
                                            carteraSeleccionada = cartera.id
                                        "
                                    >
                                        {{ cartera.nombre }}
                                    </a>
                                </li>
                            </ul>
                        </template>
                    </Dropdown>

                    <button
                        @click="vistaTabla = !vistaTabla"
                        class="hidden sm:flex items-center gap-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 px-3 py-2 rounded-lg shadow hover:bg-gray-50 hover:text-indigo-600 transition"
                    >
                        <svg
                            v-if="vistaTabla"
                            class="w-5 h-5 text-indigo-500"
                            fill="currentColor"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 512 512"
                        >
                            <!-- Icono de tarjetas (cuatro bloques) -->
                            <path
                                d="M272 64l0 176 208 0 0-144c0-17.7-14.3-32-32-32L272 64zm-32 0L64 64C46.3 64 32 78.3 32 96l0 144 208 0 0-176zM32 272l0 144c0 17.7 14.3 32 32 32l176 0 0-176L32 272zM272 448l176 0c17.7 0 32-14.3 32-32l0-144-208 0 0 176zM0 96C0 60.7 28.7 32 64 32l384 0c35.3 0 64 28.7 64 64l0 320c0 35.3-28.7 64-64 64L64 480c-35.3 0-64-28.7-64-64L0 96z"
                            />
                        </svg>

                        <svg
                            v-else
                            class="w-5 h-5 text-indigo-500"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <!-- Icono de tabla -->
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 6h18M3 12h18M3 18h18"
                            />
                        </svg>
                        <span>{{
                            vistaTabla ? "Vista Tarjetas" : "Vista Tabla"
                        }}</span>
                    </button>

                    <button
                        v-if="canDo('reportes.crear')"
                        @click="abrirModalCrearReporte()"
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
                        <span class="hidden sm:inline">Agregar</span>
                    </button>
                </div>
            </div>
        </template>
        <div v-if="!vistaTabla" class="py-6">
            <TransitionGroup
                name="fade-slide"
                tag="div"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 min-h-[150px] grid-auto-rows-[minmax(110px,_auto)]"
            >
                <div
                    v-for="reporte in reportesFiltrados"
                    :key="reporte.id"
                    class="group transition-all duration-300 rounded-xl border border-gray-200 bg-white/95 shadow hover:shadow-lg hover:border-indigo-300 p-5 flex flex-col min-h-[140px] relative overflow-hidden"
                >
                    <!-- Icono y acciones -->
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-3">
                            <span
                                class="w-11 h-11 rounded-full bg-gradient-to-tr from-indigo-500 to-indigo-400 flex items-center justify-center text-xl font-bold shadow-lg border-2 border-white group-hover:scale-105 transition-transform"
                            >
                                <div
                                    class="w-7 h-7 [&_svg]:w-full [&_svg]:h-full [&_svg]:fill-white [&_svg]:stroke-white"
                                    v-html="reporte.icon"
                                ></div>
                            </span>
                            <span
                                class="text-lg font-semibold text-gray-900 truncate max-w-[180px] sm:max-w-[260px] group-hover:text-indigo-700 transition-colors"
                                :title="reporte.nombre"
                            >
                                {{ reporte.nombre }}
                            </span>
                        </div>
                        <Actions
                            :edit="true"
                            :remove="true"
                            @edit="abrirModalEditarReporte(reporte)"
                            @delete="eliminarReporte(reporte)"
                            class="opacity-80 group-hover:opacity-100 transition"
                        />
                    </div>

                    <!-- Cartera -->
                    <div class="flex flex-wrap gap-2 mb-2">
                        <template v-if="reporte.cartera_id">
                            <span
                                class="flex items-center bg-indigo-50 border border-indigo-100 rounded-full px-3 py-1 text-xs text-indigo-700 font-medium shadow-sm"
                                :title="
                                    carteras.find(
                                        (c) => c.id === reporte.cartera_id
                                    )?.nombre
                                "
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
                                    />
                                </svg>
                                {{
                                    carteras.find(
                                        (c) => c.id === reporte.cartera_id
                                    )?.nombre ?? "Sin cartera"
                                }}
                            </span>
                        </template>
                        <span v-else class="text-gray-400 italic text-xs"
                            >Sin cartera</span
                        >
                    </div>

                    <!-- Enlaces -->
                    <div class="flex gap-3 mb-3">
                        <LinkCopiable
                            :link="reporte.link_desktop"
                            title="Enlace Desktop"
                            class="flex-1"
                        >
                            <template #icon>
                                <svg
                                    class="w-4 h-4 text-indigo-500"
                                    fill="currentColor"
                                    viewBox="0 0 512 512"
                                >
                                    <path
                                        d="M160 64l0 64 320 0 0-32c0-17.7-14.3-32-32-32L160 64zm-32 0L64 64C46.3 64 32 78.3 32 96l0 32 96 0 0-64zM32 160l0 256c0 17.7 14.3 32 32 32l384 0c17.7 0 32-14.3 32-32l0-256-336 0L32 160zM0 96C0 60.7 28.7 32 64 32l384 0c35.3 0 64 28.7 64 64l0 320c0 35.3-28.7 64-64 64L64 480c-35.3 0-64-28.7-64-64L0 96z"
                                    />
                                </svg>
                            </template>
                        </LinkCopiable>
                        <LinkCopiable
                            :link="reporte.link_mobile"
                            title="Enlace Mobile"
                            class="flex-1"
                        >
                            <template #icon>
                                <svg
                                    class="w-4 h-4 text-indigo-500"
                                    fill="currentColor"
                                    viewBox="0 0 448 512"
                                >
                                    <path
                                        d="M64 32C46.3 32 32 46.3 32 64l0 384c0 17.7 14.3 32 32 32l320 0c17.7 0 32-14.3 32-32l0-384c0-17.7-14.3-32-32-32L64 32zM0 64C0 28.7 28.7 0 64 0L384 0c35.3 0 64 28.7 64 64l0 384c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 64zM192 400l64 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-64 0c-8.8 0-16-7.2-16-16s7.2-16 16-16z"
                                    />
                                </svg>
                            </template>
                        </LinkCopiable>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex justify-between items-center border-t border-gray-100 pt-2 mt-auto text-[11px] text-gray-400"
                    >
                        <span>
                            <span class="font-semibold text-indigo-400"
                                >ID:</span
                            >
                            {{ reporte.id }}
                        </span>
                        <span v-if="reporte.orden !== undefined" class="ml-2">
                            <span class="font-semibold text-gray-400"
                                >Orden:</span
                            >
                            {{ reporte.orden }}
                        </span>
                    </div>
                </div>
                <div
                    v-if="reportesFiltrados.length === 0"
                    key="no-data"
                    class="col-span-full text-center py-8 text-gray-400 text-lg transition-opacity duration-300"
                >
                    No hay reportes registrados.
                </div>
            </TransitionGroup>
        </div>

        <div
            v-else
            class="overflow-x-auto rounded-xl border border-gray-100 bg-gray-50 mt-4"
        >
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="text-white text-xs bgPrincipal">
                    <tr>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Nombre</th>
                        <th class="px-4 py-2 text-left">Desktop</th>
                        <th class="px-4 py-2 text-left">Mobile</th>

                        <th class="px-4 py-2 text-left">Orden</th>
                        <th class="px-4 py-2 text-left">Cartera</th>
                        <th class="px-4 py-2 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="reporte in reportesFiltrados"
                        :key="reporte.id"
                        class="bg-white even:bg-indigo-50 hover:bg-indigo-100 transition"
                    >
                        <td class="px-4 py-2 text-gray-700 text-[13px]">
                            {{ reporte.id }}
                        </td>
                        <td class="px-4 py-2 text-gray-700 text-[13px]">
                            {{ reporte.nombre }}
                        </td>
                        <td class="px-4 py-2 text-gray-700 text-[13px]">
                            <a
                                v-if="reporte.link_desktop"
                                :href="reporte.link_desktop"
                                target="_blank"
                                class="text-indigo-600 underline"
                                >Desktop</a
                            >
                            <span v-else class="text-gray-400 italic"
                                >No disponible</span
                            >
                        </td>
                        <td class="px-4 py-2 text-gray-700 text-[13px]">
                            <a
                                v-if="reporte.link_mobile"
                                :href="reporte.link_mobile"
                                target="_blank"
                                class="text-indigo-600 underline"
                                >Mobile</a
                            >
                            <span v-else class="text-gray-400 italic"
                                >No disponible</span
                            >
                        </td>

                        <td class="px-4 py-2 text-gray-700 text-[13px]">
                            {{ reporte.orden }}
                        </td>
                        <td class="px-4 py-2 text-gray-700 text-[13px]">
                            <span
                                class="inline-block px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold"
                            >
                                {{
                                    carteras.find(
                                        (c) => c.id === reporte.cartera_id
                                    )?.nombre ?? "Sin cartera"
                                }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <Actions
                                :edit="canDo('reportes.editar')"
                                :remove="canDo('reportes.eliminar')"
                                @edit="abrirModalEditarReporte(reporte)"
                                @delete="eliminarReporte(reporte)"
                            />
                        </td>
                    </tr>
                    <tr v-if="reportesFiltrados.length === 0">
                        <td
                            colspan="8"
                            class="text-center py-6 text-gray-400 text-lg"
                        >
                            No hay reportes registrados.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ModalGestion
            :show="showModalReporte"
            :title="editandoReporte ? 'Editar Reporte' : 'Agregar Reporte'"
            submitLabel="Guardar"
            :initialForm="reporteForm"
            :endpoint="
                editandoReporte
                    ? route('reportes.update', reporteForm.id)
                    : route('reportes.store')
            "
            :method="editandoReporte ? 'put' : 'post'"
            @close="cerrarModalReporte"
            @success="handleSuccess"
        >
            <template #default="{ form, errors }">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <InputField
                        class="modalInputs"
                        label="Nombre"
                        v-model="form.nombre"
                        name="reporte_nombre"
                        placeholder="Nombre del reporte"
                        :error="errors.nombre"
                        :required="true"
                    />
                    <InputField
                        class="modalInputs"
                        label="Orden"
                        v-model="form.orden"
                        name="reporte_orden"
                        type="number"
                        :min="0"
                        :error="errors.orden"
                    />

                    <InputField
                        class="modalInputs"
                        label="Link Desktop"
                        v-model="form.link_desktop"
                        name="reporte_link_desktop"
                        placeholder="https://ejemplo.com/desktop"
                        :error="errors.link_desktop"
                        :required="true"
                    />
                    <InputField
                        class="modalInputs"
                        label="Link Mobile"
                        v-model="form.link_mobile"
                        name="reporte_link_mobile"
                        placeholder="https://ejemplo.com/mobile"
                        :error="errors.link_mobile"
                    />

                    <div class="sm:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Ícono SVG
                        </label>
                        <SvgUploader v-model="form.icon" :error="errors.icon" />
                    </div>

                    <div class="modalInputs sm:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            for="cartera_id"
                        >
                            Cartera
                        </label>
                        <select
                            id="cartera_id"
                            v-model="form.cartera_id"
                            class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            required
                        >
                            <option
                                v-for="c in carteras"
                                :key="c.id"
                                :value="c.id"
                            >
                                {{ c.nombre }}
                            </option>
                        </select>
                        <p
                            v-if="errors.cartera_id"
                            class="text-red-600 text-sm"
                        >
                            {{ errors.cartera_id }}
                        </p>
                    </div>
                </div>
            </template>
        </ModalGestion>
    </AuthenticatedLayout>
</template>
<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: scale(0.97);
}
</style>
