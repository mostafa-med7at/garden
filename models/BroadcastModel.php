<?php
require_once __DIR__ . '/../core/Model.php';

class BroadcastModel extends Model {
    public function logFalseAlarm($adminId, $title, $message, $siteStatus) {
        $this->db->prepare("INSERT INTO broadcasts (admin_id,title,message,site_status,is_false_alarm) VALUES (?,?,?,?,1)")
                 ->execute([$adminId, $title, 'FALSE ALARM: '.$message, $siteStatus]);
        return (int)$this->db->lastInsertId();
    }

    public function sendBroadcast($adminId, $title, $message, $affected, $siteStatus) {
        $this->db->prepare("INSERT INTO broadcasts (admin_id,title,message,affected_plots,site_status) VALUES (?,?,?,?,?)")
                 ->execute([$adminId, $title, $message, $affected, $siteStatus]);
        return (int)$this->db->lastInsertId();
    }

    public function updatePlotStatus($codes, $status) {
        if (empty($codes)) return;
        $stmt = $this->db->prepare("UPDATE plots SET status=? WHERE plot_code=?");
        foreach ($codes as $code) {
            $stmt->execute([$status, $code]);
        }
    }

    public function getActiveMemberCount() {
        return (int)$this->db->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn();
    }

    public function getRecentBroadcasts($limit = 20) {
        $limit = (int)$limit;
        return $this->db->query("SELECT b.*, u.full_name FROM broadcasts b JOIN users u ON b.admin_id=u.id ORDER BY b.sent_at DESC LIMIT {$limit}")->fetchAll();
    }
}
