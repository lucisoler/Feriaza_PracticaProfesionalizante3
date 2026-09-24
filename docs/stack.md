# Stack tecnológico

## 1. Backend

| Componente | Tecnología y versión | Justificación |
|---|---|---|
| Lenguaje | PHP 8.4.22 | Requerido por Laravel 11. Lenguaje trabajado a lo largo de la carrera. |
| Framework | Laravel 11 | Stack de referencia de Programación III. Ofrece ORM (Eloquent), migraciones, seeders, validación, autenticación con guards múltiples y pruebas integradas, lo que cubre los requisitos del proyecto sin librerías adicionales. |
| Autenticación de la API | Laravel Sanctum | Autenticación por token para la API REST propia, integrada con Laravel y con soporte para distintos roles. |
| Base de datos | MySQL 8.0.41 (servicio de Windows `MySQL80`) | Base relacional adecuada para un dominio con relaciones claras (feriantes, eventos, puestos, inscripciones), compatible con Eloquent. Para el despliegue se usa la imagen oficial `mysql:8.0` en Docker, con la misma versión mayor que en desarrollo. |
| Gestión de dependencias | Composer 2.8.9 | Estándar de PHP. |

## 2. Frontend

| Componente | Tecnología y versión | Justificación |
|---|---|---|
| Framework | Vue 3 | Stack de referencia de la carrera, ya utilizado en la Parte A. Componentes reutilizables y reactividad sencilla. |
| Integración | Inertia.js | Permite construir las pantallas con Vue usando el ruteo de Laravel, sin mantener dos proyectos separados. |
| Estilos | Tailwind CSS | Clases utilitarias que permiten construir un diseño responsivo y consistente directamente en los componentes, sin mantener hojas de estilo separadas. Facilita cumplir RNF-11 y RNF-12 (tamaño táctil y pantallas desde 360 px). |
| Empaquetado | Vite | Incluido por defecto en Laravel 11; recarga rápida en desarrollo. |
| Mapa de puestos | SVG (componente `MapaPuestos.vue`) | Permite dibujar el plano de la plaza y marcar mesas libres u ocupadas de forma escalable en cualquier pantalla. |

## 3. Herramientas

| Área | Herramienta | Uso |
|---|---|---|
| Control de versiones | Git + GitHub | Ramas `main` / `develop` / `feature/*`, pull requests y tags por sprint. |
| Gestión del proyecto | GitHub Issues, Milestones y Projects | Backlog, un milestone por sprint y tablero Kanban. |
| Comunicación | GitHub Discussions | Canal oficial con el docente. |
| Pruebas de backend | PHPUnit (incluido en Laravel) | Pruebas unitarias y de integración de endpoints, validaciones y autorización. |
| Pruebas de frontend | Vitest + Vue Test Utils | Pruebas de componentes. |
| Cliente REST | Postman | Prueba manual de los endpoints. La colección se exporta al repositorio como parte de la documentación de la API. |
| Integración continua | GitHub Actions | Ejecución de la suite en cada push y pull request. |
| Despliegue | Docker + docker-compose | Contenedores en el servidor del docente. |
| Notificaciones | WhatsApp (variable `WHATSAPP_COORDINADOR`) | Aviso al coordinador de nuevas inscripciones. |
| Editor | Visual Studio Code + Intelephense | Autocompletado y análisis de código PHP. |

## 4. Requisitos del entorno de desarrollo

- PHP 8.4 (mínimo 8.2, requerido por Laravel 11) con extensiones requeridas por Laravel (`pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`).
- Composer 2.8 o superior.
- Node.js 22 (LTS) y npm.
- MySQL Server 8.0 (en desarrollo se usa el puerto 3307) y, opcionalmente, MySQL Workbench para administrar la base.
- Git.
- Postman.
- Docker Desktop (para probar el despliegue).
