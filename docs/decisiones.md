# Registro de decisiones de diseño

Cada decisión registra el contexto, lo que se decidió y sus consecuencias. Estado: **Propuesta**, **Aceptada** o **Reemplazada**.

## D-01 — Guards separados para feriante y coordinador

- **Estado:** Aceptada
- **Contexto:** Feriantes y coordinador tienen permisos muy distintos y pantallas diferentes.
- **Decisión:** Usar un guard propio `feriante` (modelo `Feriante` que extiende `Authenticatable`) separado del guard del coordinador, con redirección según el guard en el middleware `Authenticate`.
- **Consecuencias:** Aislamiento claro entre roles. Requiere cuidar la lógica de redirección del middleware y probar el acceso cruzado entre roles.

## D-02 — API REST propia junto con Inertia

- **Estado:** Propuesta
- **Contexto:** El dispositivo exige un backend con API propia documentada. Con Inertia, las pantallas usan rutas web, que no constituyen una API.
- **Decisión:** Exponer los recursos principales como endpoints REST en `routes/api.php`, autenticados con Laravel Sanctum, y mantener Inertia para la navegación de las pantallas.
- **Consecuencias:** Se cumple el requisito de API propia y se pueden probar los endpoints con un cliente REST y con pruebas automatizadas. Implica una capa de servicios compartida para no duplicar la lógica entre controladores web y de API.

## D-03 — Prioridad como atributo de la categoría

- **Estado:** Aceptada
- **Contexto:** La prioridad de asignación depende de la categoría del feriante (RN-02) y podría cambiar en el futuro.
- **Decisión:** Guardar las categorías en una tabla propia con un campo de prioridad y un indicador de si reciben mesa, en lugar de fijarlos en el código.
- **Consecuencias:** El coordinador puede ajustar prioridades sin cambiar el código. La lógica de asignación consulta la tabla de categorías.

## D-04 — Estrategia de ramas

- **Estado:** Aceptada
- **Contexto:** El dispositivo pide ramas main / develop / feature, pull requests y líneas base por milestone.
- **Decisión:** `main` solo recibe versiones cerradas al final de cada sprint; `develop` integra el trabajo del sprint; cada issue se trabaja en una rama `feature/*` que entra a `develop` por pull request. Cada sprint cerrado se marca con un tag de versión semántica (`v0.2.0`, `v0.3.0`, …) y se gestiona con un milestone.
- **Consecuencias:** Historial ordenado y trazable entre issues, commits y pull requests.

## D-05 — Anonimización de datos reales

- **Estado:** Aceptada
- **Contexto:** El material del relevamiento (cuaderno de registro) contiene nombres y firmas de feriantes reales.
- **Decisión:** No incluir datos personales reales en el repositorio. Las imágenes se publican anonimizadas y los seeders usan datos ficticios.
- **Consecuencias:** Se protege la privacidad de los feriantes sin perder la evidencia del proceso actual.

## D-06 — Plano fijo de puestos para todos los eventos

- **Estado:** Aceptada
- **Contexto:** El sistema ya tiene un mapa SVG de la plaza con puestos numerados. El relevamiento muestra que la inscripción se hace por evento.
- **Decisión:** Los puestos representan las mesas físicas del plano y son los mismos para todos los eventos. La ocupación de un puesto en un evento se deduce de las inscripciones, no se guarda en el puesto.
- **Consecuencias:** Se reutiliza el mapa SVG existente. Se evita que el estado del puesto quede desincronizado. Un puesto puede deshabilitarse sin borrarlo.

## D-07 — La inscripción como entidad por evento

- **Estado:** Aceptada
- **Contexto:** En el modelo actual, el estado de aprobación, la confirmación de asistencia y el puesto están en el feriante, por lo que un feriante solo puede participar de un evento.
- **Decisión:** Crear la tabla `inscripciones`, que relaciona feriante, evento y puesto, y guarda el estado de aprobación, la confirmación, la asistencia y la aceptación del reglamento.
- **Consecuencias:** Un feriante puede participar en muchos eventos con historial completo. Requiere migrar los datos actuales en el Sprint 3 (ver [modelo-de-datos.md](modelo-de-datos.md), sección 5).

## D-08 — MySQL 8 como base de datos de desarrollo

- **Estado:** Aceptada
- **Contexto:** En la computadora de desarrollo conviven el MySQL de XAMPP (MariaDB) y un servidor MySQL 8 instalado como servicio de Windows (`MySQL80`, puerto 3307). El proyecto estaba configurado para el puerto 3307, pero la documentación inicial indicaba MariaDB de XAMPP. Además, el MySQL de XAMPP no inicia por un conflicto con el otro servidor.
- **Decisión:** Usar MySQL 8.0 (servicio `MySQL80`, puerto 3307, base `feria_plaza`) como base de datos de desarrollo y la imagen `mysql:8.0` para el despliegue en Docker.
- **Consecuencias:** Un único motor de base de datos en desarrollo y en producción, lo que evita diferencias de comportamiento. La guía de instalación y el stack se actualizaron para reflejar este entorno. El MySQL de XAMPP no se usa en el proyecto.
