<template>
    <v-container fluid>
        <v-row>
            <v-col cols="12">
                <div class="d-flex justify-space-between align-center mb-4 flex-wrap gap-2">
                    <h2 class="text-h6 mb-0">Tareas</h2>
                    <div class="d-flex gap-2">
                        <v-btn v-if="isAdmin" color="primary" @click="goNew">Nueva tarea</v-btn>
                        <v-btn
                            v-if="isAdmin"
                            color="indigo"
                            :variant="downloading ? 'tonal' : 'elevated'"
                            prepend-icon="mdi-download"
                            @click="downloadPendientes"
                            :loading="downloading">
                            DESCARGAR FORMULARIO
                        </v-btn>
                    </div>
                </div>
                <v-alert
                    v-if="downloadError"
                    type="error"
                    variant="tonal"
                    class="mb-2"
                    density="comfortable">
                    {{ downloadError }}
                </v-alert>
                <v-data-table
                    :items="rows"
                    :headers="headers"
                    :loading="loading"
                    class="elevation-1">
                    <template #item.estado="{ item }">
                        <div class="d-flex align-center">
                            <v-chip
                                :color="estadoColor(item.estado)"
                                size="small"
                                variant="tonal"
                                class="mr-2">
                                {{ item.estado }}
                            </v-chip>
                            <v-menu v-if="isAdmin" location="bottom" transition="fade-transition">
                                <template #activator="{ props }">
                                    <v-btn
                                        v-bind="props"
                                        size="x-small"
                                        variant="tonal"
                                        icon="mdi-pencil"></v-btn>
                                </template>
                                <v-list density="compact">
                                    <v-list-item
                                        v-for="e in estados"
                                        :key="e"
                                        @click="changeEstado(item, e)">
                                        <v-list-item-title>{{ e }}</v-list-item-title>
                                    </v-list-item>
                                </v-list>
                            </v-menu>
                        </div>
                    </template>
                    <template #item.fecha_vencimiento="{ item }">
                        {{ formatDate(item.fecha_vencimiento) }}
                    </template>
                    <template #item.created_at="{ item }">
                        {{ formatDate(item.created_at) }}
                    </template>
                    <template #no-data>
                        <div class="pa-6 text-center">No hay tareas.</div>
                    </template>
                </v-data-table>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import api from "@/services/api";
import { useRouter } from "vue-router";

type Tarea = {
    id: number;
    titulo: string;
    descripcion?: string;
    estado: string;
    fecha_vencimiento?: string;
    usuario?: string;
    usuario_id: number;
    created_at?: string;
};

const router = useRouter();
const items = ref<Tarea[]>([]);
const loading = ref(false);
const estados = ["pendiente", "en_progreso", "completada"];
const downloading = ref(false);
const downloadError = ref("");
const user = ref<{ rol: string } | null>(null);
onMounted(() => {
    const raw = localStorage.getItem("user");
    user.value = raw ? JSON.parse(raw) : null;
});
const isAdmin = computed(() => user.value?.rol === "admin");

const headers = [
    { title: "Título", value: "titulo" },
    { title: "Descripción", value: "descripcion" },
    { title: "Estado", value: "estado" },
    { title: "Usuario", value: "usuario" },
    { title: "Vencimiento", value: "fecha_vencimiento" },
    { title: "Creado", value: "created_at" },
];

const fetchTareas = async () => {
    loading.value = true;
    try {
        const { data } = await api.get<Tarea[]>("/tareas");
        items.value = data;
    } finally {
        loading.value = false;
    }
};

onMounted(fetchTareas);

const rows = computed(() => items.value);

const formatDate = (iso?: string) => {
    if (!iso) return "-";
    const d = new Date(iso);
    if (isNaN(d.getTime())) return iso;
    return d.toLocaleDateString("es-GT", { year: "numeric", month: "2-digit", day: "2-digit" });
};

const estadoColor = (estado: string) => {
    switch (estado) {
        case "pendiente":
            return "orange";
        case "en_progreso":
            return "blue";
        case "completada":
            return "green";
        default:
            return "grey";
    }
};

const goNew = () => router.push("/tareas/nueva");

const changeEstado = async (t: Tarea, nuevo: string) => {
    if (nuevo === t.estado) return;
    const old = t.estado;
    t.estado = nuevo;
    try {
        await api.put(`/tareas/${t.id}`, { estado: nuevo });
    } catch (e) {
        t.estado = old; // revertir
    }
};

const downloadPendientes = async () => {
    try {
        downloading.value = true;
        downloadError.value = "";
        const resp = await api.get("/tareas/export/pending", { responseType: "blob" });
        const blob = new Blob([resp.data], { type: "text/csv;charset=utf-8;" });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "tareas_pendientes.csv";
        a.click();
        window.URL.revokeObjectURL(url);
    } catch (e: any) {
        downloadError.value = e?.response?.data?.message || "Error al descargar";
    } finally {
        downloading.value = false;
    }
};
</script>
