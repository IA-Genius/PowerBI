<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";

const props = defineProps({
    align: { type: String, default: "right" },
    minWidth: { type: String, default: "180px" },
    maxWidth: { type: String, default: "220px" },
    contentClasses: { type: String, default: "bg-white" },
});

const open = ref(false);
const triggerRef = ref(null);
const dropdownRef = ref(null);

const closeOnEscape = (e) => {
    if (open.value && e.key === "Escape") {
        open.value = false;
    }
};

onMounted(() => {
    document.addEventListener("keydown", closeOnEscape);
});
onUnmounted(() => {
    document.removeEventListener("keydown", closeOnEscape);
});

const alignmentClasses = computed(() => {
    if (props.align === "left") {
        return "ltr:origin-top-left rtl:origin-top-right start-0";
    } else if (props.align === "right") {
        return "ltr:origin-top-right rtl:origin-top-left end-0";
    } else {
        return "origin-top";
    }
});
</script>

<template>
    <div class="relative">
        <!-- Trigger -->
        <div ref="triggerRef" @click="open = !open">
            <slot name="trigger" />
        </div>

        <!-- Overlay -->
        <div
            v-show="open"
            class="fixed inset-0 z-40"
            @click="open = false"
        ></div>

        <!-- Dropdown animado con Motion -->
        <Motion
            v-show="open"
            ref="dropdownRef"
            tag="div"
            :initial="{ opacity: 0, y: -8, scale: 0.98 }"
            :enter="{
                opacity: 1,
                y: 0,
                scale: 1,
                transition: {
                    type: 'spring',
                    stiffness: 320,
                    damping: 22,
                    duration: 0.18,
                },
            }"
            :leave="{
                opacity: 0,
                y: 8,
                scale: 0.98,
                transition: { duration: 0.13 },
            }"
            class="absolute z-50 mt-2 rounded-xl shadow-xl border border-gray-200 bg-white"
            :class="alignmentClasses"
            @click="open = false"
            style="width: 100%"
        >
            <div
                class="rounded-md ring-1 ring-black/10 ring-opacity-10 shadow-sm"
                :class="contentClasses"
                style="backdrop-filter: blur(2px)"
            >
                <slot name="content" />
            </div>
        </Motion>
    </div>
</template>
