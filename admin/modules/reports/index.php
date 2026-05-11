<?php
// modules/reports/index.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user = requireLogin();

require_once __DIR__ . '/../../controllers/ReportController.php';

$controller = new ReportController();
$controller->index($user);

