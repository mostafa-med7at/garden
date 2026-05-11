<?php
// modules/volunteer/incidents.php — Fn 22: Access Log, Fn 23: Incident Reporting
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Security & Incidents';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/IncidentController.php';

$controller = new IncidentController();
$controller->index($user);
