# Registro de pruebas — Feriaza

Registro de las pruebas automatizadas (PHPUnit), las pruebas manuales y de API, y los defectos encontrados y corregidos.

**Resumen al 20/11/2026:** 117 pruebas automatizadas en 19 archivos (117 pasan, 0 fallan; DEF-12 corregido) · 18 de 18 casos manuales OK · 25 de 25 pruebas de API OK · 12 defectos registrados (11 cerrados, 1 abierto).

> **Corrección (20/11/2026):** la versión del 27/10/2026 de este documento decía que "todavía no hay pruebas automatizadas con PHPUnit". Era incorrecto: la suite existía desde el commit `0359279` (26/10/2026, T-06), pero al reescribir el documento se perdió su sección y no se volvió a actualizar. La sección 2 la documenta tal como está hoy en el repositorio.

| Dato | Valor |
| --- | --- |
| Versión probada | v0.5.0 (`main`, PR #62) |
| Responsable | Luciano David Soler |
| Entorno | Windows · PHP 8.4 · Laravel 13.29 · MySQL 8 (puerto 3307) · Node + Vite · navegador Chrome |
| Datos | Base `feria_plaza` cargada con `php artisan migrate:fresh --seed`. Todos los datos son ficticios (D-05). |

## 1. Cómo se prueba

- **Pruebas automatizadas de backend (PHPUnit):** pruebas de feature que recorren las rutas web y de API con una base de datos vacía en cada caso. Se ejecutan con `php artisan test` (detalle en la sección 2).
- **Pruebas manuales web:** se recorre cada pantalla en el navegador siguiendo los pasos de la tabla de la sección 2 y se compara el resultado con el esperado.
- **Pruebas de API:** el script `postman/probar-api.ps1` ejecuta 25 pruebas contra `http://localhost:8000/api/v1`, con casos correctos y errores esperados (401, 403, 404, 409 y 422). Para ejecutarlo:

```powershell
php artisan migrate:fresh --seed
php artisan serve
.\postman\probar-api.ps1
```

También están disponibles la colección `postman/Feriaza-API.postman_collection.json` y el archivo `postman/feriaza-api.http` para la extensión REST Client.

## 2. Pruebas automatizadas (PHPUnit)

### 2.1 Entorno

| Dato | Valor |
| --- | --- |
| Herramienta | PHPUnit 12.5 (con `php artisan test`) |
| Framework | Laravel 13.29 · PHP 8.4 |
| Base de datos | SQLite en memoria (`phpunit.xml`); cada prueba arranca con la base vacía (`RefreshDatabase`) |
| Servicios externos | Simulados: correos con `Notification::fake()`, SMS de Twilio con `Http::fake()` y Google/Facebook con un doble de `Socialite`. No se envía nada real. |
| Datos | Seeders de categorías y lugares, y fábricas `UserFactory` (coordinador) y `FerianteFactory` (feriante) |

Para ejecutarlas:

```bash
php artisan test               # toda la suite
php artisan test --filter=ToleranciaYVetoTest   # un solo archivo
```

### 2.2 Suites

| Archivo | Qué prueba | Casos |
| --- | --- | :---: |
| `Feature/InscripcionWebTest.php` | Login y registro del feriante, categoría "Otro" y email repetido | 6 |
| `Feature/InscripcionEventoWebTest.php` | Inscripción a eventos: mesa por sección y por día, mesas deshabilitadas o de otra categoría, reglamento, revendedores, cancelación | 13 |
| `Feature/AutenticacionWebTest.php` | Login y logout web de cada rol y separación entre roles | 6 |
| `Feature/CuentaSocialTest.php` | Registro e ingreso con Google y Facebook | 6 |
| `Feature/RecuperarContrasenaTest.php` | Recuperar la contraseña por correo; no revela si el email existe | 5 |
| `Feature/RecuperarContrasenaSmsTest.php` | Recuperar la contraseña por SMS (Twilio): código, intentos fallidos y error del servicio | 6 |
| `Feature/MiCuentaWebTest.php` | Ver, modificar y dar de baja la cuenta del feriante (web) | 3 |
| `Feature/PanelCoordinadorTest.php` | Panel del coordinador: lista, alta, estados válidos, número de registro y eliminación | 7 |
| `Feature/AceptarInscripcionEventoTest.php` | Aceptar y rechazar inscripciones; al rechazar se libera la mesa | 3 |
| `Feature/PlanoCoordinadorTest.php` | Plano de la feria: mesas, secciones, zonas no disponibles, colores, eventos y ocupación por día | 14 |
| `Feature/ReconfirmacionYPagoTest.php` | Apertura de inscripción, reconfirmación a las 72 h, aviso por correo, anulación y cesión del lugar | 11 |
| `Feature/ToleranciaYVetoTest.php` | Presentismo dentro de la tolerancia, anulación y veto en eventos grandes y levantamiento del veto | 6 |
| `Feature/DesanulacionAutomaticaTest.php` | La cuenta anulada vuelve sola tras 2 eventos regulares (3 si la falta fue en un evento grande) | 3 |
| `Feature/PlanillaInscriptosTest.php` | Descarga del PDF de inscriptos del día; el visitante no puede descargarlo | 2 |
| `Feature/Api/AuthApiTest.php` | API: registro, login, Sanctum, permisos por rol, logout y límite de intentos | 10 |
| `Feature/Api/MiCuentaApiTest.php` | API: ver, modificar y dar de baja la cuenta del feriante | 4 |
| `Feature/Api/CoordinadorFeriantesApiTest.php` | API: búsqueda, alta, estados, anulación con motivo, 404, 409 e historial | 10 |
| `Feature/ExampleTest.php`, `Unit/ExampleTest.php` | Pruebas base de Laravel (la página de inicio responde) | 2 |
| **Total** | | **117** |

### 2.3 Última ejecución

**20/11/2026, en Linux: 117 pruebas, 117 aprobadas, 0 fallidas, 464 aserciones.**

Las 9 fallas que había antes tenían la misma causa (DEF-12): Inertia buscaba las páginas en `resources/js/pages` y el proyecto las tiene en `resources/js/Pages`. Se corrigió publicando `config/inertia.php` con la ruta correcta. La suite también corre en cada push y pull request con GitHub Actions (`.github/workflows/tests.yml`, D-12).

## 3. Casos de prueba manuales (web)

| ID | Módulo | Caso | Pasos | Resultado esperado | Resultado | Fecha |
| --- | --- | --- | --- | --- | --- | --- |
| CP-01 | Inscripción | Inscripción de un artesano con mesa | Entrar a `/`, completar los 3 pasos, elegir una mesa libre y enviar | Redirige a Gracias; la mesa pasa a ocupada | OK | 27/10/2026 |
| CP-02 | Inscripción | Inscripción de un revendedor (RN-03) | Elegir la categoría de revendedores y completar el formulario | No se muestra el selector de mesa; se guarda sin mesa | OK | 27/10/2026 |
| CP-03 | Inscripción | Campos obligatorios vacíos | Enviar el formulario sin nombre ni teléfono | Se muestran los errores debajo de cada campo y no se guarda | OK | 27/10/2026 |
| CP-04 | Inscripción | Categoría "Otro" | Elegir "Otro" sin completar qué vende | Error en el campo "¿Qué vende?" | OK | 27/10/2026 |
| CP-05 | Gracias | Aviso por WhatsApp | Tocar "Avisar por WhatsApp" en Gracias | Se abre WhatsApp con nombre, categoría, mesa y teléfono | OK | 27/10/2026 |
| CP-06 | Mi cuenta | Login de feriante | Entrar a `/mi-cuenta/login` con email y contraseña válidos | Muestra el resumen con estado, categoría y mesa | OK | 27/10/2026 |
| CP-07 | Mi cuenta | Login con contraseña incorrecta | Ingresar una contraseña errónea | Mensaje de error; no ingresa | OK | 27/10/2026 |
| CP-08 | Mi cuenta | Editar datos | Cambiar el teléfono y guardar | Aviso verde "Tus datos fueron actualizados." | OK | 27/10/2026 |
| CP-09 | Mi cuenta | Cambiar contraseña | Completar nueva contraseña y confirmación | Se guarda; el siguiente login funciona con la nueva | OK | 27/10/2026 |
| CP-10 | Mi cuenta | Confirmación distinta | Escribir dos contraseñas diferentes | Error de confirmación; no se guarda | OK | 27/10/2026 |
| CP-11 | Coordinador | Login del coordinador | Entrar a `/login` con las credenciales de prueba | Abre el panel con la lista de feriantes | OK | 27/10/2026 |
| CP-12 | Coordinador | Acceso sin sesión | Entrar a `/coordinador` en una ventana de incógnito (sin sesión iniciada) | Redirige a `/login` | OK | 27/10/2026 |
| CP-13 | Coordinador | Buscador | Escribir parte de un nombre o teléfono | La tabla muestra solo las coincidencias | OK | 27/10/2026 |
| CP-14 | Coordinador | Aprobar y rechazar | Tocar "Aprobar" en un feriante pendiente | Cambia el estado y se actualizan los contadores | OK | 27/10/2026 |
| CP-15 | Coordinador | Cargar feriante a mano | "Cargar feriante", completar y guardar | Aparece en la lista con el aviso de confirmación | OK | 27/10/2026 |
| CP-16 | Coordinador | Editar feriante | "Editar", cambiar un dato y guardar | Se ven los cambios en la lista | OK | 27/10/2026 |
| CP-17 | Coordinador | Eliminar feriante | "Eliminar" y confirmar | Desaparece de la lista y se libera su mesa | OK | 27/10/2026 |
| CP-18 | Coordinador | Vista en celular | Abrir el panel con el ancho de un celular | La tabla se muestra como fichas | OK | 27/10/2026 |

## 4. Pruebas de API (`postman/probar-api.ps1`)

Última ejecución: **27/10/2026 — 25 aprobadas, 0 fallidas.**

| # | Prueba | Código esperado | Resultado |
| --- | --- | --- | --- |
| 1 | Listar categorías | 200 | OK |
| 2 | Registro de feriante | 201 | OK |
| 3 | Registro con email duplicado | 422 | OK |
| 4 | Registro con contraseña corta | 422 | OK |
| 5 | Login feriante | 200 | OK |
| 6 | Login con contraseña incorrecta | 422 | OK |
| 7 | Login coordinador | 200 | OK |
| 8 | Ver mi cuenta | 200 | OK |
| 9 | Modificar mi cuenta | 200 | OK |
| 10 | Mi cuenta sin token | 401 | OK |
| 11 | Mi cuenta con token de coordinador | 403 | OK |
| 12 | Listar feriantes | 200 | OK |
| 13 | Buscar feriantes (`?q=prueba`) | 200 | OK |
| 14 | Listar feriantes con token de feriante | 403 | OK |
| 15 | Crear feriante | 201 | OK |
| 16 | Crear feriante sin datos | 422 | OK |
| 17 | Ver feriante | 200 | OK |
| 18 | Modificar feriante (aprobar) | 200 | OK |
| 19 | Ver feriante inexistente | 404 | OK |
| 20 | Historial de un feriante | 200 | OK |
| 21 | Eliminar feriante con evento abierto | 409 | OK |
| 22 | Eliminar feriante | 200 | OK |
| 23 | Logout feriante | 200 | OK |
| 24 | Token revocado | 401 | OK |
| 25 | Logout coordinador | 200 | OK |

## 5. Registro de defectos

| ID | Sprint | Defecto | Causa | Corrección | Estado |
| --- | --- | --- | --- | --- | --- |
| DEF-01 | 4 | Pantallas en blanco ("Cannot read properties of undefined") | El nombre de la página en `Inertia::render` no coincidía con el archivo `.vue` (mayúsculas y extensión `.Vue`) | Nombres de archivo unificados | Cerrado |
| DEF-02 | 4 | Error al iniciar sesión como feriante: guard no definido | Faltaba el guard `feriante` en `config/auth.php` | Guard y provider `feriantes` agregados | Cerrado |
| DEF-03 | 4 | Error de asignación masiva al reservar una mesa | `$fillable` mal escrito en `Puesto.php` | Propiedad corregida | Cerrado |
| DEF-04 | 5 | Faltaban modelos y migraciones de eventos e inscripciones en `develop` | Los archivos estaban solo en `main` | Recuperados desde `main`, migrados y sembrados | Cerrado |
| DEF-05 | 5 | Login de feriante devolvía 422 en el script de API | Se había cambiado la contraseña del usuario de prueba | Se volvió a correr `FerianteSeeder` | Cerrado |
| DEF-06 | 5 | Mi cuenta no guardaba los cambios | Reglas de validación mal escritas en `MiCuentaController` | Validación corregida y aviso de confirmación | Cerrado |
| DEF-07 | 5 | Error al aprobar un feriante | Valor `aprovado` mal escrito en la regla `in:` | Corregido a `aprobado` | Cerrado |
| DEF-08 | 5 | Error 500 al cargar un feriante desde el panel ("Undefined array key 1") | Regla `exists:puestos,id,` con una coma de más y `max100` sin dos puntos | Rama `fix/validacion-coordinador`, PR #61 | Cerrado |
| DEF-09 | 5 | La página de Gracias decía "Tu mesa quedó reservada" también a los revendedores | Texto fijo | Texto general, PR #61 | Cerrado |
| DEF-10 | 5 | Conflicto al integrar `develop` en `main` | `Feriante.php` distinto en las dos ramas, con un `use HasMany` duplicado | Merge resuelto y API reprobada (25 de 25), PR #62 | Cerrado |
| DEF-11 | 5 | Los mensajes de validación aparecen en inglés ("The nombre field is required", "…when categoria id is 4") | La aplicación no tiene traducción al español de los mensajes de validación | Archivo `lang/es/validation.php` y `APP_LOCALE=es` | Abierto |
| DEF-12 | 6 | 9 pruebas de PHPUnit fallan en Linux con "Inertia page component file [...] does not exist" | Inertia v3 busca las páginas en `resources/js/pages` (minúscula) y el proyecto las tiene en `resources/js/Pages`. Windows no distingue mayúsculas y por eso pasaban. | Publicado `config/inertia.php` con la ruta `resources/js/Pages` | Cerrado |

## 6. Limitaciones conocidas

- En el panel, los feriantes de los datos semilla muestran la mesa como "-": sus mesas están registradas en `inscripciones`. Se resuelve con el issue #48 (T-02b).
- No hay pruebas automatizadas de frontend: Vitest figura en [stack.md](stack.md) pero todavía no está instalado.
