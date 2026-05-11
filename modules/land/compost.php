<?php
// modules/land/compost.php — Fn 8: Compost Contribution Tracker
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Compost Tracker';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/CompostController.php';

$controller = new CompostController();
$controller->index($user);
