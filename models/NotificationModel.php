<?php
// models/NotificationModel.php

require_once __DIR__ . '/../core/Model.php';

class NotificationModel extends Model {

    public function __construct() {
        parent::__construct();
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        $this->db->exec("CREATE TABLE IF NOT EXISTS notifications_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            subject VARCHAR(255) NOT NULL,
            body TEXT,
            type VARCHAR(50) DEFAULT 'general',
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('sent','failed','pending') DEFAULT 'pending',
            is_read TINYINT(1) DEFAULT 0,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )");
        try {
            $this->db->exec("ALTER TABLE notifications_log ADD COLUMN is_read TINYINT(1) DEFAULT 0");
        } catch (PDOException $e) {}
    }

    public function sendNotification($recipientId, $subject, $body, $type = 'general') {
        $stmt = $this->db->prepare("SELECT email, full_name FROM users WHERE id=? AND is_active=1");
        $stmt->execute([$recipientId]);
        $recipient = $stmt->fetch();
        if (!$recipient) return false;

        $to      = $recipient['email'];
        $name    = $recipient['full_name'];
        $headers = implode("\r\n", [
            'From: ' . APP_NAME . ' <noreply@garden.local>',
            'Reply-To: noreply@garden.local',
            'Content-Type: text/html; charset=UTF-8',
            'MIME-Version: 1.0',
            'X-Mailer: PHP/' . phpversion(),
        ]);

        $htmlBody = $this->buildEmailTemplate($name, $subject, $body);
        $sent = @mail($to, $subject, $htmlBody, $headers);

        $this->db->prepare("INSERT INTO notifications_log (user_id, subject, body, type, sent_at, status) VALUES (?,?,?,?,NOW(),?)")
           ->execute([$recipientId, $subject, strip_tags($body), $type, $sent ? 'sent' : 'failed']);

        return $sent;
    }

    private function buildEmailTemplate($name, $subject, $body) {
        $appName = APP_NAME;
        $appUrl  = APP_URL;
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Inter, Arial, sans-serif; background:#f5f5f5; margin:0; padding:20px; }
    .container { max-width:600px; margin:0 auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.1); }
    .header { background:#2d6a4f; padding:24px 32px; color:#fff; }
    .header h1 { margin:0; font-size:1.3rem; }
    .header p { margin:4px 0 0; opacity:.8; font-size:.9rem; }
    .content { padding:28px 32px; color:#333; line-height:1.6; }
    .content h2 { color:#2d6a4f; margin-top:0; }
    .footer { background:#f0f0f0; padding:16px 32px; text-align:center; font-size:.8rem; color:#888; }
    .btn { display:inline-block; padding:10px 22px; background:#2d6a4f; color:#fff; text-decoration:none; border-radius:6px; font-weight:600; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>🌱 {$appName}</h1>
      <p>Community Garden Management System</p>
    </div>
    <div class="content">
      <h2>{$subject}</h2>
      <p>Dear {$name},</p>
      {$body}
      <p style="margin-top:24px"><a href="{$appUrl}" class="btn">Visit Garden Portal</a></p>
    </div>
    <div class="footer">
      This email was sent by {$appName}. Please do not reply to this address.
    </div>
  </div>
</body>
</html>
HTML;
    }

    public function getActiveUserIds($recipients = 'all', $roleId = 0, $singleUserId = 0) {
        if ($recipients === 'all') {
            $ustmt = $this->db->query("SELECT id FROM users WHERE is_active=1");
        } elseif ($recipients === 'role' && $roleId) {
            $ustmt = $this->db->prepare("SELECT id FROM users WHERE role_id=? AND is_active=1");
            $ustmt->execute([$roleId]);
        } elseif ($recipients === 'single' && $singleUserId) {
            $ustmt = $this->db->prepare("SELECT id FROM users WHERE id=? AND is_active=1");
            $ustmt->execute([$singleUserId]);
        } else {
            $ustmt = $this->db->query("SELECT id FROM users WHERE is_active=1");
        }
        return $ustmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getActivePlotOwners() {
        return $this->db->query("SELECT DISTINCT u.id FROM users u JOIN leases l ON u.id=l.user_id WHERE l.status='active'")->fetchAll(PDO::FETCH_COLUMN);
    }

    public function markWaitlistNotified($uid) {
        return $this->db->prepare("UPDATE waitlist SET status='notified', notified_at=NOW() WHERE user_id=?")->execute([$uid]);
    }

    public function getPestReportById($id) {
        $stmt = $this->db->prepare("SELECT pr.*, p.plot_code FROM pest_reports pr JOIN plots p ON pr.plot_id=p.id WHERE pr.id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getRoles() {
        return $this->db->query("SELECT * FROM roles ORDER BY id")->fetchAll();
    }

    public function getAllActiveUsers() {
        return $this->db->query("SELECT id, full_name, email, role_id FROM users WHERE is_active=1 ORDER BY full_name")->fetchAll();
    }

    public function getWaitingList() {
        return $this->db->query("SELECT w.*, u.full_name, u.email FROM waitlist w JOIN users u ON w.user_id=u.id WHERE w.status='waiting' ORDER BY w.priority_score DESC LIMIT 10")->fetchAll();
    }

    public function getPestAlerts() {
        return $this->db->query("SELECT pr.*, p.plot_code, u.full_name FROM pest_reports pr JOIN plots p ON pr.plot_id=p.id JOIN users u ON pr.reported_by=u.id WHERE pr.is_transmissible=1 AND pr.status='open' ORDER BY pr.reported_at DESC")->fetchAll();
    }

    public function getRecentLogs() {
        return $this->db->query("SELECT nl.*, u.full_name FROM notifications_log nl LEFT JOIN users u ON nl.user_id=u.id ORDER BY nl.sent_at DESC LIMIT 50")->fetchAll();
    }

    public function getMyNotifications($userId) {
        $stmt = $this->db->prepare("SELECT * FROM notifications_log WHERE user_id = ? ORDER BY sent_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function markAllAsRead($userId) {
        $this->ensureTableExists();
        return $this->db->prepare("UPDATE notifications_log SET is_read = 1 WHERE user_id = ? AND is_read = 0")->execute([$userId]);
    }
}
