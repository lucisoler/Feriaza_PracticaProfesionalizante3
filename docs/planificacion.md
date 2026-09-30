# Planificación ágil

El proyecto se gestiona con **Scrum** en sprints de dos semanas, con Scrum Daily, Sprint Review y retrospectiva al cierre de cada sprint. Se eligió Scrum porque el alcance puede ajustarse en cada Sprint Review (el docente actúa como Product Owner y puede cambiar requerimientos) y porque las entregas parciales permiten validar el avance con el usuario. El seguimiento se hace en GitHub: un **milestone** por sprint, un **issue** por historia o tarea y un **tablero Kanban** en GitHub Projects (Por hacer, En curso, Hecho).

## 1. Product backlog

Estimación en puntos de historia (escala 1, 2, 3, 5, 8). Prioridad según [requerimientos.md](requerimientos.md).

### Historias de usuario

| ID | Historia de usuario | RF | Prioridad | Puntos | Sprint |
|---|---|---|---|:---:|:---:|
| HU-01 | Como visitante, quiero registrarme como feriante para poder inscribirme a los eventos. | RF-01 | Alta | 3 | 3 |
| HU-02 | Como feriante, quiero elegir mi categoría para que se apliquen las reglas que me corresponden. | RF-02 | Alta | 2 | 3 |
| HU-03 | Como feriante o coordinador, quiero iniciar y cerrar sesión para acceder a mis funciones. | RF-03 | Alta | 3 | 3 |
| HU-04 | Como feriante, quiero ver, modificar o dar de baja mis datos para mantenerlos al día. | RF-04 | Alta | 2 | 3 |
| HU-05 | Como coordinador, quiero gestionar feriantes para cargar y corregir sus datos. | RF-13 | Alta | 3 | 3 |
| HU-06 | Como coordinador, quiero crear y gestionar eventos para abrir las inscripciones. | RF-05 | Alta | 5 | 4 |
| HU-07 | Como feriante, quiero aceptar el reglamento para conocer las reglas antes de inscribirme. | RF-06 | Media | 2 | 4 |
| HU-08 | Como feriante, quiero inscribirme y reservar mi mesa en el mapa para asegurar mi lugar. | RF-07 | Alta | 8 | 4 |
| HU-09 | Como coordinador, quiero asignar mesas según la prioridad para que nadie se quede sin lugar. | RF-09 | Alta | 5 | 4 |
| HU-10 | Como coordinador, quiero ver el listado de inscriptos de un evento para reemplazar el cuaderno. | RF-10 | Alta | 3 | 4 |
| HU-11 | Como coordinador, quiero aprobar o rechazar inscripciones para controlar quién participa. | RF-18 | Alta | 3 | 4 |
| HU-12 | Como coordinador, quiero recibir un aviso por WhatsApp de cada inscripción para enterarme a tiempo. | RF-15 | Media | 2 | 4 |
| HU-13 | Como feriante, quiero confirmar o cancelar mi asistencia para liberar la mesa si no puedo ir. | RF-08 | Media | 3 | 5 |
| HU-14 | Como coordinador, quiero marcar presentes para controlar la asistencia. | RF-11 | Media | 5 | 5 |
| HU-15 | Como coordinador, quiero registrar sanciones para dejar constancia de las ausencias. | RF-12 | Media | 3 | 5 |
| HU-16 | Como coordinador, quiero ver el historial de un feriante para conocer su participación. | RF-14 | Baja | 2 | 5 |
| HU-17 | Como feriante, quiero hablar con el coordinador por WhatsApp para resolver dudas rápido. | RF-16 | Baja | 1 | 5 |
| HU-18 | Como feriante, quiero enviar consultas y ver las respuestas para tener un canal ordenado. | RF-17 | Baja | 3 | 5 |

### Tareas técnicas

| ID | Tarea | Puntos | Sprint |
|---|---|:---:|:---:|
| T-01 | Configurar milestones, issues y tablero de GitHub Projects | 1 | 3 |
| T-02 | Migrar al modelo de datos propuesto y actualizar los seeders | 8 | 3 |
| T-03 | Instalar Sanctum y configurar la autenticación de la API por rol | 3 | 3 |
| T-04 | Crear la capa de servicios compartida entre controladores web y de API (D-02) | 3 | 3 |
| T-05 | Documentar la API y exportar la colección de Postman | 3 | 4 |
| T-06 | Pruebas automatizadas de backend y registro en `docs/pruebas.md` | 5 | 4 |
| T-07 | Adaptar las pantallas Vue al modelo nuevo y a los wireframes | 8 | 5 |
| T-08 | Diseño responsivo y feedback visual (carga, éxito, error) | 3 | 5 |
| T-09 | Documentar componentes, vistas y arquitectura en `docs/` | 2 | 5 |
| T-10 | Pruebas de componentes de frontend | 3 | 6 |
| T-11 | Integración continua con GitHub Actions | 3 | 6 |
| T-12 | Despliegue con Docker en el servidor del docente | 5 | 6 |
| T-13 | Manual de usuario, documentación final e informe | 5 | 6 |

## 2. Plan de los sprints 3 a 6

### Sprint 3 — Backend (I): 21/09 al 05/10

- **Objetivo:** tener el nuevo modelo de datos implementado y la autenticación por rol funcionando en la API.
- **Backlog:** T-01, T-02, T-03, T-04, HU-01 a HU-05.
- **Entregable:** migraciones y seeders del modelo propuesto; 13 endpoints funcionando (públicos, cuenta del feriante y gestión de feriantes).
- **Nota:** en este sprint se entrega también el recuperatorio del anteproyecto (30/09).

### Sprint 4 — Backend (II) y API: 05/10 al 19/10

- **Objetivo:** completar la operación principal (eventos, inscripciones y mesas) en la API, documentada y probada.
- **Backlog:** HU-06 a HU-12, T-05, T-06.
- **Entregable:** 28 de 37 endpoints funcionando (76 %), colección de Postman exportada y primeras pruebas automatizadas pasando.

### Sprint 5 — Frontend e integración: 19/10 al 02/11

- **Objetivo:** integrar las pantallas con la API y completar los endpoints restantes.
- **Backlog:** HU-13 a HU-18, T-07, T-08, T-09.
- **Entregable:** 37 endpoints funcionando; flujos completos del feriante y del coordinador operativos y adaptados al celular.

### Sprint 6 — Testing, CI y despliegue: 02/11 al 16/11

- **Objetivo:** asegurar la calidad y dejar la aplicación desplegada.
- **Backlog:** T-10, T-11, T-12, T-13.
- **Entregable:** suite de pruebas pasando en GitHub Actions, aplicación desplegada en el servidor del docente, documentación final, manual de usuario e informe.

## 3. Hitos

| Fecha | Hito |
|---|---|
| 30/09 | Entrega del recuperatorio del anteproyecto (tag `v0.2.0`) |
| 05/10 | Cierre del Sprint 3: modelo nuevo y autenticación por rol (tag `v0.3.0`) |
| 19/10 | Cierre del Sprint 4: backend operativo con al menos el 70 % de los endpoints (tag `v0.4.0`) |
| 27/10 al 30/10 | Entrega 2: presentación preliminar del software en vivo |
| 02/11 | Cierre del Sprint 5 (tag `v0.5.0`) |
| 13/11 | Congelamiento del código |
| 11/11 al 16/11 | Entrega 3: proyecto final completo (tag `v1.0.0`) |

## 4. Definición de terminado

Una historia o tarea se considera terminada cuando:

1. Cumple su criterio de aceptación.
2. Tiene validaciones en el servidor y, si corresponde, en el cliente.
3. Sus endpoints están probados en Postman y, desde el Sprint 4, con pruebas automatizadas que pasan.
4. Se integró a `develop` mediante un pull request que referencia su issue.
5. La documentación de `docs/` afectada está actualizada.

## 5. Riesgos

| Riesgo | Impacto | Mitigación |
|---|---|---|
| La migración del modelo (T-02) rompe funciones existentes | Alto | Hacerla al inicio del Sprint 3 en su propia rama y probar los flujos actuales antes del merge. |
| Falta de tiempo por superposición con el recuperatorio | Medio | Priorizar las historias de prioridad Alta; las de prioridad Baja pueden pasar al sprint siguiente. |
| Cambios de requerimientos pedidos por el Product Owner | Medio | Reflejarlos en `requerimientos.md` y en el backlog en cada Sprint Review. |
| Supuestos no confirmados con el coordinador (S-01 a S-07) | Medio | Confirmarlos antes de implementar las historias afectadas. |
| Problemas con el despliegue en Docker | Medio | Probar el `docker-compose.yml` en local durante el Sprint 5. |
