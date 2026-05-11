<?php
// modules/land/soil.php — Fn 3: Soil Health Lifecycle Tracker
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Soil Health Tracker';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/SoilController.php';

$controller = new SoilController();
$controller->index($user);
