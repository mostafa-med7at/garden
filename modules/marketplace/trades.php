<?php
// modules/marketplace/trades.php — Fn 24: Flash Trade, Fn 25: Karma, Fn 26: Allergen Guard, Fn 28: Quality Rating
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user      = requireLogin();
$pageTitle = 'Harvest Marketplace';
$db        = getDB();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/TradeController.php';

$controller = new TradeController();
$controller->index($user);
