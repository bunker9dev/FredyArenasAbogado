<?php
/**
 * database.php — Conexión a la base de datos
 *
 * Las credenciales reales van en un archivo .env o directamente
 * aquí si el hosting no soporta variables de entorno.
 * Este archivo está bloqueado por /includes/.htaccess.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'fredyarenasabogado_fredyarenas');  // ← cambiar tras rotar la clave
define('DB_PASS', 'TU_CLAVE_SEGURA_AQUI');             // ← nunca subir al repo
define('DB_NAME', 'fredyarenasabogado_db');
define('DB_PORT', 3306);

// Clave secreta del sitio (para firmar el captcha del formulario de contacto).
// Cámbiala por una cadena larga y aleatoria propia — no uses esta de ejemplo.
define('SITE_SECRET', 'cambia-esto-por-una-cadena-larga-y-aleatoria-propia-2026');

/**
 * Devuelve una conexión MySQLi activa o false si falla.
 * Silencia el error con @ para no exponer credenciales;
 * el log de PHP sí lo registra.
 */
function db_connect(): mysqli|false
{
    static $conn = null;

    if ($conn instanceof mysqli && $conn->ping()) {
        return $conn;
    }

    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($conn->connect_errno) {
        error_log('DB connect error: ' . $conn->connect_error);
        return false;
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}

// Alias de compatibilidad con el código anterior
function concectarDbBlog():    mysqli|false { return db_connect(); }
function concectarDbContac():  mysqli|false { return db_connect(); }