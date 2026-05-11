<?php
require_once __DIR__ . '/../core/Model.php';

class CompostModel extends Model {
    public function logContribution($userId, $amount, $notes) {
        $this->db->prepare("INSERT INTO compost_contributions (user_id, amount_kg, notes) VALUES (?,?,?)")
                 ->execute([$userId, $amount, $notes]);
    }

    public function getMyTotal($userId) {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount_kg),0) FROM compost_contributions WHERE user_id=?");
        $stmt->execute([$userId]);
        return (float)$stmt->fetchColumn();
    }

    public function getLeaderboard($limit = 10) {
        $limit = (int)$limit;
        return $this->db->query("
            SELECT u.full_name, COALESCE(SUM(c.amount_kg),0) AS total_kg, COUNT(c.id) AS entries
            FROM users u LEFT JOIN compost_contributions c ON c.user_id=u.id
            GROUP BY u.id ORDER BY total_kg DESC LIMIT {$limit}
        ")->fetchAll();
    }

    public function getMyHistory($userId, $limit = 20) {
        $limit = (int)$limit;
        $stmt = $this->db->prepare("SELECT * FROM compost_contributions WHERE user_id=? ORDER BY contributed_at DESC LIMIT {$limit}");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getGrandTotal() {
        return (float)$this->db->query("SELECT COALESCE(SUM(amount_kg),0) FROM compost_contributions")->fetchColumn();
    }
}
