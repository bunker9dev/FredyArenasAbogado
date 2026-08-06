<?php
/**
 * admin-head.php — <head> compartido del panel de administración.
 * Incluir al inicio de cada página del admin, después de requerir auth.
 *
 * Variables opcionales que la página puede definir antes de incluir:
 *   $adm_title  — título de la pestaña (sin el sufijo)
 */
$adm_title = isset($adm_title) ? $adm_title . ' · Panel' : 'Panel · Fredy Arenas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?php echo htmlspecialchars($adm_title, ENT_QUOTES); ?></title>
<link rel="shortcut icon" href="/assets/img/logo_fredyarenasabogado_412x377fondoblanco.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css">
</head>
