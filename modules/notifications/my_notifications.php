<?php
// modules/notifications/my_notifications.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/constants.php';
$user = requireLogin();

// --- MVC Routing ---
require_once __DIR__ . '/../../controllers/NotificationController.php';

$controller = new NotificationController();
$controller->myNotifications($user);

