<template>
    <v-app>
        <v-app-bar density="comfortable">
            <template #prepend>
                <v-btn
                    v-if="showBack"
                    prepend-icon="mdi-home"
                    color="primary"
                    variant="elevated"
                    size="small"
                    class="mr-2 text-capitalize"
                    @click="goHome">
                    Inicio
                </v-btn>
            </template>
            <v-toolbar-title class="text-subtitle-1 font-weight-medium">
                Segundo Parcial - Desarrollo Web
            </v-toolbar-title>
        </v-app-bar>
        <v-main>
            <router-view />
        </v-main>
    </v-app>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

// Rutas donde mostrar el botón
const backRoutes = ["usuarios-nuevo", "tareas", "tareas-nueva"];
const showBack = computed(() => backRoutes.includes(route.name as string));
const goHome = () => router.push("/usuarios");
</script>

<style scoped></style>
