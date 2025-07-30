<template>
    <transition name="fade" class="modaleStyle">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
        >
            <div class="rounded-2xl shadow-2xl w-full max-w-2xl transition-all">
                <div class="bg-white rounded-lg p-6 relative min-h-[250px]">
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
                        v-if="!props.infoOnly"
                        @submit.prevent="handleSubmit"
                        class="space-y-4"
                        autocomplete="off"
                    >
                        <slot :form="form" :errors="errors"></slot>
                        <div class="flex justify-end gap-2 mt-6">
                            <button
                                type="button"
                                @click="$emit('close')"
                                :disabled="isLoading"
                                class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition disabled:opacity-70 disabled:cursor-not-allowed"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="isLoading"
                                class="px-5 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold shadow hover:scale-105 transition-transform flex items-center gap-2 border border-indigo-700/10 disabled:opacity-70 disabled:cursor-not-allowed"
                            >
                                <template v-if="isLoading">
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
                    <div v-else>
                        <slot :form="form" :errors="errors"></slot>
                        <div class="flex justify-end gap-2 mt-6">
                            <button
                                type="button"
                                @click="$emit('close')"
                                class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition"
                            >
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { reactive, ref, watchEffect } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    show: Boolean,
    title: String,
    submitLabel: String,
    initialForm: Object,
    endpoint: String,
    method: String,
    transform: {
        type: Function,
        default: (form) => form,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    infoOnly: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close", "success", "submit", "general-error"]);

const form = reactive({});
const errors = ref({});
const isLoading = ref(false);
watchEffect(() => {
    if (props.show) {
        Object.assign(form, props.initialForm);
        errors.value = {};
    }
});

function handleSubmit() {
    isLoading.value = true;
    if (props.infoOnly) return; // No hacer nada si es solo informativo
    errors.value = {};

    const data = props.transform(form);
    emit("submit", data); // puedes capturarlo en el padre si deseas

    const hasFile = Object.values(data).some((v) => v instanceof File);

    if (hasFile) {
        const formData = new FormData();
        for (const key in data) {
            formData.append(key, data[key]);
        }

        router.post(props.endpoint, formData, {
            forceFormData: true,
            onSuccess: (page) => {
                if (page.props?.success) {
                    emit("success", page.props.success);
                    isLoading.value = false;
                }
            },
            onError: (err) => {
                errors.value = err;
                isLoading.value = false;
                if (err && err.general) {
                    emit("general-error", err.general);
                }
            },
        });
    } else {
        router[props.method](props.endpoint, data, {
            onSuccess: (page) => {
                if (page.props?.success) {
                    emit("success", page.props.success);
                    isLoading.value = false;
                }
            },
            onError: (err) => {
                errors.value = err;
                isLoading.value = false;
                if (err && err.general) {
                    emit("general-error", err.general);
                }
            },
        });
    }
}
</script>
