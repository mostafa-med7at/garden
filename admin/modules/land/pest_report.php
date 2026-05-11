<?php
// modules/land/pest_report.php — Fn 6: Pest & Disease Alert, Fn 7: Compliance Audit
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Pest & Disease Reports';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/PestReportController.php';

$controller = new PestReportController();
$controller->index($user);
