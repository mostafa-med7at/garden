<?php
// modules/land/ping_sensor.php (AJAX Endpoint)
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
header('Content-Type: application/json');

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/PlotController.php';

$controller = new PlotController();
$controller->pingSensor();
