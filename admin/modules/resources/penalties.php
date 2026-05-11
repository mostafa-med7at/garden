<?php
// modules/resources/penalties.php — Fn 16: Late Return Penalty Engine
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Tool Penalties';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/PenaltyController.php';

$controller = new PenaltyController();
$controller->index($user);
