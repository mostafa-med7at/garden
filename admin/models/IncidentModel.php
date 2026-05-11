<?php
require_once __DIR__ . '/../core/Model.php';

class IncidentModel extends Model {
    public function logGateAccess($code, $accessType) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE gate_code=? AND is_active=1");
        $stmt->execute([$code]); 
        $found = $stmt->fetch();
        
        $valid = $found ? 1 : 0;
        $uid   = $found ? $found['id'] : null;
        
        $this->db->prepare("INSERT INTO access_log (user_id,gate_code_entered,is_valid,access_type) VALUES (?,?,?,?)")
                 ->execute([$uid, $code, $valid, $accessType]);
                 
        return $valid;
    }

    public function reportIncident($reporterId, $title, $description, $location, $severity, $status) {
        $this->db->prepare("INSERT INTO incidents (reported_by,title,description,location,severity,status) VALUES (?,?,?,?,?,?)")
                 ->execute([$reporterId, $title, $description, $location, $severity, $status]);
        return (int)$this->db->lastInsertId();
    }

    public function updateIncidentStatus($incId, $status, $resolverId) {
        if ($status === 'resolved') {
            $this->db->prepare("UPDATE incidents SET status=?, resolved_by=?, resolved_at=NOW() WHERE id=?")
                     ->execute([$status, $resolverId, $incId]);
        } else {
            $this->db->prepare("UPDATE incidents SET status=? WHERE id=?")
                     ->execute([$status, $incId]);
        }
    }

    public function getAllIncidents() {
        return $this->db->query("SELECT i.*, u.full_name AS reporter, r.full_name AS resolver FROM incidents i JOIN users u ON i.reported_by=u.id LEFT JOIN users r ON i.resolved_by=r.id ORDER BY i.severity='critical' DESC, i.reported_at DESC")->fetchAll();
    }

    public function getAccessLogs($limit = 30) {
        $limit = (int)$limit;
        return $this->db->query("SELECT a.*, u.full_name FROM access_log a LEFT JOIN users u ON a.user_id=u.id ORDER BY a.accessed_at DESC LIMIT {$limit}")->fetchAll();
    }
}
