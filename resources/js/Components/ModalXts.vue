<template>
    <transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
        >
            <div
                class="bg-gradient-to-br from-blue-600 to-indigo-600 p-1 rounded-xl shadow-2xl w-full max-w-2xl transition-all"
            >
                <div
                    class="bg-white rounded-lg flex flex-col"
                    style="min-height: 580px; max-height: 92vh"
                >
                    <!-- Header y Tabs -->
                    <div class="px-6 pt-6">
                        <h2
                            class="text-2xl font-bold text-gray-800 flex items-center gap-2 mb-4"
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

                        <!-- Tabs dinámicos -->
                        <div
                            v-if="tabs?.length"
                            class="sticky top-0 z-10 bg-white pb-3"
                        >
                            <div
                                class="inline-flex w-full rounded-lg overflow-hidden shadow-sm border border-indigo-100 bg-indigo-50"
                            >
                                <button
                                    v-for="tab in tabs"
                                    :key="tab.value"
                                    type="button"
                                    @click="
                                        $emit('update:tabActiva', tab.value)
                                    "
                                    class="w-1/2 flex items-center justify-center gap-2 py-2 px-4 text-sm font-medium transition-all"
                                    :class="
                                        tabActiva === tab.value
                                            ? 'bg-white text-indigo-700 shadow-inner'
                                            : 'text-gray-600 hover:bg-white hover:text-indigo-700'
                                    "
                                >
                                    <component
                                        v-if="tab.icon"
                                        :is="tab.icon"
                                        class="w-5 h-5"
                                    />
                                    {{ tab.label }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario -->
                    <form
                        @submit.prevent="handleSubmit"
                        class="space-y-4 flex-1 flex flex-col overflow-hidden"
                        autocomplete="off"
                    >
                        <div
                            class="flex-1 overflow-y-auto px-6 pt-4 pb-2"
                            style="max-height: 55vh"
                        >
                            <slot :form="form" :errors="errors"></slot>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end gap-2 mt-4 px-6 pb-6">
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
                                {{ submitLabel }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    show: Boolean,
    title: String,
    submitLabel: String,
    form: Object,
    endpoint: String,
    method: String,
    transform: {
        type: Function,
        default: (form) => form,
    },
    tabActiva: String,
    tabs: {
        type: Array,
        default: null,
    },
});

const emit = defineEmits(["close", "success", "update:tabActiva"]);
const errors = ref({});

function handleSubmit() {
    errors.value = {};
    const data = props.transform(props.form);
    router[props.method](props.endpoint, data, {
        onSuccess: (page) =>
            emit(
                "success",
                page.props.success || "Operación realizada correctamente"
            ),
        onError: (err) => (errors.value = err),
    });
}
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
