<?php
// modules/resources/consumables.php — Fn 14: Consumable Inventory Monitor
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Consumables Inventory';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/ConsumableController.php';

$controller = new ConsumableController();
$controller->index($user);
