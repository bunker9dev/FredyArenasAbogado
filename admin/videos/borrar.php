<?php
require_once __DIR__ . '/../../admin/includes/auth.php';
requiere_auth();
require_once __DIR__ . '/../../includes/config/database.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
    header('Location: /admin/videos/'); exit;
}
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
   ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $db = db_connect();
    if ($db) {
        $st = $db->prepare("DELETE FROM videos WHERE id=?");
        $st->bind_param('i', $id);
        $st->execute();
    }
}
header('Location: /admin/videos/?ok=borrado');
exit;
