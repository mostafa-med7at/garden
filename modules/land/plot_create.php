<?php
// modules/land/plot_create.php — Fn 1: Grid-based plot creation
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Add New Plot';
$db        = getDB();
// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/PlotController.php';

$controller = new PlotController();
$controller->create($user);
