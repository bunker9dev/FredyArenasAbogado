<?php
require_once __DIR__ . '/../../admin/includes/auth.php';
requiere_auth();
require_once __DIR__ . '/../../includes/config/database.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
    header('Location: /admin/blog/');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
   ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $db = db_connect();
    if ($db) {
        // Obtener imagen para borrarla del disco
        $st = $db->prepare("SELECT imagen FROM blog WHERE id = ?");
        $st->bind_param('i', $id);
        $st->execute();
        $st->bind_result($imagen); $st->fetch(); $st->close();

        $st2 = $db->prepare("DELETE FROM blog WHERE id = ?");
        $st2->bind_param('i', $id);
        $st2->execute();

        if ($imagen) {
            $ruta = __DIR__ . '/../../imagenes/' . $imagen;
            if (is_file($ruta)) @unlink($ruta);
        }
    }
}

header('Location: /admin/blog/?ok=borrado');
exit;
