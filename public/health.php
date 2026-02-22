<?php
// public/health.php – lightweight liveness probe used by the CI smoke test.
// Intentionally self-contained: does NOT require config/config.php or the DB.
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status' => 'ok']);
