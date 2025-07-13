<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import Swal from "sweetalert2";

const props = defineProps({
    show: Boolean,
    user: Object,
});
const emit = defineEmits(["close", "success"]);

const name = ref("");
const email = ref("");
const password = ref("");
const error = ref({});
const active = ref(true);

watch(
    () => props.user,
    (newUser) => {
        if (newUser) {
            name.value = newUser.name;
            email.value = newUser.email;
            password.value = "";
            active.value = !!newUser.active;
        } else {
            name.value = "";
            email.value = "";
            password.value = "";
            active.value = true;
        }
        error.value = {};
    }
);

function cerrar() {
    emit("close");
    error.value = {};
}

function guardarUsuario() {
    if (props.user) {
        router.put(
            `/users/${props.user.id}`,
            {
                name: name.value,
                email: email.value,
                password: password.value,
                active: active.value,
            },
            {
                onError: (errors) => {
                    error.value = errors;
                },
                onSuccess: () => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Usuario actualizado correctamente",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    });
                    emit("success");
                },
            }
        );
    } else {
        router.post(
            "/users",
            {
                name: name.value,
                email: email.value,
                password: password.value,
                active: active.value,
            },
            {
                onError: (errors) => {
                    error.value = errors;
                },
                onSuccess: () => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Usuario creado correctamente",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    });
                    emit("success");
                },
            }
        );
    }
}
</script>

<template>
    <transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
        >
            <div
                class="bg-gradient-to-br from-blue-600 to-indigo-600 p-1 rounded-xl shadow-2xl w-full max-w-md transition-all"
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
                        {{ props.user ? "Editar Usuario" : "Agregar Usuario" }}
                    </h2>
                    <form
                        @submit.prevent="guardarUsuario"
                        class="space-y-4"
                        autocomplete="off"
                    >
                        <!-- INPUTS FALSOS PARA ENGAÑAR AL NAVEGADOR -->
                        <input
                            type="text"
                            name="fakeusernameremembered"
                            style="display: none"
                            autocomplete="off"
                        />
                        <input
                            type="password"
                            name="fakepasswordremembered"
                            style="display: none"
                            autocomplete="off"
                        />

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Nombre</label
                            >
                            <input
                                v-model="name"
                                type="text"
                                name="new_user_name"
                                placeholder="Nombre"
                                autocomplete="off"
                                class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            />
                            <div
                                v-if="error.name"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ error.name }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Email</label
                            >
                            <input
                                v-model="email"
                                type="email"
                                name="new_user_email"
                                placeholder="Email"
                                autocomplete="off"
                                class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            />
                            <div
                                v-if="error.email"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ error.email }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                {{
                                    props.user
                                        ? "Nueva contraseña (opcional)"
                                        : "Contraseña"
                                }}
                            </label>
                            <input
                                v-model="password"
                                type="password"
                                name="new_user_password"
                                :placeholder="
                                    props.user
                                        ? 'Nueva contraseña (opcional)'
                                        : 'Contraseña'
                                "
                                autocomplete="off"
                                readonly
                                @focus="
                                    (e) => e.target.removeAttribute('readonly')
                                "
                                class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            />
                            <div
                                v-if="error.password"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ error.password }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                for="estado"
                                >Estado</label
                            >
                            <select
                                id="estado"
                                v-model="active"
                                class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            >
                                <option :value="true">Activo</option>
                                <option :value="false">Inactivo</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <button
                                type="button"
                                @click="cerrar"
                                class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="px-5 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold shadow hover:scale-105 transition-transform"
                            >
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </transition>
</template>

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
