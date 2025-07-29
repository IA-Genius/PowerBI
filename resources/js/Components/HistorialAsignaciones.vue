<template>
    <div class="space-y-7">
        <template v-for="(cabecera, idx) in cabeceras" :key="cabecera.id">
            <div
                class="rounded-2xl border border-indigo-100 bg-white shadow animate__animated animate__fadeIn"
            >
                <button
                    class="w-full flex items-center justify-between p-4 focus:outline-none group"
                    @click="toggle(idx)"
                >
                    <div
                        class="flex flex-col sm:flex-row sm:items-center gap-3 w-full"
                    >
                        <span class="font-bold text-indigo-800 text-base">
                            {{ cabecera.usuario_cambio?.name || "—" }}
                        </span>
                        <span
                            class="text-xs bg-indigo-500/90 text-white rounded px-2 py-0.5 font-semibold"
                        >
                            {{ cabecera.motivo }}
                        </span>
                        <span class="flex items-center text-xs">
                            <span
                                class="bg-gray-100 rounded px-2 py-0.5 mr-1 font-medium"
                            >
                                {{ cabecera.asignado_de?.name || "—" }}
                            </span>
                            <span class="mx-1 text-gray-400">→</span>
                            <span
                                class="bg-blue-100 text-blue-700 rounded px-2 py-0.5 font-semibold"
                            >
                                {{ cabecera.asignado_a?.name || "—" }}
                            </span>
                        </span>
                        <span
                            class="ml-auto text-xs text-gray-500 font-semibold"
                        >
                            {{ formatFecha(cabecera.fecha) }}
                        </span>
                    </div>
                    <span
                        :class="[
                            'ml-4 transition-transform',
                            { 'rotate-180': expanded[idx] },
                        ]"
                        style="font-size: 1.3em; color: #6366f1"
                        >▼</span
                    >
                </button>
                <transition name="fade">
                    <div
                        v-show="expanded[idx]"
                        class="px-6 pb-4 animate__animated animate__fadeIn"
                    >
                        <div
                            v-if="
                                cabecera.auditoria &&
                                cabecera.auditoria.length > 0
                            "
                            class="overflow-x-auto"
                        >
                            <ul
                                class="space-y-2 mt-2 max-h-64 overflow-y-auto pr-2"
                            >
                                <li
                                    v-for="a in cabecera.auditoria"
                                    :key="a.id"
                                    class="bg-indigo-50/80 rounded-lg p-2 border border-indigo-100 flex flex-col gap-1 min-w-[220px]"
                                >
                                    <div
                                        class="flex flex-wrap items-center gap-2 mb-1"
                                    >
                                        <span
                                            class="font-semibold text-indigo-700 text-xs"
                                            >{{ a.usuario?.name || "—" }}</span
                                        >
                                        <span class="text-xs text-gray-400">{{
                                            formatFecha(a.fecha)
                                        }}</span>
                                        <span
                                            class="px-2 py-0.5 rounded bg-indigo-200 text-indigo-800 font-semibold text-xs"
                                            >{{ a.accion }}</span
                                        >
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table
                                            class="w-full text-xs mt-1 border-separate border-spacing-y-1"
                                        >
                                            <tbody>
                                                <tr
                                                    v-for="(
                                                        cambio, campo
                                                    ) in a.campos_editados"
                                                    :key="campo"
                                                >
                                                    <td
                                                        class="font-medium text-indigo-600 whitespace-nowrap pr-2"
                                                    >
                                                        {{ campo }}:
                                                    </td>
                                                    <td
                                                        class="line-through text-red-500 whitespace-nowrap pr-2"
                                                    >
                                                        {{ cambio.old ?? "—" }}
                                                    </td>
                                                    <td
                                                        class="px-1 text-gray-400"
                                                    >
                                                        →
                                                    </td>
                                                    <td
                                                        class="text-green-700 font-semibold whitespace-nowrap"
                                                    >
                                                        {{ cambio.new ?? "—" }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div
                            v-else
                            class="text-gray-400 text-center py-2 text-sm"
                        >
                            Sin detalles de auditoría.
                        </div>
                    </div>
                </transition>
            </div>
        </template>
        <div
            v-if="cabeceras.length === 0"
            class="text-gray-400 text-center py-4 text-sm"
        >
            No hay historial de asignaciones.
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import dayjs from "dayjs";
import isToday from "dayjs/plugin/isToday";
import isYesterday from "dayjs/plugin/isYesterday";
dayjs.extend(isToday);
dayjs.extend(isYesterday);

const props = defineProps({
    cabeceras: {
        type: Array,
        required: true,
    },
});

const expanded = ref([]);

onMounted(() => {
    expanded.value = props.cabeceras.map(() => false); // Todos cerrados por defecto
});

watch(
    () => props.cabeceras,
    (val) => {
        expanded.value = val.map(() => false);
    }
);

function toggle(idx) {
    expanded.value = expanded.value.map((v, i) =>
        i === idx ? !expanded.value[idx] : false
    );
}

function formatFecha(fecha) {
    if (!fecha) return "";
    const d = dayjs(fecha);
    if (d.isToday()) {
        return `Hoy ${d.format("h:mm A")}`;
    } else if (d.isYesterday()) {
        return `Ayer ${d.format("h:mm A")}`;
    } else {
        return d.format("DD/MM/YYYY h:mm A");
    }
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
