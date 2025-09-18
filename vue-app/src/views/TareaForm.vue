<template>
    <v-container style="max-width: 800px" class="py-8">
        <v-card class="pa-6">
            <div class="d-flex align-center mb-4">
                <v-btn icon="mdi-arrow-left" variant="text" @click="goBack" class="mr-2" />
                <h2 class="text-h6 mb-0">Nueva tarea</h2>
            </div>

            <v-form v-model="valid" @submit.prevent="onSubmit">
                <v-row>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.titulo"
                            :rules="[rules.required]"
                            label="Título"
                            prepend-inner-icon="mdi-format-title" />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.usuario_id"
                            :items="usuarios"
                            item-title="nombre"
                            item-value="id"
                            :rules="[rules.required]"
                            label="Usuario"
                            prepend-inner-icon="mdi-account" />
                    </v-col>
                    <v-col cols="12">
                        <v-textarea
                            v-model="form.descripcion"
                            label="Descripción"
                            auto-grow
                            rows="2"
                            prepend-inner-icon="mdi-text" />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.estado"
                            :items="estados"
                            label="Estado"
                            prepend-inner-icon="mdi-flag" />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.fecha_vencimiento"
                            type="date"
                            label="Fecha vencimiento"
                            prepend-inner-icon="mdi-calendar" />
                    </v-col>
                </v-row>

                <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
                    {{ error }}
                </v-alert>
                <v-alert v-if="success" type="success" variant="tonal" class="mb-4">
                    {{ success }}
                </v-alert>

                <div class="d-flex justify-center gap-2 mt-2">
                    <v-btn
                        color="primary"
                        type="submit"
                        :loading="loading"
                        :disabled="!valid || loading">
                        Crear tarea
                    </v-btn>
                    <v-btn variant="tonal" color="grey" @click="goBack">Cancelar</v-btn>
                </div>
            </v-form>
        </v-card>
    </v-container>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from "vue";
import api from "@/services/api";
import { useRouter } from "vue-router";

type Usuario = { id: number; nombre: string };

const router = useRouter();
const usuarios = ref<Usuario[]>([]);
const estados = ["pendiente", "en_progreso", "completada"];

const form = reactive({
    titulo: "",
    descripcion: "",
    usuario_id: null as number | null,
    estado: "pendiente",
    fecha_vencimiento: "",
});

const loading = ref(false);
const valid = ref(false);
const error = ref("");
const success = ref("");

const rules = { required: (v: any) => !!v || "Requerido" };

const fetchUsuarios = async () => {
    try {
        const { data } = await api.get<Usuario[]>("/usuarios/listUsers");
        usuarios.value = data;
    } catch (e) {
        /* noop */
    }
};

onMounted(fetchUsuarios);

const goBack = () => router.push("/tareas");

const onSubmit = async () => {
    error.value = "";
    success.value = "";
    loading.value = true;
    try {
        await api.post("/tareas/create", {
            titulo: form.titulo,
            descripcion: form.descripcion || undefined,
            usuario_id: form.usuario_id,
            estado: form.estado,
            fecha_vencimiento: form.fecha_vencimiento || undefined,
        });
        success.value = "Tarea creada";
        setTimeout(() => router.push("/tareas"), 600);
    } catch (e: any) {
        error.value = e.response?.data?.message || "Error al crear la tarea";
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
