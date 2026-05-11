<?php
require_once __DIR__ . '/../core/Model.php';

class PenaltyModel extends Model {
    public function resolvePenalty($penId, $type, $hours) {
        $this->db->prepare("UPDATE tool_penalties SET penalty_type=?,service_hours=?,status='served' WHERE id=?")
                 ->execute([$type, $hours, $penId]);
    }

    public function getUserIdFromPenalty($penId) {
        $stmt = $this->db->prepare("SELECT user_id FROM tool_penalties WHERE id=?");
        $stmt->execute([$penId]);
        return $stmt->fetchColumn();
    }

    public function addServiceHours($userId, $hours, $desc, $month) {
        $this->db->prepare("INSERT INTO service_hours (user_id,hours_logged,activity_description,month_year,status) VALUES (?,?,?,?,'approved')")
                 ->execute([$userId, $hours, $desc, $month]);
    }

    public function getAllPenalties() {
        return $this->db->query("
            SELECT tp.*, t.name AS tool_name, u.full_name, u.email, r.due_date, r.returned_at
            FROM tool_penalties tp
            LEFT JOIN tool_reservations r ON tp.reservation_id = r.id
            LEFT JOIN tools t ON r.tool_id = t.id
            JOIN users u ON tp.user_id = u.id
            ORDER BY tp.issued_at DESC
        ")->fetchAll();
    }

    public function getUserPenalties($userId) {
        $stmt = $this->db->prepare("
            SELECT tp.*, t.name AS tool_name, u.full_name, r.due_date, r.returned_at
            FROM tool_penalties tp
            LEFT JOIN tool_reservations r ON tp.reservation_id = r.id
            LEFT JOIN tools t ON r.tool_id = t.id
            JOIN users u ON tp.user_id = u.id
            WHERE tp.user_id = ? ORDER BY tp.issued_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function addManualPenalty($userId, $fineAmount, $serviceHours, $reason) {
        $this->db->prepare("
            INSERT INTO tool_penalties
                (reservation_id, user_id, days_late, penalty_type, fine_amount, service_hours, status, source, reason)
            VALUES
                (NULL, ?, 0, 'fine', ?, ?, 'pending', 'manual', ?)
        ")->execute([$userId, $fineAmount, $serviceHours, $reason]);
        return $this->db->lastInsertId();
    }

    public function getActiveUsers() {
        return $this->db->query("
            SELECT u.id, u.full_name, r.name AS role_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.is_active = 1
            ORDER BY u.full_name
        ")->fetchAll();
    }
}
