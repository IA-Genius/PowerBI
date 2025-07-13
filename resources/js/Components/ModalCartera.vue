<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import Swal from "sweetalert2";

const props = defineProps({
    show: Boolean,
    cartera: Object,
});
const emit = defineEmits(["close", "success"]);

const nombre = ref("");
const descripcion = ref("");
const orden = ref(0);
const estado = ref(true);
const error = ref({});

watch(
    () => props.cartera,
    (newCartera) => {
        if (newCartera) {
            nombre.value = newCartera.nombre;
            descripcion.value = newCartera.descripcion;
            orden.value = newCartera.orden;
            estado.value = !!newCartera.estado;
        } else {
            nombre.value = "";
            descripcion.value = "";
            orden.value = 0;
            estado.value = true;
        }
        error.value = {};
    }
);

function cerrar() {
    emit("close");
    error.value = {};
}

function guardarCartera() {
    if (props.cartera) {
        router.put(
            `/carteras/${props.cartera.id}`,
            {
                nombre: nombre.value,
                descripcion: descripcion.value,
                orden: orden.value,
                estado: estado.value,
            },
            {
                onError: (errors) => {
                    error.value = errors;
                },
                onSuccess: () => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Cartera actualizada correctamente",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    });
                    emit("success");
                },
            }
        );
    } else {
        router.post(
            "/carteras",
            {
                nombre: nombre.value,
                descripcion: descripcion.value,
                orden: orden.value,
                estado: estado.value,
            },
            {
                onError: (errors) => {
                    error.value = errors;
                },
                onSuccess: () => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Cartera creada correctamente",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    });
                    emit("success");
                },
            }
        );
    }
}
</script>

<template>
    <transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
        >
            <div
                class="bg-gradient-to-br from-blue-600 to-indigo-600 p-1 rounded-xl shadow-2xl w-full max-w-md transition-all"
            >
                <div class="bg-white rounded-lg p-6">
                    <h2
                        class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2"
                    >
                        <svg
                            class="w-7 h-7 text-indigo-600"
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
                        {{
                            props.cartera ? "Editar Cartera" : "Agregar Cartera"
                        }}
                    </h2>
                    <form
                        @submit.prevent="guardarCartera"
                        class="space-y-4"
                        autocomplete="off"
                    >
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Nombre</label
                            >
                            <input
                                v-model="nombre"
                                type="text"
                                name="cartera_nombre"
                                placeholder="Nombre"
                                autocomplete="off"
                                class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            />
                            <div
                                v-if="error.nombre"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ error.nombre }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Descripción</label
                            >
                            <textarea
                                v-model="descripcion"
                                name="cartera_descripcion"
                                placeholder="Descripción"
                                autocomplete="off"
                                class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none resize-none"
                            ></textarea>
                            <div
                                v-if="error.descripcion"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ error.descripcion }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Orden</label
                            >
                            <input
                                v-model="orden"
                                type="number"
                                name="cartera_orden"
                                min="0"
                                autocomplete="off"
                                class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            />
                            <div
                                v-if="error.orden"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ error.orden }}
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
                                v-model="estado"
                                class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            >
                                <option :value="true">Activa</option>
                                <option :value="false">Inactiva</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <button
                                type="button"
                                @click="cerrar"
                                class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="px-5 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold shadow hover:scale-105 transition-transform"
                            >
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
