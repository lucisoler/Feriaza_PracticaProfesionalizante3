# Diseño de la API

API REST propia del sistema, definida a partir de [requerimientos.md](requerimientos.md) y [modelo-de-datos.md](modelo-de-datos.md). Convive con las pantallas de Inertia según la decisión D-02 de [decisiones.md](decisiones.md): la lógica de negocio vive en clases de servicio que usan tanto los controladores web como los de la API.

## 1. Convenciones generales

| Aspecto | Definición |
|---|---|
| URL base | `/api/v1` |
| Formato | JSON en solicitudes y respuestas (`Content-Type: application/json`, `Accept: application/json`) |
| Autenticación | Token Bearer emitido por Laravel Sanctum: `Authorization: Bearer <token>` |
| Nombres | Recursos en plural y en español, en minúsculas (`/eventos`, `/inscripciones`) |
| Fechas | Formato ISO 8601 (`2026-10-10`, `2026-10-10T17:00:00-03:00`) |
| Paginación | Listados con `?page=N`; 20 elementos por página |
| Versionado | Prefijo `/v1` para poder cambiar la API sin romper clientes existentes |

## 2. Autenticación y autorización

Feriantes y coordinadores se autentican por separado. Tanto el modelo `Feriante` como `User` usan el trait `HasApiTokens` de Sanctum. Al iniciar sesión, cada token se emite con una habilidad (*ability*) que identifica el rol:

| Rol | Login | Habilidad del token | Middleware de las rutas |
|---|---|---|---|
| Feriante | `POST /api/v1/feriantes/login` | `feriante` | `auth:sanctum` + `abilities:feriante` |
| Coordinador | `POST /api/v1/coordinador/login` | `coordinador` | `auth:sanctum` + `abilities:coordinador` |

- Sin token o con token inválido, la API responde **401**.
- Con un token válido pero de otro rol, responde **403** (RNF-02).
- El login tiene un límite de 5 intentos por minuto por correo e IP (RNF-03); al superarlo responde **429**.
- `POST /api/v1/logout` revoca el token actual.

## 3. Códigos de estado

| Código | Uso |
|---|---|
| 200 OK | Lectura o modificación correcta |
| 201 Created | Recurso creado |
| 204 No Content | Eliminación correcta |
| 401 Unauthorized | Falta el token o es inválido |
| 403 Forbidden | El token no tiene permiso para esa acción |
| 404 Not Found | El recurso no existe o no pertenece al usuario |
| 409 Conflict | Conflicto de negocio (puesto ya reservado, inscripción duplicada, evento con inscriptos) |
| 422 Unprocessable Entity | Error de validación de datos |
| 429 Too Many Requests | Se superó el límite de intentos |

Formato de error de validación (el estándar de Laravel):

```json
{
  "message": "El campo email ya está registrado.",
  "errors": {
    "email": ["El campo email ya está registrado."]
  }
}
```

Formato de error de negocio:

```json
{
  "message": "Esa mesa ya fue reservada, elegí otra."
}
```

## 4. Endpoints

### 4.1 Públicos

| Método | Ruta | Descripción | Auth | RF |
|---|---|---|---|---|
| POST | `/feriantes/registro` | Registrar un feriante; devuelve el token | — | RF-01, RF-02 |
| POST | `/feriantes/login` | Iniciar sesión como feriante | — | RF-03 |
| POST | `/coordinador/login` | Iniciar sesión como coordinador | — | RF-03 |
| GET | `/categorias` | Listar categorías para el formulario de registro | — | RF-02 |

### 4.2 Feriante

Todas requieren token con habilidad `feriante`.

| Método | Ruta | Descripción | RF |
|---|---|---|---|
| POST | `/logout` | Cerrar sesión, revocando el token (lo usa también el coordinador) | RF-03 |
| GET | `/mi-cuenta` | Ver los datos propios | RF-04 |
| PATCH | `/mi-cuenta` | Modificar los datos propios | RF-02, RF-04 |
| DELETE | `/mi-cuenta` | Darse de baja | RF-04 |
| GET | `/eventos` | Listar eventos abiertos | RF-07 |
| GET | `/eventos/{evento}/puestos` | Ver los puestos del evento con su disponibilidad | RF-07 |
| POST | `/eventos/{evento}/inscripciones` | Aceptar el reglamento e inscribirse, con o sin puesto | RF-06, RF-07, RF-15 |
| GET | `/mis-inscripciones` | Ver las inscripciones propias con su estado | RF-08, RF-18 |
| PATCH | `/mis-inscripciones/{inscripcion}/confirmacion` | Confirmar asistencia o avisar que no asiste | RF-08 |
| GET | `/mis-consultas` | Ver las consultas propias y sus respuestas | RF-17 |
| POST | `/mis-consultas` | Enviar una consulta | RF-17 |
| GET | `/contacto-coordinador` | Obtener el enlace de WhatsApp del coordinador | RF-16 |

### 4.3 Coordinador

Todas requieren token con habilidad `coordinador`. Para cerrar sesión, el coordinador usa el mismo `POST /logout` del feriante.

| Método | Ruta | Descripción | RF |
|---|---|---|---|
| GET | `/coordinador/eventos` | Listar eventos (filtro `?estado=`) | RF-05 |
| POST | `/coordinador/eventos` | Crear un evento | RF-05 |
| GET | `/coordinador/eventos/{evento}` | Ver un evento | RF-05 |
| PATCH | `/coordinador/eventos/{evento}` | Modificar datos o estado de un evento | RF-05 |
| DELETE | `/coordinador/eventos/{evento}` | Eliminar un evento sin inscriptos (409 si tiene) | RF-05 |
| GET | `/coordinador/eventos/{evento}/inscripciones` | Listado de inscriptos (filtros `?categoria=`, `?estado=`, `?asistencia=`; orden `?orden=numero\|prioridad`) | RF-10 |
| GET | `/coordinador/eventos/{evento}/lista-espera` | Inscriptos sin puesto, ordenados por prioridad | RF-09 |
| PATCH | `/coordinador/inscripciones/{inscripcion}/estado` | Aprobar o rechazar una inscripción | RF-18 |
| PATCH | `/coordinador/inscripciones/{inscripcion}/puesto` | Asignar o cambiar el puesto | RF-09 |
| PATCH | `/coordinador/inscripciones/{inscripcion}/asistencia` | Marcar presente | RF-11 |
| POST | `/coordinador/inscripciones/{inscripcion}/sancion` | Registrar una sanción | RF-12 |
| GET | `/coordinador/feriantes` | Listar feriantes (búsqueda `?q=`) | RF-13 |
| POST | `/coordinador/feriantes` | Crear un feriante | RF-13 |
| GET | `/coordinador/feriantes/{feriante}` | Ver un feriante | RF-13 |
| PATCH | `/coordinador/feriantes/{feriante}` | Modificar un feriante | RF-13 |
| DELETE | `/coordinador/feriantes/{feriante}` | Eliminar un feriante (409 si tiene inscripciones en eventos abiertos) | RF-13 |
| GET | `/coordinador/feriantes/{feriante}/historial` | Participaciones, asistencia y sanciones | RF-14 |
| GET | `/coordinador/puestos` | Listar los puestos del plano | RF-09 |
| PATCH | `/coordinador/puestos/{puesto}` | Habilitar o deshabilitar un puesto | RF-09 |
| GET | `/coordinador/consultas` | Listar consultas (filtro `?estado=`) | RF-17 |
| PATCH | `/coordinador/consultas/{consulta}/respuesta` | Responder una consulta | RF-17 |

**Procesos automáticos (sin endpoint):**

- **Notificación al coordinador (RF-15):** se genera al crear una inscripción, dentro del mismo servicio.
- **Paso a ausente (RF-11):** un comando programado de Laravel (`schedule`) marca como `ausente` las inscripciones aprobadas sin presente una hora después del inicio del evento.

## 5. Ejemplos

### 5.1 Login de feriante

`POST /api/v1/feriantes/login`

```json
{
  "email": "feriante@ejemplo.com",
  "password": "secreto123"
}
```

Respuesta **200**:

```json
{
  "token": "1|a8Fk2...",
  "feriante": {
    "id": 5,
    "nombre": "Ana",
    "apellido": "Gómez",
    "categoria": { "id": 1, "nombre": "Artesanos", "prioridad": 1 }
  }
}
```

Con credenciales incorrectas, **422** con el mensaje "Correo o contraseña incorrectos".

### 5.2 Inscripción a un evento con reserva de puesto

`POST /api/v1/eventos/3/inscripciones` (token de feriante)

```json
{
  "puesto_id": 12,
  "acepta_reglamento": true
}
```

Respuesta **201**:

```json
{
  "id": 41,
  "evento": { "id": 3, "nombre": "Encuentro de danzas" },
  "puesto": { "id": 12, "numero": 12 },
  "estado": "pendiente",
  "confirmacion": "sin_confirmar",
  "reglamento_aceptado_at": "2026-10-01T10:15:00-03:00"
}
```

Errores posibles:

| Caso | Código |
|---|---|
| `acepta_reglamento` falso o ausente | 422 |
| El evento no está abierto | 409 |
| El puesto ya está reservado en ese evento | 409 |
| El feriante ya está inscripto en ese evento | 409 |
| Un revendedor envía `puesto_id` | 422 |

### 5.3 Aprobar una inscripción

`PATCH /api/v1/coordinador/inscripciones/41/estado` (token de coordinador)

```json
{
  "estado": "aprobada"
}
```

Respuesta **200** con la inscripción actualizada. Si el estado es `rechazada`, el puesto queda libre para ese evento.

### 5.4 Marcar presente

`PATCH /api/v1/coordinador/inscripciones/41/asistencia` (token de coordinador)

```json
{
  "asistencia": "presente"
}
```

Respuesta **200**. Si la hora actual supera el inicio del evento más la tolerancia de 1 hora (RN-07), responde **409** con el mensaje "La tolerancia para marcar presente ya venció".

## 6. Documentación y prueba

- Cada endpoint implementado se prueba con **Postman**. La colección se exporta a `docs/postman/feriaza.postman_collection.json`.
- Las pruebas automatizadas de los endpoints se registran en `docs/pruebas.md` (Sprint 4).
- Resumen: **4 endpoints públicos, 12 de feriante (incluido el logout compartido) y 21 de coordinador: 37 en total.** La meta del Sprint 4 es tener al menos el 70 % (26 endpoints) funcionando y probados.
