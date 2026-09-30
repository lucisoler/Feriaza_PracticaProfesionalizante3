# Fundamentación

## 1. Contexto

La feria de la Plaza San Martín de Castelli reúne a feriantes que venden sus productos durante los eventos que se realizan en la plaza (por ejemplo, el "Encuentro de danzas"). La organización está a cargo de un coordinador, que inscribe a los feriantes, les asigna un número de mesa y controla que se cumplan las reglas de la feria.

Los feriantes se clasifican en cuatro categorías según el rubro o mercadería que ofrecen:

| Categoría | Qué venden | Prioridad | ¿Recibe mesa? |
|---|---|---|---|
| Artesanos | Productos artesanales y souvenirs | Alta | Sí |
| Manualidades | Productos hechos a mano (mimbre, pequeñas estatuillas, joyas), generalmente elaborados por descendientes de pueblos originarios de Castelli | Alta | Sí |
| Masas | Masas, tortas, facturas, postres | Media | Sí |
| Revendedores | Productos existentes o de una organización o empresa (medias, electrodomésticos pequeños, cables USB, parlantes bluetooth, etc.) | Baja | No, traen su propia mesa |

Artesanos y manualidades tienen mayor prioridad porque fomentan la cultura de la localidad, el turismo y dan a conocer sus especialidades, así como su flora y fauna.

## 2. Problemática

Hoy la inscripción se realiza de forma manual. Para cada evento, el coordinador anota en un cuaderno el apellido y nombre de cada feriante, el producto que vende, un número y la firma del feriante, y entrega un número tomado de un talonario de papel.

A partir de la entrevista con el coordinador y de la observación del cuaderno de registro se identifican los siguientes problemas:

- **Sin respaldo:** toda la información depende de un cuaderno físico que puede perderse o dañarse.
- **Consulta difícil:** no es posible buscar rápidamente a un feriante, ver su historial de participación o saber cuántas mesas quedan libres.
- **Asignación manual:** la asignación de números de mesa y la aplicación de prioridades por categoría dependen de la memoria y el criterio del coordinador en el momento.
- **Control de reglas sin registro:** las prohibiciones (venta de ropa, electrodomésticos grandes) y las categorías no se controlan de forma sistemática.
- **Asistencia y sanciones sin trazabilidad:** existe una tolerancia de una hora y se sanciona a quien no se presenta, pero no hay un registro ordenado de presentes ni de sanciones.
- **Comunicación informal:** las consultas y avisos entre feriantes y coordinador no tienen un canal organizado.

## 3. Usuarios destinatarios

- **Feriante:** persona que vende en la feria. Se inscribe, reserva su número de mesa, confirma asistencia y consulta sus datos. Muchos acceden desde el celular y no todos tienen experiencia con la tecnología, por lo que la interfaz debe ser simple.
- **Coordinador:** organiza la feria. Gestiona eventos, feriantes y mesas, controla la asistencia, registra sanciones y responde consultas.

## 4. Solución propuesta y valor esperado

Una aplicación web que reemplaza el cuaderno y el talonario por un registro digital ordenado, aplica automáticamente las reglas de prioridad y de categorías, y da al coordinador información en tiempo real sobre inscriptos y mesas disponibles. El objetivo central que planteó el coordinador es que **ningún feriante se quede sin mesa**, y el sistema apunta directamente a eso.

Para los feriantes, significa poder inscribirse sin depender de encontrar al coordinador en persona, saber de antemano qué número de mesa tienen y contar con un canal claro para hacer consultas.

## 5. Objetivos

### Objetivo general

Desarrollar una aplicación web que digitalice la inscripción de feriantes y la asignación de mesas de la feria de la Plaza San Martín, respetando las prioridades por categoría y las reglas de la feria, para que ningún feriante habilitado se quede sin lugar.

### Objetivos específicos

| ID | Objetivo específico |
|---|---|
| OE-1 | Digitalizar la inscripción de feriantes, reemplazando el cuaderno de registro. |
| OE-2 | Gestionar la asignación de números de mesa aplicando las prioridades por categoría. |
| OE-3 | Controlar la asistencia de los feriantes y registrar las sanciones por ausencia. |
| OE-4 | Ofrecer un canal de comunicación ordenado entre feriantes y coordinador. |
| OE-5 | Brindar al coordinador herramientas para gestionar eventos, feriantes y listados. |
| OE-6 | Proveer una interfaz simple, usable desde el celular por personas con poca experiencia tecnológica. |

## 6. Alcance

### Incluye

- Registro e inicio de sesión de feriantes y del coordinador.
- Gestión de la cuenta propia del feriante (consulta, modificación y baja).
- Gestión de eventos de feria por parte del coordinador.
- Inscripción de feriantes a eventos y reserva de número de mesa (excepto revendedores).
- Asignación y reasignación de mesas por parte del coordinador según prioridad.
- Confirmación de asistencia, registro de presentes y registro de sanciones.
- Aceptación del reglamento de la feria (productos prohibidos) al inscribirse.
- Notificación al coordinador ante nuevas inscripciones y acceso al WhatsApp del coordinador.
- Buzón de consultas.
- Listado de inscriptos por evento, que reemplaza al cuaderno.

### No incluye

- Cobro de canon, pagos en línea o facturación.
- Aplicación móvil nativa (el sistema es web y adaptable al celular).
- Envío masivo de mensajes a los feriantes.
- Venta en línea de los productos de los feriantes.
- Gestión de visitantes del público general.

### Viabilidad

El alcance se considera abordable en los sprints 3 a 6 porque parte del sistema ya está implementado (registro de feriantes, inicio de sesión, cuenta propia, gestión de feriantes por el coordinador y notificación por WhatsApp). La planificación detallada está en [planificacion.md](planificacion.md).

## 7. Supuestos

Los siguientes puntos surgieron durante el análisis y quedan pendientes de confirmación con el coordinador. Hasta entonces, se trabaja con estos supuestos:

| ID | Supuesto | Pendiente de confirmar |
|---|---|---|
| S-01 | Cada mesa mide 1,5 m. | Unidad de la medida "1½". |
| S-02 | El número de mesa se reinicia en cada evento. | Si el número del talonario es el número de mesa o un orden de llegada. |
| S-03 | "Costura", "accesorios" y prendas como pantalones se tratan como casos a revisar por el coordinador. | Si son categorías aparte o excepciones a la prohibición de venta de ropa. |
| S-04 | "Agregar otro rubro" se interpreta como la opción "Otro", con una descripción libre del rubro. | Si se refería a esa opción o a permitir más de un rubro por feriante, y qué prioridad tiene "Otro". |
| S-05 | La sanción la decide el coordinador; el sistema solo la registra. | En qué consiste la sanción y cuánto dura. |
| S-06 | Los revendedores se inscriben en el sistema, pero sin número de mesa. | Si deben inscribirse. |
| S-07 | Las mesas disponibles son las del plano fijo de la plaza, iguales para todos los eventos. | Cantidad de mesas y qué ocurre si hay más inscriptos que mesas. |

## 8. Fuentes del relevamiento

| ID | Fuente | Descripción |
|---|---|---|
| E-01 | Entrevista al coordinador de la plaza | Categorías, reglas, datos del formulario y requerimientos. Fecha: 08/09/2026 de las 19:30 hasta las 20:00 |
| D-01 | Cuaderno de registro del evento "Encuentro de danzas" | Evidencia del proceso actual. La imagen se incluye anonimizada, sin nombres ni firmas. |
| D-02 | Talonario de números | Evidencia del sistema actual de asignación de números. |
