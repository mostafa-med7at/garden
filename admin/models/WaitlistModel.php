<?php
require_once __DIR__ . '/../core/Model.php';

class WaitlistModel extends Model {
    public function isUserOnWaitlist($userId) {
        $chk = $this->db->prepare("SELECT id FROM waitlist WHERE user_id=?");
        $chk->execute([$userId]);
        return $chk->fetch();
    }

    public function joinWaitlist($userId, $score) {
        $stmt = $this->db->prepare("INSERT INTO waitlist (user_id, priority_score) VALUES (?,?)");
        $stmt->execute([$userId, $score]);
        return $this->db->lastInsertId();
    }

    public function leaveWaitlist($userId) {
        $this->db->prepare("DELETE FROM waitlist WHERE user_id=?")->execute([$userId]);
    }

    public function getTopWaitingMember() {
        return $this->db->query("
            SELECT w.*, u.full_name, u.email
            FROM waitlist w JOIN users u ON w.user_id = u.id
            WHERE w.status='waiting'
            ORDER BY w.priority_score DESC
            LIMIT 1")->fetch();
    }

    public function notifyTopMember($id) {
        $this->db->prepare("UPDATE waitlist SET status='notified', notified_at=NOW() WHERE id=?")->execute([$id]);
    }

    public function respond($userId, $response) {
        $this->db->prepare("UPDATE waitlist SET status=? WHERE user_id=?")->execute([$response, $userId]);
    }

    public function getAllWaitlistItems() {
        $waitlistItems = $this->db->query("
            SELECT w.*, u.full_name, u.email, u.community_points, u.residency_months
            FROM waitlist w
            JOIN users u ON w.user_id = u.id
            ORDER BY w.priority_score DESC
        ")->fetchAll();

        foreach ($waitlistItems as &$wItem) {
            $wItem['status'] = $wItem['status'] ?? 'waiting';
            $wItem['full_name'] = $wItem['full_name'] ?? 'Unknown User';
            $wItem['community_points'] = $wItem['community_points'] ?? 0;
            $wItem['residency_months'] = $wItem['residency_months'] ?? 0;
            $wItem['priority_score'] = $wItem['priority_score'] ?? 0;
        }
        unset($wItem);

        return $waitlistItems;
    }

    public function getAvailablePlotsCount() {
        return (int)$this->db->query("SELECT COUNT(*) FROM plots WHERE status='available'")->fetchColumn();
    }
}
