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
});

const emit = defineEmits(["close", "success"]);

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

    router[props.method](props.endpoint, data, {
        onSuccess: (page) => {
            if (page.props?.success) {
                emit("success", page.props.success);
            }
        },
        onError: (err) => (errors.value = err),
    });
}
</script>
