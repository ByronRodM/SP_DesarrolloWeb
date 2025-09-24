<template>
    <v-container class="d-flex align-center justify-center fill-height">
        <v-card class="w-full max-w-md p-6 rounded-2xl">
            <div class="text-center mb-4">
                <v-icon color="warning" size="48" class="mb-2">mdi-key-change</v-icon>
                <h2 class="text-xl font-semibold">Cambio de Contraseña Obligatorio</h2>
                <p class="text-sm text-gray-500">
                    Por seguridad, debes cambiar tu contraseña antes de continuar
                </p>
            </div>

            <v-form v-model="valid" @submit.prevent="onSubmit">
                <v-text-field
                    v-model="currentPassword"
                    label="Contraseña Actual"
                    type="password"
                    :rules="[rules.required]"
                    prepend-inner-icon="mdi-lock"
                    density="comfortable"
                    clearable />

                <v-text-field
                    v-model="newPassword"
                    label="Nueva Contraseña"
                    type="password"
                    :rules="[rules.required, rules.min8]"
                    prepend-inner-icon="mdi-lock-plus"
                    density="comfortable"
                    clearable />

                <v-text-field
                    v-model="confirmPassword"
                    label="Confirmar Nueva Contraseña"
                    type="password"
                    :rules="[rules.required, rules.match]"
                    prepend-inner-icon="mdi-lock-check"
                    density="comfortable"
                    clearable />

                <v-alert
                    v-if="errorMsg"
                    type="error"
                    variant="tonal"
                    class="mb-3"
                    :text="errorMsg"
                    density="comfortable" />

                <v-alert
                    v-if="successMsg"
                    type="success"
                    variant="tonal"
                    class="mb-3"
                    :text="successMsg"
                    density="comfortable" />

                <v-btn
                    :loading="loading"
                    :disabled="!valid || loading"
                    type="submit"
                    color="primary"
                    class="w-full">
                    Cambiar Contraseña
                </v-btn>
            </v-form>

            <div class="text-center mt-4">
                <p class="text-xs text-gray-500">
                    No puedes acceder a otras funciones hasta cambiar la contraseña
                </p>
            </div>
        </v-card>
    </v-container>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/api";

const router = useRouter();

const currentPassword = ref("");
const newPassword = ref("");
const confirmPassword = ref("");
const loading = ref(false);
const valid = ref(false);
const errorMsg = ref("");
const successMsg = ref("");

const rules = {
    required: (v: string) => !!v || "Requerido",
    min8: (v: string) => (v?.length ?? 0) >= 8 || "Mínimo 8 caracteres",
    match: (v: string) => v === newPassword.value || "Las contraseñas no coinciden",
};

const onSubmit = async () => {
    errorMsg.value = "";
    successMsg.value = "";
    loading.value = true;

    try {
        const response = await api.post("/change-password", {
            current_password: currentPassword.value,
            new_password: newPassword.value,
            new_password_confirmation: confirmPassword.value,
        });

        successMsg.value = response.data.message;

        // Actualizar el usuario en localStorage para quitar el flag
        const userStr = localStorage.getItem("user");
        if (userStr) {
            const user = JSON.parse(userStr);
            user.must_change_password = false;
            localStorage.setItem("user", JSON.stringify(user));
        }

        // Redirigir después de 2 segundos
        setTimeout(() => {
            router.push("/usuarios");
        }, 2000);
    } catch (e: any) {
        console.error("Error al cambiar contraseña:", e);
        errorMsg.value =
            e?.response?.data?.message ||
            e?.response?.data?.errors?.current_password?.[0] ||
            e?.response?.data?.errors?.new_password?.[0] ||
            "Error al cambiar la contraseña. Intenta de nuevo.";
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.min-h-screen {
    min-height: 100vh;
}

.max-w-md {
    max-width: 28rem;
}

.p-6 {
    padding: 1.5rem;
}

.mb-2 {
    margin-bottom: 0.5rem;
}

.mb-3 {
    margin-bottom: 0.75rem;
}

.mb-4 {
    margin-bottom: 1rem;
}

.mt-4 {
    margin-top: 1rem;
}

.w-full {
    width: 100%;
}

.text-center {
    text-align: center;
}

.text-xl {
    font-size: 1.25rem;
}

.font-semibold {
    font-weight: 600;
}

.text-sm {
    font-size: 0.875rem;
}

.text-xs {
    font-size: 0.75rem;
}

.text-gray-500 {
    color: #6b7280;
}

.rounded-2xl {
    border-radius: 1rem;
}
</style>
