<template>
    <transition name="fade" class="modaleStyle">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
        >
            <div
                class="bg-gradient-to-br from-blue-600 to-indigo-600 p-1 rounded-xl shadow-2xl w-full max-w-3xl transition-all"
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
                        {{ title }}
                    </h2>

                    <form
                        @submit.prevent="emitirEnvio"
                        class="space-y-6"
                        autocomplete="off"
                    >
                        <!-- SLOT DE CONTENIDO -->
                        <slot :form="form" :errors="errors" />

                        <!-- BOTONES DINÁMICOS -->
                        <div class="flex justify-end gap-2 mt-6">
                            <button
                                type="button"
                                @click="$emit('close')"
                                class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition"
                            >
                                Cancelar
                            </button>

                            <!-- Botón de Importar (solo si aún no hay preview) -->
                            <button
                                v-if="!mostrarConfirmar"
                                type="submit"
                                class="px-5 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold shadow hover:scale-105 transition-transform"
                            >
                                {{ submitLabel }}
                            </button>

                            <!-- Botón de Confirmar Importación (si ya hay registros analizados) -->
                            <button
                                v-else
                                type="button"
                                @click="emit('confirmar')"
                                :disabled="importando"
                                :class="[
                                    'px-5 py-2 rounded-lg font-semibold shadow transition flex items-center justify-center gap-2 w-48',
                                    importando
                                        ? 'bg-gray-400 cursor-not-allowed text-gray-700'
                                        : 'bg-blue-600 hover:bg-blue-700 text-white',
                                ]"
                            >
                                <span
                                    v-if="importando"
                                    class="loader-big"
                                ></span>
                                <span v-else>Confirmar</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { reactive, ref, watchEffect, computed, watch } from "vue";

const props = defineProps({
    show: Boolean,
    title: String,
    submitLabel: String,
    initialForm: Object,
    previewRows: Array,
    allRows: Array,
    importando: Boolean,
});

const emit = defineEmits(["close", "submit", "confirmar"]);

const form = reactive({});
const errors = ref({});

watchEffect(() => {
    if (props.show) {
        Object.assign(form, props.initialForm || {});
        errors.value = {};
    }
});

// Mostrar botón si hay registros analizados (no solo duplicados)
const mostrarConfirmar = computed(() => props.allRows?.length > 0);

function emitirEnvio() {
    emit("submit", form, () => emit("close"));
}
</script>

<style scoped>
.loader-big {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    animation: spin 0.8s linear infinite;
    display: inline-block;
}
@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
