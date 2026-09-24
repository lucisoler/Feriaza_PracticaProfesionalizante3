# Feriaza — Documentación del proyecto

**Feriaza** es una aplicación web para la inscripción de feriantes y la reserva de mesas de la feria de la Plaza San Martín de Castelli. Reemplaza el registro manual en cuaderno y talonario por un sistema que aplica las prioridades por categoría y las reglas de la feria.

| Dato | Detalle |
|---|---|
| Estudiante | Soler, Luciano David |
| Carrera | Tecnicatura Superior en Desarrollo de Software — IES "René Favaloro" |
| Unidad curricular | Práctica Profesionalizante III — Parte B (Proyecto Integrador Final) |
| Docente | Hibrich, Sergio Fabián |
| Modalidad | Individual |
| Período | 24/08/2026 — 16/11/2026 |

## Índice

| Documento | Contenido | Estado |
|---|---|---|
| [fundamentacion.md](fundamentacion.md) | Contexto, problemática, usuarios, objetivos, alcance y supuestos | Completo |
| [requerimientos.md](requerimientos.md) | 10 reglas de negocio, 18 requerimientos funcionales en 6 módulos y 18 no funcionales medibles | Completo |
| [roles-y-permisos.md](roles-y-permisos.md) | Roles Visitante, Feriante y Coordinador, y matriz de permisos por acción | Completo |
| [modelo-de-datos.md](modelo-de-datos.md) | Modelo actual y propuesto: DER, diccionario de datos y plan de migración | Completo |
| [diseno-api.md](diseno-api.md) | 37 endpoints REST con método, ruta, rol y RF; autenticación con Sanctum y ejemplos | Completo |
| [diseno-interfaz.md](diseno-interfaz.md) | Principios de diseño, mapa de navegación por rol y 7 wireframes | Completo |
| [stack.md](stack.md) | Tecnologías, versiones y justificación | Completo |
| [instalacion.md](instalacion.md) | Instalación local con MySQL 8, usuarios de prueba y problemas frecuentes | Completo (despliegue en Sprint 6) |
| [decisiones.md](decisiones.md) | 8 decisiones de diseño con contexto y consecuencias | Completo |
| [planificacion.md](planificacion.md) | 18 historias de usuario, 13 tareas técnicas, plan de los sprints 3 a 6, hitos y riesgos | Completo |

## Resumen del proyecto

- **Problema:** la inscripción de feriantes y la asignación de mesas se hacen a mano, en cuaderno y talonario, sin respaldo ni control de prioridades.
- **Usuarios:** feriantes (inscripción y reserva de mesa, mayormente desde el celular) y coordinador (gestión de eventos, feriantes, mesas, asistencia y sanciones).
- **Objetivo:** que ningún feriante habilitado se quede sin mesa, respetando las prioridades por categoría y las reglas de la feria.
- **Stack:** Laravel 11, Vue 3, Inertia.js, Tailwind CSS y MySQL 8.

## Gestión del proyecto

- **Ramas:** `main` (versiones cerradas por sprint), `develop` (integración) y `feature/*` (una por issue), con pull requests hacia `develop`.
- **Planificación:** un milestone por sprint y tablero en GitHub Projects.
- **Comunicación:** GitHub Discussions del repositorio.
