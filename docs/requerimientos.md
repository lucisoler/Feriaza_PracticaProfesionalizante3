# Requerimientos del sistema

Los roles de usuario y sus permisos se detallan en [roles-y-permisos.md](roles-y-permisos.md). Las fuentes (E-01, D-01, D-02), los objetivos (OE-n) y los supuestos (S-n) están en [fundamentacion.md](fundamentacion.md).

## 1. Reglas de negocio

| ID | Regla | Fuente |
|---|---|---|
| RN-01 | Las categorías de feriantes son: Artesanos, Manualidades, Masas y Revendedores, más "Otro" para rubros no contemplados (S-04). | E-01 |
| RN-02 | Prioridad de asignación: Artesanos y Manualidades (alta), Masas (media), Revendedores (baja). | E-01 |
| RN-03 | Los revendedores no reciben mesa ni número de mesa; traen su propia mesa. | E-01 |
| RN-04 | Las mesas miden 1,5 m (ver S-01). | E-01 |
| RN-05 | Está prohibida la venta de ropa, excepto ropa interior y medias. | E-01 |
| RN-06 | Los revendedores no pueden vender electrodomésticos grandes (heladeras, hornos eléctricos). | E-01 |
| RN-07 | Hay una tolerancia de 1 hora desde el inicio del evento para presentarse. | E-01 |
| RN-08 | El feriante inscripto que no se presenta dentro de la tolerancia es sancionado. | E-01 |
| RN-09 | Un número de mesa no puede asignarse a más de un feriante en el mismo evento. | E-01, D-02 |
| RN-10 | Ningún feriante habilitado debe quedar sin mesa: ante falta de lugar, se asigna según prioridad. | E-01 |

## 2. Requerimientos funcionales

**Prioridad:** Alta (imprescindible), Media (importante), Baja (deseable).
**Estado:** Implementado, Parcial o Pendiente, según el código actual del repositorio.

### Módulo 1 — Cuenta y acceso

#### RF-01 — Registro de feriante

| Campo | Detalle |
|---|---|
| Actor | Visitante (feriante sin cuenta) |
| Disparador / precondición | El visitante elige "Registrarme". No existe una cuenta con el mismo correo. |
| Descripción | El feriante crea su cuenta mediante un formulario en 3 pasos: datos personales, datos del emprendimiento y credenciales de acceso. |
| Resultado esperado | Se crea la cuenta con rol Feriante, se inicia sesión automáticamente y se muestra "Mi cuenta". |
| Reglas de negocio | RN-01. |
| Validaciones | Nombre y apellido: obligatorios, máximo 100 caracteres. Teléfono: obligatorio, máximo 20 caracteres. Correo: obligatorio, formato válido, único. Contraseña: obligatoria, mínimo 8 caracteres, con confirmación. Nombre del emprendimiento: opcional, máximo 150 caracteres. Instagram, Facebook y TikTok: opcionales, máximo 100 caracteres cada uno. |
| Criterio de aceptación | Dado un visitante, cuando completa los 3 pasos con datos válidos, entonces queda registrado y ve "Mi cuenta". Si un campo es inválido, se muestra el error junto al campo y no avanza de paso. Si el correo ya existe, se informa "Este correo ya está registrado". |
| Prioridad | Alta |
| Trazabilidad | E-01 · OE-1, OE-6 |
| Estado | Implementado |

#### RF-02 — Selección de rubros

| Campo | Detalle |
|---|---|
| Actor | Feriante |
| Disparador / precondición | El feriante está en el paso de rubros del registro o edita sus datos. |
| Descripción | El feriante elige su categoría. Si su rubro no está en la lista, elige "Otro" y lo describe (S-04). |
| Resultado esperado | La categoría queda asociada al feriante y determina su prioridad y si recibe mesa. |
| Reglas de negocio | RN-01, RN-02, RN-03. |
| Validaciones | Categoría obligatoria y existente. Si la categoría es "Otro", la descripción es obligatoria (máx. 100 caracteres). |
| Criterio de aceptación | Dado un feriante que elige Artesanos, cuando guarda, entonces su prioridad es Alta. Si elige "Otro" y deja la descripción vacía, se muestra "Especificá tu rubro" y no se guarda. |
| Prioridad | Alta |
| Trazabilidad | E-01 · OE-1, OE-2 |
| Estado | Parcial |

#### RF-03 — Inicio y cierre de sesión

| Campo | Detalle |
|---|---|
| Actor | Feriante, Coordinador |
| Disparador / precondición | El usuario tiene una cuenta registrada y elige "Ingresar". |
| Descripción | El usuario ingresa con correo y contraseña, y puede cerrar sesión. Feriantes y coordinador tienen accesos separados. |
| Resultado esperado | El feriante accede a "Mi cuenta"; el coordinador, a su panel. Al cerrar sesión, vuelve a la pantalla de inicio. |
| Reglas de negocio | Cada rol solo accede a sus propias pantallas y endpoints. |
| Validaciones | Correo y contraseña obligatorios. Máximo 5 intentos fallidos por minuto (RNF-03). |
| Criterio de aceptación | Con credenciales correctas, el usuario accede a su pantalla principal. Con credenciales incorrectas, ve "Correo o contraseña incorrectos" sin indicar cuál falló. Un feriante que intenta abrir una pantalla del coordinador es redirigido a su inicio. |
| Prioridad | Alta |
| Trazabilidad | OE-1 · `FerianteAuthController`, `Feriante/Login.vue`, guard `feriante` |
| Estado | Implementado |

#### RF-04 — Gestión de la cuenta propia

| Campo | Detalle |
|---|---|
| Actor | Feriante |
| Disparador / precondición | El feriante inició sesión y abre "Mi cuenta". |
| Descripción | El feriante consulta, modifica o da de baja sus propios datos. |
| Resultado esperado | Los cambios se guardan; ante una baja confirmada, la cuenta se elimina y se cierra la sesión. |
| Reglas de negocio | Un feriante solo puede ver y modificar su propio registro. No puede darse de baja si tiene una inscripción en un evento en curso. |
| Validaciones | Las mismas que RF-01. La baja requiere confirmación explícita. |
| Criterio de aceptación | Dado un feriante que cambia su teléfono y guarda, entonces el cambio persiste al volver a ingresar. Cuando solicita la baja y confirma, no puede volver a ingresar con esas credenciales. |
| Prioridad | Alta |
| Trazabilidad | OE-1 · `MiCuentaController`, `Feriante/MiCuenta.vue` |
| Estado | Implementado |

### Módulo 2 — Eventos e inscripción

#### RF-05 — Gestión de eventos de feria

| Campo | Detalle |
|---|---|
| Actor | Coordinador |
| Disparador / precondición | El coordinador inició sesión y elige "Eventos". |
| Descripción | El coordinador crea, modifica y cancela eventos con nombre, fecha o fechas, hora de inicio y estado (borrador, abierto, cerrado, cancelado). Las mesas disponibles son los puestos habilitados del plano de la plaza (D-06). |
| Resultado esperado | El evento queda registrado; al pasar a "abierto", se habilita la inscripción con todos los puestos habilitados libres. |
| Reglas de negocio | RN-04, RN-09. Un evento con inscriptos no se elimina, solo se cancela. |
| Validaciones | Nombre obligatorio (máx. 100 caracteres). Fecha de inicio obligatoria, igual o posterior a hoy. Fecha de fin igual o posterior a la de inicio. Hora de inicio obligatoria. |
| Criterio de aceptación | Dado un plano con 40 puestos habilitados, cuando el coordinador crea "Encuentro de danzas" y lo abre, entonces el evento aparece disponible para los feriantes con 40 mesas libres. Si ingresa una fecha pasada, se rechaza con un mensaje. |
| Prioridad | Alta |
| Trazabilidad | E-01, D-01 · OE-5 |
| Estado | Pendiente |

#### RF-06 — Aceptación del reglamento

| Campo | Detalle |
|---|---|
| Actor | Feriante |
| Disparador / precondición | El feriante inicia la inscripción a un evento. |
| Descripción | El feriante lee y acepta el reglamento con los productos prohibidos antes de inscribirse. |
| Resultado esperado | Se registra la aceptación con fecha y hora, y se habilita la inscripción. |
| Reglas de negocio | RN-05, RN-06. |
| Validaciones | La aceptación es obligatoria para continuar. |
| Criterio de aceptación | Si el feriante no marca la aceptación, el botón "Inscribirme" permanece deshabilitado. Al aceptar, se guarda la fecha de aceptación. |
| Prioridad | Media |
| Trazabilidad | E-01 · OE-2 |
| Estado | Pendiente |

#### RF-07 — Inscripción a un evento y reserva de mesa

| Campo | Detalle |
|---|---|
| Actor | Feriante |
| Disparador / precondición | El feriante inició sesión, aceptó el reglamento (RF-06) y el evento está abierto. |
| Descripción | El feriante se inscribe al evento y reserva un número de mesa libre en el mapa de la plaza. |
| Resultado esperado | La inscripción queda registrada con su número de mesa y el sistema notifica al coordinador (RF-15). |
| Reglas de negocio | RN-03, RN-09. Los revendedores se inscriben sin número de mesa (S-06). Un feriante no puede inscribirse dos veces al mismo evento. |
| Validaciones | La mesa elegida debe existir en el evento y estar libre al momento de confirmar. |
| Criterio de aceptación | Dado un feriante de Masas, cuando elige la mesa 12 libre y confirma, entonces la mesa queda reservada y deja de aparecer disponible. Si otro feriante la reservó un instante antes, se informa "Esa mesa ya fue reservada, elegí otra". Dado un revendedor, no se muestra el mapa y la inscripción se registra sin número. |
| Prioridad | Alta |
| Trazabilidad | E-01, D-02 · OE-1, OE-2 · mapa SVG de puestos |
| Estado | Parcial |

#### RF-08 — Confirmación de asistencia

| Campo | Detalle |
|---|---|
| Actor | Feriante |
| Disparador / precondición | El feriante está inscripto y el evento no comenzó. |
| Descripción | El feriante confirma que asistirá o avisa que no podrá asistir. |
| Resultado esperado | Si confirma, su inscripción figura como "confirmada". Si avisa que no asiste, su mesa se libera. |
| Reglas de negocio | RN-10. |
| Validaciones | Solo se puede modificar antes de la hora de inicio del evento. |
| Criterio de aceptación | Dado un feriante inscripto, cuando indica que no asistirá, entonces su mesa pasa a estar disponible y el coordinador lo ve en el listado. Una vez iniciado el evento, la opción no está disponible. |
| Prioridad | Media |
| Trazabilidad | E-01 · OE-3 |
| Estado | Pendiente |

### Módulo 3 — Mesas y listados

#### RF-09 — Asignación y reasignación de mesas por prioridad

| Campo | Detalle |
|---|---|
| Actor | Coordinador |
| Disparador / precondición | Existe un evento abierto con feriantes inscriptos. |
| Descripción | El coordinador asigna o cambia el número de mesa de un feriante. El sistema ordena la lista de espera según la prioridad de la categoría. |
| Resultado esperado | La asignación se actualiza y el feriante ve su nuevo número de mesa. |
| Reglas de negocio | RN-02, RN-03, RN-09, RN-10. |
| Validaciones | La mesa destino debe estar libre. No se asigna mesa a revendedores. |
| Criterio de aceptación | Dado un evento sin mesas libres con un feriante de Artesanos y otro de Masas en espera, cuando se libera una mesa, entonces el sistema sugiere primero al de Artesanos. Intentar asignar una mesa ocupada muestra un error. |
| Prioridad | Alta |
| Trazabilidad | E-01 · OE-2 |
| Estado | Pendiente |

#### RF-10 — Listado de inscriptos por evento

| Campo | Detalle |
|---|---|
| Actor | Coordinador |
| Disparador / precondición | Existe un evento con inscriptos. |
| Descripción | El coordinador ve los inscriptos con nombre, categoría, número de mesa y estado de asistencia. Reemplaza al cuaderno de registro. |
| Resultado esperado | Listado filtrable por categoría y estado, y ordenable por número de mesa o prioridad. |
| Reglas de negocio | RN-02. |
| Validaciones | — |
| Criterio de aceptación | Dado un evento con 20 inscriptos, cuando el coordinador filtra por Masas, entonces ve solo los de esa categoría. Al ordenar por número, la lista respeta el orden de mesas. |
| Prioridad | Alta |
| Trazabilidad | D-01 · OE-5 |
| Estado | Pendiente |

### Módulo 4 — Asistencia y sanciones

#### RF-11 — Registro de presentes

| Campo | Detalle |
|---|---|
| Actor | Coordinador |
| Disparador / precondición | El evento está en curso. |
| Descripción | El coordinador marca a los feriantes presentes desde el listado. |
| Resultado esperado | Queda registrado el presente con hora. Al vencer la tolerancia, los no marcados pasan a "ausente". |
| Reglas de negocio | RN-07. |
| Validaciones | Solo se puede marcar presente entre la hora de inicio y el fin de la tolerancia. |
| Criterio de aceptación | Dado un evento que comienza a las 17:00, cuando el coordinador marca presente a un feriante a las 17:40, entonces queda como presente. A las 18:00, los no marcados figuran como ausentes. |
| Prioridad | Media |
| Trazabilidad | E-01, D-01 · OE-3 |
| Estado | Pendiente |

#### RF-12 — Registro de sanciones

| Campo | Detalle |
|---|---|
| Actor | Coordinador |
| Disparador / precondición | El feriante figura como ausente en un evento. |
| Descripción | El coordinador registra una sanción con motivo y observaciones. |
| Resultado esperado | La sanción aparece en el historial del feriante con fecha, evento y motivo. |
| Reglas de negocio | RN-08. La sanción la define el coordinador; el sistema solo la registra (S-05). |
| Validaciones | Motivo obligatorio (máx. 255 caracteres). Solo se sanciona a feriantes ausentes. |
| Criterio de aceptación | Dado un feriante ausente, cuando el coordinador registra la sanción, entonces figura en su historial. Intentar sancionar a un feriante presente muestra un error. |
| Prioridad | Media |
| Trazabilidad | E-01 · OE-3 |
| Estado | Pendiente |

### Módulo 5 — Administración de feriantes

#### RF-13 — Gestión de feriantes por el coordinador

| Campo | Detalle |
|---|---|
| Actor | Coordinador |
| Disparador / precondición | El coordinador inició sesión y abre "Feriantes". |
| Descripción | El coordinador lista, busca, crea, modifica y elimina feriantes. |
| Resultado esperado | Los cambios se reflejan en el listado. |
| Reglas de negocio | RN-01. No se elimina un feriante con inscripciones en eventos abiertos. |
| Validaciones | Las mismas que RF-01. La eliminación requiere confirmación. |
| Criterio de aceptación | Cuando el coordinador busca por apellido, ve solo las coincidencias. Cuando crea un feriante válido, aparece en el listado. |
| Prioridad | Alta |
| Trazabilidad | OE-5 · `CoordinadorFerianteController`, `Coordinador/FerianteForm.vue`, `Coordinador.vue` |
| Estado | Implementado |

#### RF-14 — Historial del feriante

| Campo | Detalle |
|---|---|
| Actor | Coordinador |
| Disparador / precondición | El coordinador abre la ficha de un feriante. |
| Descripción | El coordinador ve los eventos en los que participó el feriante, su asistencia y sus sanciones. |
| Resultado esperado | Historial ordenado del más reciente al más antiguo. |
| Reglas de negocio | RN-08. |
| Validaciones | — |
| Criterio de aceptación | Dado un feriante con 3 participaciones y 1 sanción, cuando el coordinador abre su ficha, entonces ve las 3 participaciones y la sanción con su fecha. |
| Prioridad | Baja |
| Trazabilidad | E-01 · OE-3, OE-5 |
| Estado | Pendiente |

#### RF-18 — Aprobación o rechazo de inscripciones

| Campo | Detalle |
|---|---|
| Actor | Coordinador |
| Disparador / precondición | Existe una inscripción en estado "pendiente". |
| Descripción | El coordinador revisa la inscripción y la aprueba o la rechaza desde el listado. |
| Resultado esperado | La inscripción cambia de estado y el feriante lo ve en "Mi cuenta". Si se rechaza, su mesa se libera. |
| Reglas de negocio | RN-05, RN-06 (el coordinador puede rechazar si el rubro incumple el reglamento), RN-10. |
| Validaciones | Estado destino obligatorio: "aprobada" o "rechazada". |
| Criterio de aceptación | Dada una inscripción pendiente con la mesa 7, cuando el coordinador la rechaza, entonces figura como rechazada y la mesa 7 vuelve a estar libre. Cuando la aprueba, el feriante ve "Aprobada" en su cuenta. |
| Prioridad | Alta |
| Trazabilidad | E-01 · OE-2, OE-5 · `CoordinadorFerianteController::cambiarEstado` |
| Estado | Parcial (hoy el estado está en el feriante, no por evento) |

### Módulo 6 — Comunicación

#### RF-15 — Notificación al coordinador

| Campo | Detalle |
|---|---|
| Actor | Sistema |
| Disparador / precondición | Un feriante confirma una inscripción. Está configurado `WHATSAPP_COORDINADOR`. |
| Descripción | El sistema notifica al coordinador por WhatsApp. |
| Resultado esperado | El coordinador recibe nombre, categoría, evento y número de mesa. |
| Reglas de negocio | — |
| Validaciones | El número del coordinador se lee de la configuración, no del código. |
| Criterio de aceptación | Dada una inscripción nueva, cuando se confirma, entonces se genera la notificación con los cuatro datos. |
| Prioridad | Media |
| Trazabilidad | E-01 · OE-4 |
| Estado | Implementado |

#### RF-16 — Acceso al WhatsApp del coordinador

| Campo | Detalle |
|---|---|
| Actor | Feriante |
| Disparador / precondición | El feriante inició sesión. |
| Descripción | Un botón abre una conversación de WhatsApp con el coordinador. |
| Resultado esperado | Se abre WhatsApp con el chat del coordinador. |
| Reglas de negocio | — |
| Validaciones | El número se toma de la configuración. |
| Criterio de aceptación | Cuando el feriante toca "Hablar con el coordinador", se abre WhatsApp con ese chat. |
| Prioridad | Baja |
| Trazabilidad | E-01 · OE-4 |
| Estado | Pendiente |

#### RF-17 — Buzón de consultas

| Campo | Detalle |
|---|---|
| Actor | Feriante, Coordinador |
| Disparador / precondición | El usuario inició sesión. |
| Descripción | El feriante envía consultas y el coordinador las lee y responde. |
| Resultado esperado | La consulta pasa de "pendiente" a "respondida" y el feriante ve la respuesta. |
| Reglas de negocio | Cada feriante ve solo sus propias consultas. |
| Validaciones | Asunto obligatorio (máx. 100 caracteres). Mensaje y respuesta obligatorios (máx. 1000 caracteres). |
| Criterio de aceptación | Dado un feriante que envía una consulta, cuando el coordinador responde, entonces la consulta figura como respondida y el feriante ve la respuesta en su cuenta. |
| Prioridad | Baja |
| Trazabilidad | E-01 · OE-4 |
| Estado | Pendiente |

### Matriz de trazabilidad

| RF | Módulo | Fuente | Objetivo | Reglas | Prioridad | Estado |
|---|---|---|---|---|---|---|
| RF-01 | Cuenta y acceso | E-01 | OE-1, OE-6 | RN-01 | Alta | Implementado |
| RF-02 | Cuenta y acceso | E-01 | OE-1, OE-2 | RN-01, RN-02, RN-03 | Alta | Parcial |
| RF-03 | Cuenta y acceso | — | OE-1 | — | Alta | Implementado |
| RF-04 | Cuenta y acceso | — | OE-1 | — | Alta | Implementado |
| RF-05 | Eventos e inscripción | E-01, D-01 | OE-5 | RN-04, RN-09 | Alta | Pendiente |
| RF-06 | Eventos e inscripción | E-01 | OE-2 | RN-05, RN-06 | Media | Pendiente |
| RF-07 | Eventos e inscripción | E-01, D-02 | OE-1, OE-2 | RN-03, RN-09 | Alta | Parcial |
| RF-08 | Eventos e inscripción | E-01 | OE-3 | RN-10 | Media | Pendiente |
| RF-09 | Mesas y listados | E-01 | OE-2 | RN-02, RN-03, RN-09, RN-10 | Alta | Pendiente |
| RF-10 | Mesas y listados | D-01 | OE-5 | RN-02 | Alta | Pendiente |
| RF-11 | Asistencia y sanciones | E-01, D-01 | OE-3 | RN-07 | Media | Pendiente |
| RF-12 | Asistencia y sanciones | E-01 | OE-3 | RN-08 | Media | Pendiente |
| RF-13 | Administración | — | OE-5 | RN-01 | Alta | Implementado |
| RF-14 | Administración | E-01 | OE-3, OE-5 | RN-08 | Baja | Pendiente |
| RF-18 | Administración | E-01 | OE-2, OE-5 | RN-05, RN-06, RN-10 | Alta | Parcial |
| RF-15 | Comunicación | E-01 | OE-4 | — | Media | Implementado |
| RF-16 | Comunicación | E-01 | OE-4 | — | Baja | Pendiente |
| RF-17 | Comunicación | E-01 | OE-4 | — | Baja | Pendiente |

La relación de cada RF con las tablas está en [modelo-de-datos.md](modelo-de-datos.md). Cuando se completen la API y los wireframes, esta matriz se amplía con las columnas **Endpoints** y **Pantallas**.

## 3. Requerimientos no funcionales

### Seguridad

| ID | Requerimiento | Métrica y valor objetivo |
|---|---|---|
| RNF-01 | Las contraseñas se almacenan cifradas con hash. | 100 % de las contraseñas con bcrypt; ninguna en texto plano en la base de datos. |
| RNF-02 | Autorización por rol verificada en el servidor. | 100 % de los endpoints del coordinador responden 403 a un feriante autenticado y 401 sin autenticación. |
| RNF-03 | Protección contra intentos de acceso por fuerza bruta. | Máximo 5 intentos fallidos de inicio de sesión por minuto por usuario e IP. |
| RNF-04 | Validación de datos en el servidor. | 100 % de los formularios y endpoints de escritura validan en el servidor, además del cliente. |
| RNF-05 | Sin secretos en el repositorio. | 0 credenciales o claves en el código; todas en variables de entorno (`.env`). |

### Rendimiento

| ID | Requerimiento | Métrica y valor objetivo |
|---|---|---|
| RNF-06 | Carga rápida de las pantallas principales. | Menos de 3 s en conexión móvil 4G, medido con Lighthouse. |
| RNF-07 | Respuesta rápida de la API. | 95 % de las respuestas en menos de 500 ms con 500 feriantes y 100 mesas cargados. |

### Usabilidad

| ID | Requerimiento | Métrica y valor objetivo |
|---|---|---|
| RNF-08 | Inscripción simple para usuarios con poca experiencia. | Un feriante que usa el sistema por primera vez completa registro e inscripción en menos de 5 minutos, sin ayuda (prueba con al menos 3 usuarios). |
| RNF-09 | Pocos pasos para la operación principal. | Máximo 3 pantallas desde "Mi cuenta" hasta tener una mesa reservada. |
| RNF-10 | Mensajes claros. | 100 % de los mensajes de error en español, indicando el campo y cómo corregirlo. |
| RNF-11 | Legibilidad y uso táctil. | Contraste de texto mínimo 4,5:1 (WCAG AA) y botones de al menos 44 × 44 px. |

### Compatibilidad

| ID | Requerimiento | Métrica y valor objetivo |
|---|---|---|
| RNF-12 | Diseño adaptable. | Funciona sin desplazamiento horizontal en pantallas desde 360 px de ancho. |
| RNF-13 | Navegadores soportados. | Últimas 2 versiones de Chrome, Firefox, Edge y Safari para iOS. |

### Mantenibilidad

| ID | Requerimiento | Métrica y valor objetivo |
|---|---|---|
| RNF-14 | Pruebas automatizadas del backend. | Cobertura mínima del 70 % en controladores y servicios. |
| RNF-15 | Integración continua. | 100 % de los pull requests a `develop` y `main` ejecutan la suite en GitHub Actions; el merge se bloquea si falla. |
| RNF-16 | Estilo de código consistente. | Código PHP según PSR-12, verificado con Laravel Pint sin errores. |

### Despliegue

| ID | Requerimiento | Métrica y valor objetivo |
|---|---|---|
| RNF-17 | Despliegue reproducible con Docker. | La aplicación se levanta en el servidor del docente con `docker compose up -d`, sin pasos manuales adicionales fuera de los documentados en [instalacion.md](instalacion.md). |
| RNF-18 | Datos de prueba. | Los seeders cargan al menos 1 coordinador, 20 feriantes de las 4 categorías y 1 evento con mesas, con un solo comando. |
