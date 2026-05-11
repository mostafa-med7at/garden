<?php
// modules/land/plot_detail.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Plot Details';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/PlotController.php';

$controller = new PlotController();
$controller->detail($user);
