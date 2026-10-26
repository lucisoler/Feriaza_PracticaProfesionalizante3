# Registro de pruebas

Este documento reúne las pruebas del sistema en dos partes:

1. **Pruebas automatizadas** de backend (PHPUnit), que se ejecutan con `php artisan test`.
2. **Pruebas manuales** de los flujos completos en el navegador y con la colección de Postman.

Cada caso indica el requerimiento (RF) o la regla de negocio (RN) que verifica, según [requerimientos.md](requerimientos.md).

## 1. Pruebas automatizadas

### 1.1 Entorno

| Dato | Valor |
|---|---|
| Herramienta | PHPUnit 12 (integrado en Laravel con `php artisan test`) |
| Framework | Laravel 13.29 |
| PHP | 8.4 |
| Base de datos | SQLite en memoria (`phpunit.xml`): cada prueba arranca con la base vacía (`RefreshDatabase`) |
| Datos | `CategoriaSeeder` y fábricas `UserFactory` (coordinador) y `FerianteFactory` (feriante) |

### 1.2 Suites

| Archivo | Qué prueba | Casos |
|---|---|:---:|
| `tests/Feature/InscripcionWebTest.php` | Formulario público de inscripción y reserva de mesa | 6 |
| `tests/Feature/AutenticacionWebTest.php` | Login y logout web de cada rol, y separación entre roles | 6 |
| `tests/Feature/MiCuentaWebTest.php` | Ver, modificar y dar de baja la cuenta del feriante (web) | 3 |
| `tests/Feature/PanelCoordinadorTest.php` | Panel del coordinador: feriantes, estados y mesas | 6 |
| `tests/Feature/Api/AuthApiTest.php` | Autenticación de la API con Sanctum y habilidades por rol | 10 |
| `tests/Feature/Api/MiCuentaApiTest.php` | Cuenta del feriante por la API | 4 |
| `tests/Feature/Api/CoordinadorFeriantesApiTest.php` | Gestión de feriantes e historial por la API | 7 |
| `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php` | Pruebas base de Laravel (la página de inicio responde) | 2 |
| **Total** | | **44** |

### 1.3 Casos

| ID | Caso | RF / RN | Resultado esperado |
|---|---|---|---|
| PA-01 | Inscripción con mesa libre | RF-01, RF-07 | Se crea el feriante, la mesa queda `reservado` a su nombre y la sesión queda iniciada |
| PA-02 | Inscripción con una mesa ya reservada | RN-09 | Error en `puesto_id` y el feriante **no** se guarda (transacción) |
| PA-03 | Categoría con mesa sin elegir puesto | RN-03 | Error "Elegí una mesa del plano." |
| PA-04 | Revendedor que envía un puesto | RN-03 | Se inscribe sin mesa; la mesa sigue `libre` |
| PA-05 | Categoría "Otro" sin describir el rubro | RF-02 | Error en `rubro_otro` |
| PA-06 | Email repetido | RF-01 | Error en `email` |
| PA-07 | Login del coordinador con datos correctos | RF-03 | Redirige a `/coordinador` con la sesión iniciada |
| PA-08 | Login del coordinador con contraseña incorrecta | RF-03 | Error en `email`; no se inicia sesión |
| PA-09 | Logout del coordinador | RF-03 | Cierra la sesión y redirige a `/login` |
| PA-10 | Login y logout del feriante | RF-03 | Entra a `/mi-cuenta`; al salir vuelve a `/` |
| PA-11 | Visitante en una página privada | RF-03 | `/coordinador` → `/login` y `/mi-cuenta` → `/mi-cuenta/login` |
| PA-12 | Feriante logueado entra al panel del coordinador | Roles | Redirige a `/login` (los guards están separados) |
| PA-13 | El feriante ve su cuenta | RF-04 | Página `Feriante/MiCuenta` con sus datos |
| PA-14 | El feriante modifica sus datos | RF-04 | Se guardan los cambios y aparece el mensaje de éxito |
| PA-15 | Baja del feriante (web) | RF-04 | Se borra la cuenta, se libera la mesa y se cierra la sesión |
| PA-16 | Panel del coordinador | RF-13 | Lista todos los feriantes |
| PA-17 | El coordinador crea un feriante con mesa | RF-13 | El feriante queda con la mesa asignada |
| PA-18 | El coordinador aprueba un feriante | RF-18 | El estado pasa a `aprobado` |
| PA-19 | Estado inválido | RF-18 | Error de validación en `estado` |
| PA-20 | El coordinador elimina un feriante | RF-13 | Se borra y se libera su mesa |
| PA-21 | El coordinador libera una mesa | RF-09 | La mesa queda `libre` y sin feriante |
| PA-22 | `GET /api/v1/categorias` sin token | RF-02 | 200 con las 5 categorías ordenadas por prioridad |
| PA-23 | Registro por la API | RF-01 | 201 con el feriante y el token; no devuelve la contraseña |
| PA-24 | Registro por la API sin datos | RF-01 | 422 en JSON con los errores de cada campo |
| PA-25 | Login del feriante por la API | RF-03 | 200 con token; con contraseña incorrecta, 422 |
| PA-26 | Login del coordinador por la API | RF-03 | 200 con token; no devuelve la contraseña |
| PA-27 | Rutas privadas sin token | RF-03 | 401 |
| PA-28 | Token de feriante en una ruta del coordinador | Roles | 403 |
| PA-29 | Token de coordinador en `/mi-cuenta` | Roles | 403 |
| PA-30 | Logout por la API | RF-03 | Se revoca el token |
| PA-31 | Más de 6 intentos de login por minuto | RNF (seguridad) | 429 |
| PA-32 | `GET /api/v1/mi-cuenta` | RF-04 | 200 con los datos del feriante; sin contraseña |
| PA-33 | `PATCH /api/v1/mi-cuenta` con un solo campo | RF-04 | Cambia ese campo y el resto queda igual |
| PA-34 | Cambiar el email por el de otro feriante | RF-04 | 422 en `email` |
| PA-35 | `DELETE /api/v1/mi-cuenta` | RF-04 | 200; se borra la cuenta y se libera la mesa |
| PA-36 | Búsqueda de feriantes con `?q=` | RF-13 | Devuelve solo los que coinciden |
| PA-37 | Alta de feriante por la API | RF-13 | 201 con el feriante creado |
| PA-38 | Cambio de estado por la API | RF-18 | 200 con el estado nuevo |
| PA-39 | Feriante inexistente | RF-13 | 404 |
| PA-40 | Eliminar un feriante con inscripciones en eventos abiertos | RF-13 | 409; el feriante no se borra |
| PA-41 | Eliminar un feriante sin eventos abiertos | RF-13 | 200; se borra |
| PA-42 | Historial de un feriante | RF-14 | Resumen con participaciones, presentes, ausentes y sanciones |

### 1.4 Ejecuciones

| Fecha | Rama | Pruebas | Resultado | Observaciones |
|---|---|:---:|---|---|
| 26/10/2026 | `feature/pruebas-backend` | 44 | 44 pasan (150 aserciones) | Primera ejecución de la suite completa |

### 1.5 Defectos encontrados

| ID | Defecto | Cómo se detectó | Corrección |
|---|---|---|---|
| D-01 | "Cerrar sesión" del coordinador daba error 500: la ruta apuntaba a `CoordinadorController@logout`, que no existía | Revisión de código, confirmado con PA-09 | Se agregó el método `logout` (rama `fix/logout-y-puestos`) |
| D-02 | "Liberar mesa" daba error 500: faltaban los `use` de `Puesto`, `Inertia` y `RedirectResponse` en `PuestoController` | Revisión de código, confirmado con PA-21 | Se agregaron los imports y se corrigió el orden por `numero` |
| D-03 | El panel recibía `whatsapp` vacío: leía `config/feria.php`, que no existía; además se usaba `env()` en un controlador, que falla con la configuración en caché | Revisión de código | Se creó `config/feria.php` y se documentó `WHATSAPP_COORDINADOR` en `.env.example` |

## 2. Pruebas manuales

Se ejecutan con los usuarios de prueba de [instalacion.md](instalacion.md) (sección 3), después de `php artisan migrate:fresh --seed`, con `php artisan serve` y `npm run dev` en marcha. Las pruebas de la API usan la colección `postman/Feriaza-API.postman_collection.json` o el script `postman/probar-api.ps1`.

Completar **Resultado** (OK / Falla), **Fecha** y **Observaciones** al ejecutar cada caso.

### 2.1 Feriante (web, desde el celular)

| ID | Caso | Pasos | Resultado esperado | Resultado | Fecha | Observaciones |
|---|---|---|---|---|---|---|
| PM-01 | Inscripción con mesa | Inicio → completar los 3 pasos eligiendo Artesanos y una mesa libre | Pantalla de Gracias con el botón de WhatsApp; la mesa aparece ocupada en el plano | | | |
| PM-02 | Inscripción de revendedor | Igual que PM-01 eligiendo Revendedores | No se muestra el plano; inscripción sin mesa | | | |
| PM-03 | Mesa tomada por otra persona | Abrir el formulario en dos navegadores y reservar la misma mesa en ambos | El segundo ve "Esa mesa ya fue reservada. Elegí otra." | | | |
| PM-04 | Aviso por WhatsApp | En Gracias, tocar el botón de WhatsApp | Se abre WhatsApp con el mensaje al coordinador (nombre, categoría, mesa y teléfono) | | | |
| PM-05 | Login y Mi cuenta | `/mi-cuenta/login` con feriante@feriaza.test | Muestra los datos y la mesa del feriante | | | |
| PM-06 | Editar datos | En Mi cuenta, cambiar el teléfono y guardar | Mensaje de éxito; el cambio persiste al recargar | | | |
| PM-07 | Baja | En Mi cuenta, cancelar la inscripción y confirmar | Vuelve al inicio; la mesa queda libre en el plano | | | |
| PM-08 | Vista en celular | Repetir PM-01 con las herramientas de desarrollo en 375 px de ancho | Sin desplazamiento horizontal; el plano y los botones se pueden usar | | | |

### 2.2 Coordinador (web)

| ID | Caso | Pasos | Resultado esperado | Resultado | Fecha | Observaciones |
|---|---|---|---|---|---|---|
| PM-09 | Login | `/login` con coordinador@feriaza.test | Entra al panel con el listado de feriantes | | | |
| PM-10 | Login incorrecto | `/login` con una contraseña errónea | Mensaje "Credenciales Incorrectas" | | | |
| PM-11 | Crear feriante | Panel → nuevo feriante con una mesa | Aparece en el listado con la mesa asignada | | | |
| PM-12 | Editar y cambiar de mesa | Editar el feriante de PM-11 y elegir otra mesa | La mesa anterior queda libre y la nueva reservada | | | |
| PM-13 | Aprobar o rechazar | Cambiar el estado de un feriante pendiente | El estado se actualiza sin recargar la página | | | |
| PM-14 | Eliminar feriante | Eliminar un feriante con mesa y confirmar | Desaparece del listado y su mesa queda libre | | | |
| PM-15 | Cerrar sesión | "Cerrar sesión" en el panel | Vuelve a `/login`; `/coordinador` ya no abre sin iniciar sesión | | | |

### 2.3 API (Postman)

| ID | Caso | Pasos | Resultado esperado | Resultado | Fecha | Observaciones |
|---|---|---|---|---|---|---|
| PM-16 | Flujo del feriante | Registro → login → ver, editar y borrar Mi cuenta → logout | 201, 200, 200, 200, 200 y 200 | | | |
| PM-17 | Flujo del coordinador | Login → listar, crear, ver, editar, historial y borrar feriante | 200, 201, 200, 200, 200 y 200 | | | |
| PM-18 | Permisos por rol | Usar el token del feriante en `/coordinador/feriantes` | 403 | | | |
| PM-19 | Límite de intentos | Enviar 7 logins seguidos con una contraseña incorrecta | El séptimo responde 429 | | | |
