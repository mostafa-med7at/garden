<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/constants.php';
$user      = requireLogin();
$pageTitle = 'Dashboard';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/controllers/DashboardController.php';

$controller = new DashboardController();
$controller->index($user);
