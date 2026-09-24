# Roles y permisos

## 1. Roles

| Rol | Descripción | Autenticación |
|---|---|---|
| Visitante | Persona sin sesión iniciada. Puede registrarse como feriante e iniciar sesión. | No requiere |
| Feriante | Vende en la feria. Gestiona su cuenta, se inscribe a eventos, reserva mesa, confirma asistencia y envía consultas. | Guard `feriante` |
| Coordinador | Organiza la feria. Gestiona eventos, feriantes, mesas, asistencia, sanciones y consultas. | Guard del coordinador |

Feriante y coordinador usan accesos separados: un usuario autenticado con un rol no puede acceder a las pantallas ni a los endpoints del otro. La autorización se verifica siempre en el servidor (RNF-02).

## 2. Matriz de permisos

| Acción | Visitante | Feriante | Coordinador | RF |
|---|:---:|:---:|:---:|---|
| Registrarse como feriante | ✅ | — | — | RF-01 |
| Iniciar y cerrar sesión | ✅ | ✅ | ✅ | RF-03 |
| Ver y editar sus propios datos y rubros | — | ✅ | — | RF-02, RF-04 |
| Darse de baja | — | ✅ | — | RF-04 |
| Ver eventos abiertos | — | ✅ | ✅ | RF-07 |
| Crear, modificar y cancelar eventos | — | — | ✅ | RF-05 |
| Aceptar el reglamento e inscribirse a un evento | — | ✅ | — | RF-06, RF-07 |
| Reservar número de mesa | — | ✅ (excepto revendedores) | — | RF-07 |
| Confirmar o cancelar su asistencia | — | ✅ | — | RF-08 |
| Asignar y reasignar mesas | — | — | ✅ | RF-09 |
| Ver listado de inscriptos de un evento | — | — | ✅ | RF-10 |
| Aprobar o rechazar inscripciones | — | — | ✅ | RF-18 |
| Ver el estado de su propia inscripción | — | ✅ | — | RF-18 |
| Registrar presentes | — | — | ✅ | RF-11 |
| Registrar sanciones | — | — | ✅ | RF-12 |
| Gestionar feriantes (alta, baja, modificación) | — | — | ✅ | RF-13 |
| Ver historial de un feriante | — | Solo el propio | ✅ | RF-14 |
| Hablar con el coordinador por WhatsApp | — | ✅ | — | RF-16 |
| Enviar consultas | — | ✅ | — | RF-17 |
| Responder consultas | — | — | ✅ | RF-17 |

## 3. Reglas de acceso

- Un feriante solo puede leer y modificar sus propios datos, inscripciones y consultas.
- Un feriante que intenta acceder a una ruta del coordinador es redirigido a su inicio (en la interfaz) o recibe `403 Forbidden` (en la API).
- Un usuario sin sesión que intenta acceder a una ruta protegida es redirigido al inicio de sesión correspondiente (en la interfaz) o recibe `401 Unauthorized` (en la API).
