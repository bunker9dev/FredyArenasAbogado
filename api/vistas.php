<?php
/**
 * api/vistas.php — Registra una vista única por artículo
 * Acepta POST JSON: { "articulo_id": 3 }
 * Devuelve JSON: { "ok": true }
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../includes/config/database.php';
require_once __DIR__ . '/../includes/functions/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$body        = json_decode(file_get_contents('php://input'), true);
$articulo_id = isset($body['articulo_id']) ? (int)$body['articulo_id'] : 0;

if (!$articulo_id) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

registrar_vista($articulo_id);
echo json_encode(['ok' => true]);
