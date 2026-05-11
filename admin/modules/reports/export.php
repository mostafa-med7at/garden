<?php
// modules/reports/export.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user = requireLogin();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/ReportController.php';

$controller = new ReportController();
$controller->export($user);
