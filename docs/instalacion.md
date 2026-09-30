# Instalación y ejecución

## 1. Requisitos

| Herramienta | Versión |
|---|---|
| PHP | 8.2 o superior (desarrollado con 8.4) |
| Composer | 2.8 o superior |
| Node.js y npm | Node 22 (LTS) |
| Base de datos | MySQL 8.0 |
| Git | Cualquier versión reciente |

El detalle del stack está en [stack.md](stack.md).

## 2. Instalación local (Windows con MySQL 8)

### 2.1 Clonar el repositorio

```powershell
git clone https://github.com/lucisoler/Feriaza_PracticaProfesionalizante3.git
cd Feriaza_PracticaProfesionalizante3\backendpc
```

### 2.2 Instalar dependencias

```powershell
composer install
npm install
```

### 2.3 Configurar el entorno

```powershell
copy .env.example .env
php artisan key:generate
```

Editar el archivo `.env` con estos valores:

```env
APP_NAME=Feriaza
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=feria_plaza
DB_USERNAME=root
DB_PASSWORD=tu_contraseña

WHATSAPP_COORDINADOR=549XXXXXXXXXX
```

`WHATSAPP_COORDINADOR` es el número del coordinador en formato internacional, sin espacios ni el signo `+`. Ninguna credencial real se sube al repositorio (RNF-05).

### 2.4 Crear la base de datos

1. Verificar que el servicio de MySQL esté iniciado. En PowerShell: `Get-Service MySQL80` debe mostrar `Running`.
2. Crear la base de datos desde MySQL Workbench (o desde la consola de MySQL):

```sql
CREATE DATABASE feria_plaza CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

El puerto (`3307` en este entorno) y la contraseña de `root` deben coincidir con los del archivo `.env`.

### 2.5 Crear las tablas y cargar los datos de prueba

```powershell
php artisan migrate --seed
```

Para empezar de cero en cualquier momento (borra todos los datos):

```powershell
php artisan migrate:fresh --seed
```

### 2.6 Ejecutar la aplicación

Se necesitan dos terminales abiertas al mismo tiempo:

```powershell
# Terminal 1: servidor de Laravel
php artisan serve
```

```powershell
# Terminal 2: compilación del frontend (Vite)
npm run dev
```

La aplicación queda disponible en `http://localhost:8000`.

## 3. Usuarios de prueba

Los seeders `CoordinadorSeeder` y `FerianteSeeder` crean los siguientes usuarios (datos ficticios, D-05). `FerianteSeeder` carga además otros 25 feriantes de las cinco categorías, todos con la misma contraseña. Son credenciales solo para desarrollo.

| Rol | Correo | Contraseña | Ingreso |
|---|---|---|---|
| Coordinador | coordinador@feriaza.test | Coordinador123 | `/login` |
| Feriante | feriante@feriaza.test | Feriante123 | `/mi-cuenta/login` |

## 4. Cómo probar la aplicación

| Flujo | Pasos |
|---|---|
| Registro de feriante | Inicio → "Quiero inscribirme" → completar los 3 pasos → se abre "Mi cuenta". |
| Cuenta del feriante | Ingresar como feriante → editar los datos → guardar → verificar el cambio. |
| Panel del coordinador | Ingresar como coordinador → crear un feriante → editarlo → aprobarlo → eliminarlo. |
| API | Importar en Postman la colección `docs/postman/feriaza.postman_collection.json` (desde el Sprint 4). |

## 5. Pruebas automatizadas

```powershell
php artisan test
```

El detalle de la suite y sus resultados se registra en `docs/pruebas.md` a partir del Sprint 4.

## 6. Despliegue con Docker

El despliegue en el servidor del docente se realiza en el Sprint 6. Esta sección se completará con:

- El `Dockerfile` de la aplicación y el `docker-compose.yml` con los servicios `app` (PHP con Laravel), `web` (servidor web) y `db` (MySQL 8.0).
- Las variables de entorno de producción (sin secretos).
- Los comandos de despliegue: `docker compose up -d --build` y `docker compose exec app php artisan migrate --force`.
- La dirección del servidor provista por el docente y la verificación de los flujos críticos en esa instancia (RNF-17).

## 7. Problemas frecuentes

| Problema | Solución |
|---|---|
| `SQLSTATE[HY000] [2002]` al migrar | El servicio `MySQL80` no está iniciado: `Start-Service MySQL80` (PowerShell como administrador). |
| `SQLSTATE[HY000] [1045] Access denied` | La contraseña de `DB_PASSWORD` no coincide con la de `root`, o `DB_PORT` apunta a otro servidor. Después de corregir el `.env`, ejecutar `php artisan config:clear`. |
| `Unknown database 'feria_plaza'` | Falta crear la base de datos (paso 2.4). |
| XAMPP muestra "MySQL shutdown unexpectedly" | El proyecto no usa el MySQL de XAMPP; no es necesario iniciarlo. |
| La página se ve sin estilos | Falta ejecutar `npm run dev` en la segunda terminal. |
| `No application encryption key has been specified` | Falta ejecutar `php artisan key:generate`. |
