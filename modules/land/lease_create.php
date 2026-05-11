<?php
// modules/land/lease_create.php — Rent a plot
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Rent a Plot';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/LeaseController.php';

$controller = new LeaseController();
$controller->create($user);
