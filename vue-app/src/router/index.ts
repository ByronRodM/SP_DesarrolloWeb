import { createRouter, createWebHistory } from "vue-router";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: "/", redirect: "/usuarios" },
        { path: "/login", name: "login", component: () => import("@/views/LoginView.vue") },
        {
            path: "/change-password",
            name: "change-password",
            component: () => import("@/views/ChangePasswordView.vue"),
            meta: { requiresAuth: true },
        },

        {
            path: "/usuarios",
            name: "usuarios",
            component: () => import("@/views/HomeView.vue"),
            meta: { requiresAuth: true },
        },

        {
            path: "/usuarios/nuevo",
            name: "usuarios-nuevo",
            component: () => import("@/views/UserForm.vue"),
            meta: { requiresAuth: true },
        },
        {
            path: "/usuarios/:id/editar",
            name: "usuarios-editar",
            component: () => import("@/views/UserForm.vue"),
            props: true,
            meta: { requiresAuth: true },
        },

        // Tareas
        {
            path: "/tareas",
            name: "tareas",
            component: () => import("@/views/TareasList.vue"),
            meta: { requiresAuth: true },
        },
        {
            path: "/tareas/nueva",
            name: "tareas-nueva",
            component: () => import("@/views/TareaForm.vue"),
            meta: { requiresAuth: true },
        },

        { path: "/:pathMatch(.*)*", redirect: "/usuarios" },
    ],
});

router.beforeEach((to) => {
    const token = localStorage.getItem("token");
    const userStr = localStorage.getItem("user");

    // Si no hay token y la ruta requiere auth, redirigir a login
    if (to.meta.requiresAuth && !token) return { name: "login", query: { redirect: to.fullPath } };

    // Si ya está logueado y trata de ir a login, redirigir a usuarios
    if (to.name === "login" && token) return { name: "usuarios" };

    // Verificar si debe cambiar contraseña (solo si está autenticado)
    if (token && userStr && to.name !== "change-password") {
        try {
            const user = JSON.parse(userStr);
            if (user.must_change_password) {
                return { name: "change-password" };
            }
        } catch (e) {
            // Si hay error parseando el usuario, limpiar y redirigir a login
            localStorage.removeItem("token");
            localStorage.removeItem("user");
            return { name: "login" };
        }
    }

    return true;
});

export default router;
