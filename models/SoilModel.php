<?php
require_once __DIR__ . '/../core/Model.php';

class SoilModel extends Model {
    public function getMyPlots($userId) {
        $stmt = $this->db->prepare("SELECT p.* FROM plots p JOIN leases l ON l.plot_id=p.id WHERE l.user_id=? AND l.status='active'");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getAllPlots() {
        return $this->db->query("SELECT * FROM plots ORDER BY plot_code")->fetchAll();
    }

    public function addSoilEvent($plotId, $userId, $eventType, $fertType, $ph, $crop, $notes, $atRisk) {
        $stmt = $this->db->prepare("
            INSERT INTO soil_events (plot_id, user_id, event_type, fertilizer_type, ph_level, crop_name, notes, is_at_risk)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$plotId, $userId, $eventType, $fertType, $ph, $crop, $notes, $atRisk]);
        return (int)$this->db->lastInsertId();
    }

    public function getEventsByPlot($plotId) {
        $stmt = $this->db->prepare("
            SELECT se.*, u.full_name FROM soil_events se
            JOIN users u ON se.user_id = u.id
            WHERE se.plot_id = ?
            ORDER BY se.recorded_at DESC
        ");
        $stmt->execute([$plotId]);
        return $stmt->fetchAll();
    }
}
