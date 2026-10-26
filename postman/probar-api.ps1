# =====================================================================
# Feriaza API v1: pruebas manuales automatizadas desde PowerShell
# Uso (desde la carpeta del proyecto, con php artisan serve corriendo):
#   powershell -ExecutionPolicy Bypass -File postman\probar-api.ps1
# =====================================================================

$base = 'http://localhost:8000/api/v1'
$emailNuevo = "api$(Get-Random -Maximum 99999)@feriaza.test"   # distinto en cada corrida
$aprobadas = 0
$fallidas = 0

function Probar($nombre, $metodo, $ruta, $esperado, $cuerpo = $null, $token = $null) {
    $headers = @{ Accept = 'application/json' }
    if ($token) { $headers.Authorization = "Bearer $token" }

    $params = @{
        Method          = $metodo
        Uri             = "$base$ruta"
        Headers         = $headers
        TimeoutSec      = 120
        UseBasicParsing = $true
    }
    if ($cuerpo) {
        $params.ContentType = 'application/json; charset=utf-8'
        $params.Body = [System.Text.Encoding]::UTF8.GetBytes(($cuerpo | ConvertTo-Json))
    }

    $codigo = 0
    $contenido = $null
    try {
        $resp = Invoke-WebRequest @params
        $codigo = [int]$resp.StatusCode
        $contenido = $resp.Content
    } catch {
        # Windows PowerShell 5.1 lanza una excepción con las respuestas 4xx y 5xx: se lee igual
        $r = $_.Exception.Response
        if ($r) {
            $codigo = [int]$r.StatusCode
            try {
                $lector = New-Object System.IO.StreamReader($r.GetResponseStream())
                $contenido = $lector.ReadToEnd()
            } catch {
                $contenido = $_.ErrorDetails.Message
            }
            if (-not $contenido) { $contenido = $_.ErrorDetails.Message }
        } else {
            Write-Host "      Error de conexión: $($_.Exception.Message)" -ForegroundColor DarkYellow
        }
    }

    $json = $null
    if ($contenido) { try { $json = $contenido | ConvertFrom-Json } catch { } }

    if ($codigo -eq $esperado) {
        Write-Host ("  OK    {0,-45} {1}" -f $nombre, $codigo) -ForegroundColor Green
        $script:aprobadas++
    } else {
        Write-Host ("  FALLA {0,-45} esperaba {1}, recibió {2}" -f $nombre, $esperado, $codigo) -ForegroundColor Red
        if ($json) { Write-Host "        $($json | ConvertTo-Json -Compress -Depth 3)" -ForegroundColor DarkGray }
        $script:fallidas++
    }
    return $json
}

Write-Host "`nProbando $base (email nuevo: $emailNuevo)`n"

$registro = @{
    nombre = 'Ana'; apellido = 'Prueba'; email = $emailNuevo
    password = 'Prueba123'; password_confirmation = 'Prueba123'
    telefono = '3644000001'; categoria_id = 1
}

Probar 'Listar categorías'                      GET  '/categorias' 200 | Out-Null
Probar 'Registro de feriante'                   POST '/feriantes/registro' 201 $registro | Out-Null
Probar 'Registro con email duplicado'           POST '/feriantes/registro' 422 $registro | Out-Null
$corta = $registro.Clone(); $corta.email = 'corta@feriaza.test'; $corta.password = '123'; $corta.password_confirmation = '123'
Probar 'Registro con contraseña corta'          POST '/feriantes/registro' 422 $corta | Out-Null

$lf = Probar 'Login feriante'                   POST '/feriantes/login' 200 @{ email = 'feriante@feriaza.test'; password = 'Feriante123' }
Probar 'Login con contraseña incorrecta'        POST '/feriantes/login' 422 @{ email = 'feriante@feriaza.test'; password = 'incorrecta' } | Out-Null
$lc = Probar 'Login coordinador'                POST '/coordinador/login' 200 @{ email = 'coordinador@feriaza.test'; password = 'Coordinador123' }

$tf = $lf.token
$tc = $lc.token

Probar 'Ver mi cuenta'                          GET   '/mi-cuenta' 200 $null $tf | Out-Null
Probar 'Modificar mi cuenta'                    PATCH '/mi-cuenta' 200 @{ telefono = '3644999999'; instagram = '@feriaza' } $tf | Out-Null
Probar 'Mi cuenta sin token'                    GET   '/mi-cuenta' 401 | Out-Null
Probar 'Mi cuenta con token de coordinador'     GET   '/mi-cuenta' 403 $null $tc | Out-Null

# --- Coordinador: gestión de feriantes (HU-05) ---
Write-Host "`n  Coordinador: gestión de feriantes`n"
$idPrueba = $lf.feriante.id   # feriante@feriaza.test: aprobado e inscripto en el evento abierto

Probar 'Listar feriantes'                       GET   '/coordinador/feriantes' 200 $null $tc | Out-Null
Probar 'Buscar feriantes (?q=prueba)'           GET   '/coordinador/feriantes?q=prueba' 200 $null $tc | Out-Null
Probar 'Listar feriantes con token de feriante' GET   '/coordinador/feriantes' 403 $null $tf | Out-Null
$emailCoord = "coord$(Get-Random -Maximum 99999)@feriaza.test"
$nuevo = Probar 'Crear feriante'                POST  '/coordinador/feriantes' 201 @{
    nombre = 'Carlos'; apellido = 'Cargado'; email = $emailCoord; password = 'Prueba123'
    telefono = '3644000002'; categoria_id = 2
} $tc
Probar 'Crear feriante sin datos'               POST  '/coordinador/feriantes' 422 @{ nombre = '' } $tc | Out-Null
$idNuevo = $nuevo.data.id
Probar 'Ver feriante'                           GET   "/coordinador/feriantes/$idNuevo" 200 $null $tc | Out-Null
Probar 'Modificar feriante (aprobar)'           PATCH "/coordinador/feriantes/$idNuevo" 200 @{ estado = 'aprobado'; telefono = '3644000003' } $tc | Out-Null
Probar 'Ver feriante inexistente'               GET   '/coordinador/feriantes/999999' 404 $null $tc | Out-Null
Probar 'Historial de un feriante'               GET   "/coordinador/feriantes/$idPrueba/historial" 200 $null $tc | Out-Null
Probar 'Eliminar feriante con evento abierto'   DELETE "/coordinador/feriantes/$idPrueba" 409 $null $tc | Out-Null
Probar 'Eliminar feriante'                      DELETE "/coordinador/feriantes/$idNuevo" 200 $null $tc | Out-Null

Write-Host "`n  Cierre de sesión`n"
Probar 'Logout feriante'                      POST  '/logout' 200 $null $tf | Out-Null
Probar 'Token revocado'                         GET   '/mi-cuenta' 401 $null $tf | Out-Null
Probar 'Logout coordinador'                     POST  '/logout' 200 $null $tc | Out-Null

$color = if ($fallidas -eq 0) { 'Green' } else { 'Yellow' }
Write-Host "`nResultado: $aprobadas aprobadas, $fallidas fallidas`n" -ForegroundColor $color
Write-Host "Nota: registro y login admiten 6 intentos por minuto. Si ves 429, esperá un minuto y corré de nuevo.`n"
