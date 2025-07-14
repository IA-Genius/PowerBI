<template>
    <div class="flex justify-center gap-2">
        <button
            v-if="edit"
            @click="$emit('edit')"
            class="p-1.5 rounded-full bg-yellow-400 hover:bg-yellow-500 text-white shadow-md transition transform hover:scale-105"
            :title="editTitle"
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
            v-if="remove"
            @click="$emit('delete')"
            class="p-1.5 rounded-full bg-red-600 hover:bg-red-700 text-white shadow-md transition transform hover:scale-105"
            :title="removeTitle"
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

        <button
            v-if="list"
            @click="$emit('list')"
            class="p-1.5 rounded-full bg-blue-500 hover:bg-blue-600 text-white shadow-md transition transform hover:scale-105"
            :title="listTitle"
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
                    d="M4 6h16M4 12h16M4 18h16"
                />
            </svg>
        </button>
    </div>
</template>

<script setup>
const props = defineProps({
    edit: { type: Boolean, default: false },
    remove: { type: Boolean, default: false },
    list: { type: Boolean, default: false },
    editTitle: { type: String, default: "Editar" },
    removeTitle: { type: String, default: "Eliminar" },
    listTitle: { type: String, default: "Ver listado" },
});
</script>
