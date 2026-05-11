<?php
// controllers/NotificationController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class NotificationController extends Controller {

    private $model;

    public function __construct() {
        $this->model = new NotificationModel();
    }

    public function index($user) {
        if ($user['role_name'] !== 'admin') {
            header('Location: /garden/index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Send custom notification
            if (isset($_POST['send_custom'])) {
                $recipients = $_POST['recipients'] ?? 'all';
                $subject    = trim($_POST['subject']);
                $body       = trim($_POST['body']);
                $roleId     = (int)($_POST['role_filter'] ?? 0);
                $singleUid  = (int)($_POST['single_user_id'] ?? 0);

                if ($subject && $body) {
                    $uids = $this->model->getActiveUserIds($recipients, $roleId, $singleUid);
                    $sentOk = 0;
                    foreach ($uids as $uid) {
                        if ($this->model->sendNotification($uid, $subject, "<p>$body</p>", 'custom')) $sentOk++;
                    }
                    auditLog('notifications_sent', 'admin', 'notifications_log', null, "Custom: $subject → $sentOk sent");
                    setFlash('success', "Notification sent to $sentOk recipient(s).");
                } else {
                    setFlash('danger', 'Subject and body are required.');
                }
                header('Location: index.php'); exit;
            }

            // Send waitlist notification
            if (isset($_POST['notify_waitlist'])) {
                $uid = (int)$_POST['waitlist_user_id'];
                $plotCode = trim($_POST['plot_code']);
                $subject  = "Good news — A plot is available! ($plotCode)";
                $body     = "<p>A garden plot (<strong>$plotCode</strong>) has become available and you're next on the waitlist! Please log in to the portal to accept or decline within 48 hours.</p>";
                $sent = $this->model->sendNotification($uid, $subject, $body, 'waitlist');
                
                if ($sent) {
                    $this->model->markWaitlistNotified($uid);
                    auditLog('waitlist_notified', 'admin', 'waitlist', $uid, "Notified about $plotCode");
                    setFlash('success', 'Waitlist notification sent.');
                } else {
                    setFlash('danger', 'Email could not be delivered. Check XAMPP mail config.');
                }
                header('Location: index.php'); exit;
            }

            // Send pest alert
            if (isset($_POST['send_pest_alert'])) {
                $reportId = (int)$_POST['report_id'];
                $pest = $this->model->getPestReportById($reportId);
                
                if ($pest && $pest['is_transmissible']) {
                    $owners = $this->model->getActivePlotOwners();
                    $subject = "⚠️ Pest Alert: {$pest['pest_type']} reported near plot {$pest['plot_code']}";
                    $body    = "<p>A transmissible pest/disease has been reported on plot <strong>{$pest['plot_code']}</strong>.</p><p><strong>Type:</strong> {$pest['pest_type']}<br><strong>Severity:</strong> {$pest['severity']}</p><p>Please inspect your plot and report any signs immediately.</p>";
                    $sentOk = 0;
                    foreach ($owners as $uid) {
                        if ($this->model->sendNotification($uid, $subject, $body, 'pest_alert')) $sentOk++;
                    }
                    auditLog('pest_alert_sent', 'admin', 'pest_reports', $reportId, "Alert sent to $sentOk plot owners");
                    setFlash('success', "Pest alert sent to $sentOk plot owners.");
                } else {
                    setFlash('warning', 'Pest report not found or not marked as transmissible.');
                }
                header('Location: index.php'); exit;
            }
        }

        $roles         = $this->model->getRoles();
        $allUsers      = $this->model->getAllActiveUsers();
        $waitlistItems = $this->model->getWaitingList();
        $pestAlerts    = $this->model->getPestAlerts();
        $recentLogs    = $this->model->getRecentLogs();
        $pageTitle     = 'Email & Notifications';

        $this->view('notifications_index', [
            'user' => $user,
            'pageTitle' => $pageTitle,
            'roles' => $roles,
            'allUsers' => $allUsers,
            'waitlistItems' => $waitlistItems,
            'pestAlerts' => $pestAlerts,
            'recentLogs' => $recentLogs
        ]);
    }

    public function myNotifications($user) {
        $this->model->markAllAsRead($user['id']);
        $notifications = $this->model->getMyNotifications($user['id']);
        $pageTitle = 'My Notifications';
        
        $this->view('notifications_my', [
            'user' => $user,
            'pageTitle' => $pageTitle,
            'notifications' => $notifications
        ]);
    }
}
