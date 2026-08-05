<?php
/**
 * api/feedback.php — Guarda el voto "¿Te resultó útil?"
 * Acepta POST JSON: { "articulo_id": 3, "valor": "si" }
 * Devuelve JSON: { "ok": true } | { "ok": false, "msg": "..." }
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../includes/config/database.php';
require_once __DIR__ . '/../includes/functions/helpers.php';

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$articulo_id = isset($body['articulo_id']) ? (int)$body['articulo_id'] : 0;
$valor       = $body['valor'] ?? '';

if (!$articulo_id || !in_array($valor, ['si', 'no'], true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Datos inválidos']);
    exit;
}

$guardado = guardar_feedback($articulo_id, $valor);
echo json_encode(['ok' => $guardado]);
