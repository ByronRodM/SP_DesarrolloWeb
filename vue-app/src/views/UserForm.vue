<template>
    <v-container class="py-8" style="max-width: 800px">
        <v-card class="pa-6">
            <div class="d-flex align-center mb-4">
                <v-btn icon="mdi-arrow-left" variant="text" @click="goBack" class="mr-2" />
                <h2 class="text-h6 mb-0">{{ isEdit ? "Editar usuario" : "Nuevo usuario" }}</h2>
            </div>

            <v-form v-model="valid" @submit.prevent="onSubmit">
                <v-row>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.nombre"
                            :rules="[rules.required]"
                            label="Nombre"
                            prepend-inner-icon="mdi-account"
                            required />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.email"
                            :rules="[rules.required, rules.email]"
                            label="Email"
                            prepend-inner-icon="mdi-email"
                            required
                            type="email" />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.password"
                            :rules="isEdit ? [] : [rules.required, rules.min6]"
                            :type="showPass ? 'text' : 'password'"
                            label="Password"
                            prepend-inner-icon="mdi-lock"
                            :append-inner-icon="showPass ? 'mdi-eye-off' : 'mdi-eye'"
                            @click:append-inner="showPass = !showPass"
                            :hint="isEdit ? 'Dejar vacío para no cambiar' : ''"
                            :persistent-hint="isEdit" />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.rol"
                            :items="roles"
                            :rules="[rules.required]"
                            label="Rol"
                            prepend-inner-icon="mdi-shield-account" />
                    </v-col>
                </v-row>

                <v-alert
                    v-if="error"
                    type="error"
                    variant="tonal"
                    class="mb-4"
                    density="comfortable">
                    {{ error }}
                </v-alert>
                <v-alert
                    v-if="success"
                    type="success"
                    variant="tonal"
                    class="mb-4"
                    density="comfortable">
                    {{ success }}
                </v-alert>

                <div class="d-flex gap-2 justify-center mt-2">
                    <v-btn
                        color="primary"
                        type="submit"
                        :loading="loading"
                        :disabled="!valid || loading">
                        {{ isEdit ? "Guardar cambios" : "Crear usuario" }}
                    </v-btn>
                    <v-btn variant="tonal" color="grey" @click="goBack">Cancelar</v-btn>
                </div>
            </v-form>
        </v-card>
    </v-container>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "@/services/api";

interface UsuarioForm {
    nombre: string;
    email: string;
    password: string;
    rol: "admin" | "usuario" | "";
}

const router = useRouter();
const route = useRoute();
const idParam = route.params.id as string | undefined;
const isEdit = computed(() => !!idParam);

const roles = ["admin", "usuario"];

const form = reactive<UsuarioForm>({
    nombre: "",
    email: "",
    password: "",
    rol: "",
});

const valid = ref(false);
const loading = ref(false);
const error = ref("");
const success = ref("");
const showPass = ref(false);

const rules = {
    required: (v: any) => !!v || "Requerido",
    email: (v: string) => /.+@.+\..+/.test(v) || "Email inválido",
    min6: (v: string) => v.length >= 6 || "Mínimo 6 caracteres",
};

const goBack = () => router.push("/usuarios");

const loadUser = async () => {
    if (!isEdit.value) return;
    loading.value = true;
    try {
        const { data } = await api.get(`/usuarios/getUser/${idParam}`);
        // Si la API aún no implementa show, manejaría ausencia.
        if (!data) throw new Error("No se pudo cargar el usuario");
        form.nombre = data.nombre;
        form.email = data.email;
        form.rol = data.rol;
    } catch (e: any) {
        error.value = e.response?.data?.message || e.message || "Error cargando usuario";
    } finally {
        loading.value = false;
    }
};

onMounted(loadUser);

const onSubmit = async () => {
    error.value = "";
    success.value = "";
    loading.value = true;
    try {
        if (isEdit.value) {
            const payload: any = { nombre: form.nombre, email: form.email, rol: form.rol };
            if (form.password) payload.password = form.password;
            await api.put(`/usuarios/updateUser/${idParam}`, payload);
            success.value = "Usuario actualizado";
        } else {
            await api.post("/usuarios/addUser", {
                nombre: form.nombre,
                email: form.email,
                password: form.password,
                rol: form.rol,
            });
            success.value = "Usuario creado";
        }
        // breve retraso y volver
        setTimeout(() => router.push("/usuarios"), 600);
    } catch (e: any) {
        error.value = e.response?.data?.message || "Error al guardar";
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 0.5rem;
}
</style>
