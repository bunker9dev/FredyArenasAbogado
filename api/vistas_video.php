<?php
/**
 * api/vistas_video.php — Registra una vista única por video
 * Acepta POST JSON: { "video_id": 3 }
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

$body     = json_decode(file_get_contents('php://input'), true);
$video_id = isset($body['video_id']) ? (int)$body['video_id'] : 0;

if (!$video_id) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

registrar_vista_video($video_id);
echo json_encode(['ok' => true]);