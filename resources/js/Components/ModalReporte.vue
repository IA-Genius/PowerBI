<template>
    <transition name="fade">
        <div
            class="fixed inset-0 z-60 flex items-center justify-center bg-black bg-opacity-40"
        >
            <div
                class="bg-gradient-to-br from-blue-600 to-indigo-600 p-1 rounded-xl shadow-2xl w-full max-w-lg transition-all"
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
                        {{ editando ? "Editar Reporte" : "Nuevo Reporte" }}
                    </h2>

                    <form
                        @submit.prevent="$emit('guardar')"
                        class="space-y-4"
                        autocomplete="off"
                    >
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Nombre</label
                            >
                            <input
                                v-model="form.nombre"
                                type="text"
                                placeholder="Nombre del reporte"
                                class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                required
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Link</label
                            >
                            <input
                                v-model="form.link"
                                type="url"
                                placeholder="https://ejemplo.com/reporte"
                                class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                required
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Orden</label
                            >
                            <input
                                v-model.number="form.orden"
                                type="number"
                                min="0"
                                class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Cartera</label
                            >
                            <select
                                v-model="form.cartera_id"
                                class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                                required
                            >
                                <option
                                    v-for="cartera in carteras"
                                    :key="cartera.id"
                                    :value="cartera.id"
                                >
                                    {{ cartera.nombre }}
                                </option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <button
                                type="button"
                                @click="$emit('close')"
                                class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="px-5 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold shadow hover:scale-105 transition-transform"
                            >
                                {{ editando ? "Actualizar" : "Registrar" }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
defineProps({
    carteras: Array,
    form: Object,
    editando: Boolean,
});
</script>

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
