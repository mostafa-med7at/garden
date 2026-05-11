<?php
// modules/volunteer/shifts.php — Fn 19: Shift Substitution Workflow
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Shift Schedule';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/ShiftController.php';

$controller = new ShiftController();
$controller->index($user);
