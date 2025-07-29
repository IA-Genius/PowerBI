<template>
    <transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
        >
            <div class="rounded-2xl shadow-2xl w-full max-w-2xl transition-all">
                <div
                    class="bg-white rounded-2xl flex flex-col shadow-lg border border-gray-100"
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

                        <!-- Tabs dinámicos mejorados -->
                        <div
                            v-if="tabs?.length"
                            class="sticky top-0 z-10 bg-white pt-1 pb-4"
                        >
                            <div class="flex border-b border-gray-200">
                                <template v-for="tab in tabs" :key="tab.value">
                                    <button
                                        type="button"
                                        @click="
                                            $emit('update:tabActiva', tab.value)
                                        "
                                        class="flex-1 text-center py-3 px-1 text-sm font-medium border-b-2 transition-all flex justify-center items-center gap-2"
                                        :class="
                                            tabActiva === tab.value
                                                ? 'border-indigo-600 text-indigo-700 font-semibold'
                                                : 'border-transparent text-gray-500 hover:text-indigo-600 hover:border-gray-300'
                                        "
                                    >
                                        <component
                                            v-if="tab.icon"
                                            :is="tab.icon"
                                            class="w-4 h-4"
                                        />
                                        {{ tab.label }}
                                    </button>
                                </template>
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
                            style="min-height: 600px; max-height: 55vh"
                        >
                            <div class="min-h-full">
                                <slot :form="form" :errors="errors" />
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end gap-2 mt-4 px-6 pb-6">
                            <button
                                type="button"
                                @click="$emit('close')"
                                class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition border border-gray-300 shadow-sm"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="loading"
                                class="px-5 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold shadow hover:scale-105 transition-transform flex items-center gap-2 border border-indigo-700/10 disabled:opacity-70 disabled:cursor-not-allowed"
                            >
                                <template v-if="loading">
                                    <svg
                                        class="w-5 h-5 animate-spin text-white"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        ></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                        ></path>
                                    </svg>
                                </template>
                                <template v-else>
                                    <span>{{ submitLabel }}</span>
                                </template>
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

const loading = ref(false);

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
    loading.value = true;
    const data = props.transform(props.form);
    router[props.method](props.endpoint, data, {
        onSuccess: (page) => {
            loading.value = false;
            if (page.props?.success) {
                emit("success", page.props.success);
            }
        },
        onError: (err) => {
            loading.value = false;
            errors.value = err;
        },
        onFinish: () => {
            loading.value = false;
        },
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
