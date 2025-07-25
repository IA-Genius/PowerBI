<script setup>
import { computed, onMounted, onUnmounted, ref, watch, nextTick } from "vue";

const props = defineProps({
    align: { type: String, default: "right" },
    minWidth: { type: String, default: "180px" },
    maxWidth: { type: String, default: "220px" },
    contentClasses: { type: String, default: "bg-white" },
});

const open = ref(false);
const triggerRef = ref(null);
const dropdownRef = ref(null);
const dropdownWidth = ref(parseInt(props.minWidth)); // default

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

watch(open, async (isOpen) => {
    if (isOpen) {
        await nextTick();
        if (triggerRef.value) {
            const triggerW = triggerRef.value.offsetWidth;
            const minW = parseInt(props.minWidth);
            const maxW = parseInt(props.maxWidth);
            dropdownWidth.value = Math.max(minW, Math.min(triggerW, maxW));
        }
    }
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

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                ref="dropdownRef"
                v-show="open"
                class="absolute z-50 mt-2 rounded-md shadow-lg"
                :class="alignmentClasses"
                :style="{ width: dropdownWidth + 'px' }"
                @click="open = false"
            >
                <div
                    class="rounded-md ring-1 ring-black ring-opacity-5"
                    :class="contentClasses"
                >
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>
