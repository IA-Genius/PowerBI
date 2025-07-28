<template>
    <transition name="fade" class="modaleStyle">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
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
                        {{ title }}
                    </h2>
                    <form
                        @submit.prevent="handleSubmit"
                        class="space-y-4"
                        autocomplete="off"
                    >
                        <slot :form="form" :errors="errors"></slot>
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
                                :disabled="props.loading"
                                :class="[
                                    'px-5 py-2 rounded-lg font-semibold shadow transition flex items-center gap-2',
                                    props.loading
                                        ? 'bg-gray-400 cursor-not-allowed text-gray-700'
                                        : 'bg-indigo-600 hover:bg-indigo-700 text-white',
                                ]"
                            >
                                <span
                                    v-if="props.loading"
                                    class="loader"
                                ></span>
                                <span v-else>{{
                                    submitLabel || "Guardar"
                                }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </transition>

    <!-- Overlay de carga -->
    <transition name="fade">
        <div
            v-if="props.loading"
            class="absolute inset-0 z-30 flex items-center justify-center bg-black bg-opacity-30"
        >
            <span class="loader-big"></span>
            <span class="ml-3 text-white font-semibold"
                >Asignando registros...</span
            >
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
});

const emit = defineEmits(["close", "success", "submit"]);

const form = reactive({});
const errors = ref({});

watchEffect(() => {
    if (props.show) {
        Object.assign(form, props.initialForm);
        errors.value = {};
    }
});

function handleSubmit() {
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
                }
            },
            onError: (err) => {
                errors.value = err;
            },
        });
    } else {
        router[props.method](props.endpoint, data, {
            onSuccess: (page) => {
                if (page.props?.success) {
                    emit("success", page.props.success);
                }
            },
            onError: (err) => {
                errors.value = err;
            },
        });
    }
}
</script>

<style scoped>
.loader-big {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #6366f1;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    animation: spin 0.8s linear infinite;
    display: inline-block;
}
.loader {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #6366f1;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    animation: spin 0.8s linear infinite;
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
