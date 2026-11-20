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

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=cuenta@gmail.com
MAIL_PASSWORD=contraseña_de_aplicacion
MAIL_FROM_ADDRESS=cuenta@gmail.com
```

`WHATSAPP_COORDINADOR` es el número del coordinador en formato internacional, sin espacios ni el signo `+`. Los correos (aviso de inscripción al coordinador y recuperación de contraseña) salen de la cuenta de Gmail indicada; `MAIL_PASSWORD` es una contraseña de aplicación de Google (Cuenta de Google → Seguridad → Verificación en dos pasos → Contraseñas de aplicaciones), no la contraseña normal. Con `MAIL_MAILER=log` no se envían y quedan en `storage/logs/laravel.log`. Ninguna credencial real se sube al repositorio (RNF-05).

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

La aplicación se empaqueta en una sola imagen (FrankenPHP + PHP 8.4 sobre Alpine) y la base de datos es SQLite, guardada en el volumen `app-storage`. El `Dockerfile` no incluye Node, por eso el frontend se compila antes en la máquina de despliegue.

### 6.1 Requisitos

- Docker y Docker Compose (v2 o superior).
- Node 22 y npm, solo para compilar el frontend.
- Un archivo `.env` con `APP_KEY` generada y las credenciales reales (no se sube al repositorio).

### 6.2 Pasos

```bash
# 1. Compilar el frontend (genera public/build, que el Dockerfile exige)
npm ci
npm run build

# 2. Construir y levantar el contenedor
docker compose up -d --build

# 3. Crear las tablas (sin --seed: no cargar usuarios de prueba en producción)
docker compose exec app php artisan migrate --force

# 4. Verificar
docker compose ps
docker compose logs -f app
```

La aplicación queda en `http://localhost:8089`. El puerto se cambia con `APP_PORT` en el `.env`.

### 6.3 Variables de producción

`docker-compose.yml` fija estos valores, que prevalecen sobre los del `.env`:

| Variable | Valor | Motivo |
|---|---|---|
| `APP_ENV` | `production` | Modo de producción |
| `APP_DEBUG` | `false` | No mostrar trazas de error al público |
| `APP_URL` | `${APP_PUBLIC_URL}` o `http://localhost:8089` | Dirección pública; se usa en enlaces y en los callbacks de Google/Facebook |
| `DB_CONNECTION` / `DB_DATABASE` | `sqlite` / `/app/storage/app/database.sqlite` | Base dentro del volumen persistente |

Para publicar en un servidor, definir `APP_PUBLIC_URL` en el `.env` del proyecto (Compose lo lee de ahí) o en el shell, por ejemplo `APP_PUBLIC_URL=https://feriaza.ejemplo.org`. No hace falta mantener `APP_URL` del `.env` en sincronía, porque el compose lo sobrescribe. Esa URL debe registrarse en las consolas de Google y Facebook como `${APP_URL}/auth/google/callback` y `${APP_URL}/auth/facebook/callback`.

### 6.4 Operación

| Tarea | Comando |
|---|---|
| Ver estado | `docker compose ps` |
| Ver logs | `docker compose logs -f app` |
| Reiniciar | `docker compose restart app` |
| Actualizar tras cambios de código | `npm run build` y luego `docker compose up -d --build`, y `docker compose exec app php artisan migrate --force` si hay migraciones nuevas |
| Detener | `docker compose down` (conserva los datos) |
| Borrar todo, incluidos los datos | `docker compose down -v` (**destructivo**: borra la base SQLite) |

### 6.5 Verificación en la instancia del docente

La dirección del servidor la provee el docente. En esa instancia hay que verificar los flujos críticos (RNF-17): inicio de sesión del coordinador, registro y cuenta del feriante, y la carga de planos.

## 7. Problemas frecuentes

| Problema | Solución |
|---|---|
| `SQLSTATE[HY000] [2002]` al migrar | El servicio `MySQL80` no está iniciado: `Start-Service MySQL80` (PowerShell como administrador). |
| `SQLSTATE[HY000] [1045] Access denied` | La contraseña de `DB_PASSWORD` no coincide con la de `root`, o `DB_PORT` apunta a otro servidor. Después de corregir el `.env`, ejecutar `php artisan config:clear`. |
| `Unknown database 'feria_plaza'` | Falta crear la base de datos (paso 2.4). |
| XAMPP muestra "MySQL shutdown unexpectedly" | El proyecto no usa el MySQL de XAMPP; no es necesario iniciarlo. |
| La página se ve sin estilos | Falta ejecutar `npm run dev` en la segunda terminal. |
| `Falta public/build` al construir la imagen | Ejecutar `npm ci && npm run build` antes de `docker compose up -d --build`. |
| `No space left on device` al construir la imagen | El almacenamiento de Docker está lleno. Liberar espacio con `docker system prune -f` y `docker builder prune -f` (no borra volúmenes ni la imagen en uso) y reintentar. Si la partición de Docker es muy chica, ampliarla. |
| `No application encryption key has been specified` en el contenedor | Falta `APP_KEY` en el `.env`: generarla con `php artisan key:generate --show` y pegarla en el `.env`. |
| `No application encryption key has been specified` | Falta ejecutar `php artisan key:generate`. |
