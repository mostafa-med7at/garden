<?php
// modules/land/plots.php — Fn 1: Grid Plot Map  |  Fn 2: Billing preview
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';

// Public access — guests may view
$user      = currentUser();
$pageTitle = 'Garden Plot Map';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/PlotController.php';

$controller = new PlotController();
$controller->index($user);
