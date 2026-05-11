<?php
// modules/land/leases.php — Fn 5: Lease Renewal & Eviction Workflow
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Lease Management';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/LeaseController.php';

$controller = new LeaseController();
$controller->index($user);
