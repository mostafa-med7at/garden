<?php
// modules/volunteer/tasks.php — Fn 17: Communal Task Weighting, Fn 18: Service Hour Tracker
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Tasks & Volunteer Hours';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/TaskController.php';

$controller = new TaskController();
$controller->index($user);
