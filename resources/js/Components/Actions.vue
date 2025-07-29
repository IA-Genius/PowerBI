<template>
    <div class="flex justify-center gap-2">
        <button
            v-if="edit"
            @click="$emit('edit')"
            class="action-btn bg-gradient-to-tr from-indigo-500 to-indigo-400 hover:from-indigo-600 hover:to-indigo-500 text-white"
            :title="editTitle"
        >
            <span class="sr-only">Editar</span>
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
                    d="M15.232 5.232l3.536 3.536M9 13l6.293-6.293a1 1 0 011.414 0l3.586 3.586a1 1 0 010 1.414L13 17H9v-4z"
                />
            </svg>
        </button>

        <button
            v-if="remove"
            @click="$emit('delete')"
            class="action-btn bg-gradient-to-tr from-rose-500 to-rose-400 hover:from-rose-600 hover:to-rose-500 text-white"
            :title="removeTitle"
        >
            <span class="sr-only">Eliminar</span>
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
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>

        <button
            v-if="canViewHistory"
            @click="$emit('history')"
            class="action-btn bg-gradient-to-tr from-yellow-400 to-yellow-300 hover:from-yellow-500 hover:to-yellow-400 text-yellow-900 border border-yellow-300"
            title="Ver historial de asignaciones"
        >
            <span class="sr-only">Historial</span>
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
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
        </button>

        <button
            v-if="list"
            @click="$emit('list')"
            class="action-btn bg-gradient-to-tr from-blue-500 to-blue-400 hover:from-blue-600 hover:to-blue-500 text-white"
            :title="listTitle"
        >
            <span class="sr-only">Listado</span>
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
    canViewHistory: { type: Boolean, default: false },
});
</script>

<style scoped>
/* Botones de acción compactos */
.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.55rem;
    padding: 0.22rem 0.38rem;
    font-size: 0.92rem;
    font-weight: 500;
    box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.07);
    transition: all 0.16s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
    border: none;
    cursor: pointer;
    gap: 0.18rem;
    position: relative;
    min-width: 28px;
    min-height: 28px;
}
.action-btn:active {
    transform: scale(0.97);
}
.action-btn svg {
    display: block;
    width: 1.1em;
    height: 1.1em;
}
.action-btn:not(:disabled):hover {
    filter: brightness(1.08) saturate(1.2);
    box-shadow: 0 4px 16px 0 rgba(0, 0, 0, 0.1);
    z-index: 2;
}
.action-btn:focus {
    box-shadow: 0 0 0 2px #6366f1;
}
</style>
