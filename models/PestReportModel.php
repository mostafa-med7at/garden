<?php
require_once __DIR__ . '/../core/Model.php';

class PestReportModel extends Model {
    public function getMyPlot($userId) {
        $stmt = $this->db->prepare("SELECT p.* FROM plots p JOIN leases l ON l.plot_id=p.id WHERE l.user_id=? AND l.status='active' LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function getAllPlots() {
        return $this->db->query("SELECT * FROM plots ORDER BY plot_code")->fetchAll();
    }

    public function createReport($plotId, $userId, $pestType, $severity, $transmit, $desc) {
        $stmt = $this->db->prepare("INSERT INTO pest_reports (plot_id, reported_by, pest_type, severity, is_transmissible, description) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$plotId, $userId, $pestType, $severity, $transmit, $desc]);
        return (int)$this->db->lastInsertId();
    }

    public function getPlotCode($plotId) {
        $stmt = $this->db->prepare("SELECT plot_code FROM plots WHERE id=?");
        $stmt->execute([$plotId]);
        return $stmt->fetchColumn();
    }

    public function getNeighborPlots($plotId, $prefix) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT u.id, u.full_name, u.email, p.plot_code
            FROM plots p
            JOIN leases l ON l.plot_id=p.id AND l.status='active'
            JOIN users u ON l.user_id=u.id
            WHERE p.id != ? AND p.plot_code LIKE ?
        ");
        $stmt->execute([$plotId, $prefix . '%']);
        return $stmt->fetchAll();
    }

    public function createInspection($plotId, $wardenId, $notes, $photoPaths, $result, $violDet, $penalty) {
        $stmt = $this->db->prepare("INSERT INTO inspections (plot_id, warden_id, notes, photo_paths, result, violation_details, penalty_applied) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$plotId, $wardenId, $notes, json_encode($photoPaths), $result, $violDet, $penalty]);
        return (int)$this->db->lastInsertId();
    }

    public function updateCompliance($plotId, $compStatus) {
        $this->db->prepare("UPDATE plots SET compliance_status=? WHERE id=?")->execute([$compStatus, $plotId]);
    }

    public function getAllReports() {
        return $this->db->query("SELECT r.*, p.plot_code, u.full_name FROM pest_reports r JOIN plots p ON r.plot_id=p.id JOIN users u ON r.reported_by=u.id ORDER BY r.reported_at DESC")->fetchAll();
    }

    public function getAllInspections() {
        return $this->db->query("SELECT i.*, p.plot_code, u.full_name AS warden_name FROM inspections i JOIN plots p ON i.plot_id=p.id JOIN users u ON i.warden_id=u.id ORDER BY i.inspected_at DESC")->fetchAll();
    }
}
