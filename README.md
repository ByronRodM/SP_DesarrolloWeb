# Proyecto Laravel + Vue (Segundo Parcial Desarrollo Web)

Este repositorio contiene:

-   `laravel-api/`: API Laravel (usuarios, tareas, autenticación, multitenancy).
-   `vue-app/`: SPA Vue 3 que consume la API y aplica control de sesión y rol.

## Autenticación

Implementada con Laravel Sanctum (tokens). Todas las rutas del CRUD se protegen con `auth:sanctum`; respuestas 401 cuando falta token. El frontend agrega automáticamente el header `Authorization` y restringe vistas según rol. El rol `admin` puede mutar (crear/editar/eliminar usuarios y tareas y exportar); el rol `usuario` solo lee.

## Multitenancy

Aislamiento por subdominio usando middleware de tenancy en los grupos `api` y `web`. Cada tenant (ej. `empresa1.*`, `empresa2.*`) opera sobre su propio contexto de datos; no hay fuga de usuarios ni tareas entre tenants. Resolución automática antes de ejecutar controladores.

## Despliegue (EC2)

Preparado y probado en una instancia con stack PHP 8.2 + Composer + Node + Apache2 + base de datos. CRUD y autenticación en entorno remoto. Rama usada: `feature/segundoParcial`.

## Documentación

La documentación complementaria se encuentra en la carpeta raíz `docs/`. Contiene descripciones concisas de: estado base, autenticación/roles, multitenancy y despliegue.

## Evidencias

Capturas colocadas en `docs/images/` y referenciados en los docs (login, subdominios aislados, exportación, instancia EC2).

## Rama y Trazabilidad

Trabajo desarrollado sobre `feature/segundoParcial` con commits incrementales.

---

> Byron Daniel Rodríguez Mazariegos
