<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60"
    >
        <div
            class="bg-white rounded-2xl shadow-2xl p-10 max-w-5xl w-full relative border border-indigo-100"
        >
            <button
                class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl transition"
                @click="$emit('close')"
            >
                <span aria-hidden="true">&times;</span>
            </button>

            <h2
                class="text-2xl font-extrabold mb-8 text-indigo-700 flex items-center gap-2"
            >
                Gestión de Reportes
            </h2>

            <div class="flex justify-between items-center mb-6">
                <span class="text-lg font-semibold text-gray-700"
                    >Listado de reportes</span
                >
                <button
                    class="flex items-center gap-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-5 py-2 rounded-lg shadow-md hover:shadow-lg hover:brightness-110 hover:-translate-y-0.5 transition-all duration-300 ease-out font-semibold"
                    @click="abrirModalCrear"
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
                    Nuevo Reporte
                </button>
            </div>

            <div
                ref="scrollContainer"
                @scroll="handleScroll"
                class="overflow-hidden rounded-xl border border-gray-100 bg-gray-50 custom-scroll relative"
                style="max-height: 65vh; overflow-y: auto"
            >
                <table class="min-w-full divide-y divide-gray-200">
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
                                class="px-4 py-2 text-center text-xs font-bold text-indigo-700 uppercase"
                            >
                                Link
                            </th>
                            <th
                                class="px-4 py-2 text-left text-xs font-bold text-indigo-700 uppercase"
                            >
                                Orden
                            </th>
                            <th
                                class="px-4 py-2 text-left text-xs font-bold text-indigo-700 uppercase"
                            >
                                Cartera
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
                            v-for="reporte in reportesLocal"
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
                                <input
                                    type="text"
                                    :value="reporte.link"
                                    readonly
                                    class="w-52 h-[20px] truncate text-[13px] bg-transparent border-none text-indigo-600 underline cursor-pointer hover:text-indigo-800 transition focus:outline-none py-0 leading-[20px]"
                                    @click="window.open(reporte.link, '_blank')"
                                    title="Abrir enlace"
                                />
                            </td>
                            <td class="px-4 py-2 text-gray-700 text-[13px]">
                                {{ reporte.orden }}
                            </td>
                            <td class="px-4 py-2 text-gray-700 text-[13px]">
                                <span
                                    class="inline-block px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold"
                                >
                                    {{
                                        reporte.cartera?.nombre ?? "Sin cartera"
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <div class="flex justify-center gap-2">
                                    <button
                                        @click="abrirModalEditar(reporte)"
                                        class="p-1.5 rounded-full bg-yellow-400 hover:bg-yellow-500 text-white shadow-md transition transform hover:scale-105"
                                        title="Editar reporte"
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
                                        @click="eliminarReporte(reporte)"
                                        class="p-1.5 rounded-full bg-red-600 hover:bg-red-700 text-white shadow-md transition transform hover:scale-105"
                                        title="Eliminar reporte"
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
                        <tr v-if="reportesLocal.length === 0">
                            <td
                                colspan="6"
                                class="text-center py-6 text-gray-400 text-lg"
                            >
                                No hay reportes registrados.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Fade dinámico -->
                <transition name="fade">
                    <div
                        v-if="showFade"
                        class="pointer-events-none absolute bottom-0 left-0 right-0 h-10 fade-bottom"
                    ></div>
                </transition>
            </div>
        </div>

        <!-- Modal de crear/editar reporte -->
        <ModalReporte
            v-if="showSubModal"
            :carteras="props.carteras"
            :form="form"
            :editando="editando"
            @close="cerrarSubModal"
            @guardar="guardarReporte"
        />
    </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, nextTick } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import ModalReporte from "./ModalReporte.vue";

const props = defineProps({
    show: Boolean,
    reportes: { type: Array, default: () => [] },
    carteras: { type: Array, default: () => [] },
});

const reportesLocal = ref([...props.reportes]);
const scrollContainer = ref(null);
const showFade = ref(false);

watch(
    () => props.reportes,
    (nuevos) => {
        reportesLocal.value = [...nuevos];
        nextTick(() => {
            handleScroll();
        });
    },
    { immediate: true }
);

const showSubModal = ref(false);
const editando = ref(false);

const form = reactive({
    id: null,
    nombre: "",
    link: "",
    orden: 0,
    cartera_id: null,
});

function abrirModalCrear() {
    editando.value = false;
    form.id = null;
    form.nombre = "";
    form.link = "";
    form.orden = 0;
    form.cartera_id = props.carteras.length ? props.carteras[0].id : null;
    showSubModal.value = true;
}

function abrirModalEditar(reporte) {
    editando.value = true;
    form.id = reporte.id;
    form.nombre = reporte.nombre;
    form.link = reporte.link;
    form.orden = reporte.orden;
    form.cartera_id = reporte.cartera_id;
    showSubModal.value = true;
}

function cerrarSubModal() {
    showSubModal.value = false;
}

async function guardarReporte() {
    const payload = {
        nombre: form.nombre,
        link: form.link,
        orden: form.orden,
        cartera_id: form.cartera_id,
    };

    try {
        if (editando.value) {
            const response = await axios.put(`/reportes/${form.id}`, payload);
            const updated = response.data.reporte;

            const idx = reportesLocal.value.findIndex(
                (r) => r.id === updated.id
            );
            if (idx !== -1) {
                reportesLocal.value[idx] = updated;
            }

            cerrarSubModal();

            await Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Reporte actualizado correctamente",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
        } else {
            const response = await axios.post(`/reportes`, payload);
            reportesLocal.value.push(response.data.reporte);

            cerrarSubModal();

            await Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Reporte creado correctamente",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
        }
    } catch (error) {
        console.error(error);
        await Swal.fire({
            title: "Error",
            text: "Hubo un problema al guardar el reporte.",
            icon: "error",
        });
    }
}

function eliminarReporte(reporte) {
    Swal.fire({
        title: "¿Eliminar reporte?",
        text: `¿Estás seguro de eliminar "${reporte.nombre}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            axios
                .delete(`/reportes/${reporte.id}`)
                .then(() => {
                    reportesLocal.value = reportesLocal.value.filter(
                        (r) => r.id !== reporte.id
                    );
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Reporte eliminado correctamente",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    });
                })
                .catch(() => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "error",
                        title: "No se pudo eliminar el reporte",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    });
                });
        }
    });
}

function handleScroll() {
    const el = scrollContainer.value;
    if (!el) return;

    showFade.value = el.scrollTop + el.clientHeight < el.scrollHeight - 5;
}

onMounted(() => {
    nextTick(() => {
        handleScroll();
    });
});
</script>

<style scoped>
.custom-scroll {
    position: relative;
    overflow-y: auto;
    scrollbar-width: none; /* Firefox */
}
.custom-scroll::-webkit-scrollbar {
    width: 0;
}

/* Fade dinámico */
.fade-bottom {
    background: linear-gradient(to bottom, transparent, #f9fafb);
    transition: opacity 0.3s ease;
}

/* Animación de fade */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
