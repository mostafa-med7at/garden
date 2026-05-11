<?php
require_once __DIR__ . '/../core/Model.php';

class ToolModel extends Model {
    public function getToolStatus($id) {
        $stmt = $this->db->prepare("SELECT status FROM tools WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }

    public function updateToolStatus($id, $status) {
        $this->db->prepare("UPDATE tools SET status=? WHERE id=?")->execute([$status, $id]);
    }

    public function logToolStateChange($toolId, $changedBy, $old, $new, $notes) {
        $this->db->prepare("INSERT INTO tool_state_log (tool_id,changed_by,old_status,new_status,notes) VALUES (?,?,?,?,?)")
                 ->execute([$toolId, $changedBy, $old, $new, $notes]);
    }

    public function checkReservationConflict($toolId, $slotDate, $slotStart, $slotEnd, $excludeResId = null) {
        $sql = "SELECT id FROM tool_reservations WHERE tool_id=? AND slot_date=? AND status='confirmed' AND NOT (slot_end <= ? OR slot_start >= ?)";
        $params = [$toolId, $slotDate, $slotStart, $slotEnd];
        if ($excludeResId) {
            $sql .= " AND id!=?";
            $params[] = $excludeResId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function createReservation($toolId, $userId, $slotDate, $slotStart, $slotEnd, $due) {
        $this->db->prepare("INSERT INTO tool_reservations (tool_id,user_id,slot_date,slot_start,slot_end,due_date,status) VALUES (?,?,?,?,?,?,'confirmed')")
                 ->execute([$toolId, $userId, $slotDate, $slotStart, $slotEnd, $due]);
        return $this->db->lastInsertId();
    }

    public function getReservation($resId, $userId) {
        $stmt = $this->db->prepare("SELECT r.*, t.name AS tool_name FROM tool_reservations r JOIN tools t ON r.tool_id=t.id WHERE r.id=? AND r.user_id=?");
        $stmt->execute([$resId, $userId]);
        return $stmt->fetch();
    }

    public function cancelReservation($resId) {
        $this->db->prepare("UPDATE tool_reservations SET status='cancelled' WHERE id=?")->execute([$resId]);
    }

    public function rescheduleReservation($resId, $slotDate, $slotStart, $slotEnd, $due) {
        $this->db->prepare("UPDATE tool_reservations SET slot_date=?, slot_start=?, slot_end=?, due_date=? WHERE id=?")
                 ->execute([$slotDate, $slotStart, $slotEnd, $due, $resId]);
    }

    public function returnTool($resId, $toolId) {
        $this->db->prepare("UPDATE tool_reservations SET status='completed', returned_at=NOW() WHERE id=?")->execute([$resId]);
        $this->db->prepare("UPDATE tools SET status='available', total_usage_hours=total_usage_hours+1 WHERE id=?")->execute([$toolId]);
    }

    public function getToolById($id) {
        $stmt = $this->db->prepare("SELECT * FROM tools WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function markToolMaintenance($toolId) {
        $this->db->prepare("UPDATE tools SET needs_maintenance=1 WHERE id=?")->execute([$toolId]);
    }

    public function addPenalty($resId, $userId, $daysLate, $fine, $serviceHours) {
        $this->db->prepare("INSERT INTO tool_penalties (reservation_id,user_id,days_late,penalty_type,fine_amount,service_hours) VALUES (?,?,?,?,?,?)")
                 ->execute([$resId, $userId, $daysLate, 'fine', $fine, $serviceHours]);
    }

    public function reportDamage($toolId, $userId, $desc) {
        $this->db->prepare("INSERT INTO damage_reports (tool_id,reported_by,description,status) VALUES (?,?,?,'pending')")
                 ->execute([$toolId, $userId, $desc]);
        return $this->db->lastInsertId();
    }

    public function reviewDamage($repId, $type, $fee, $exempt, $adminId) {
        $this->db->prepare("UPDATE damage_reports SET damage_type=?,repair_fee=?,is_exempt=?,admin_reviewed_by=?,reviewed_at=NOW(),status='reviewed' WHERE id=?")
                 ->execute([$type, $fee, $exempt, $adminId, $repId]);
    }

    public function getDamageReportToolId($repId) {
        $stmt = $this->db->prepare("SELECT tool_id FROM damage_reports WHERE id=?");
        $stmt->execute([$repId]);
        return $stmt->fetchColumn();
    }

    public function addTool($name, $desc, $threshold, $mediaLinks) {
        $this->db->prepare("INSERT INTO tools (name,description,maintenance_threshold_hours,media_links) VALUES (?,?,?,?)")
                 ->execute([$name, $desc, $threshold, $mediaLinks]);
        return $this->db->lastInsertId();
    }

    public function getAllTools() {
        $tools = $this->db->query("SELECT * FROM tools ORDER BY status, name")->fetchAll();
        foreach ($tools as &$t) {
            $t['status'] = $t['status'] ?? 'available';
            $t['name'] = $t['name'] ?? 'Unknown Tool';
            $t['description'] = $t['description'] ?? '';
            $t['media_links'] = $t['media_links'] ?? '';
            $t['total_usage_hours'] = $t['total_usage_hours'] ?? 0;
            $t['maintenance_threshold_hours'] = max(1, (int)($t['maintenance_threshold_hours'] ?? 50));
            $t['needs_maintenance'] = $t['needs_maintenance'] ?? 0;
        }
        unset($t);
        return $tools;
    }

    public function getMyReservations($userId) {
        $stmt = $this->db->prepare("SELECT r.*, t.name AS tool_name FROM tool_reservations r JOIN tools t ON r.tool_id=t.id WHERE r.user_id=? AND r.status='confirmed' ORDER BY r.slot_date");
        $stmt->execute([$userId]);
        $myRes = $stmt->fetchAll();
        foreach ($myRes as &$r) { $r['tool_name'] = $r['tool_name'] ?? 'Unknown Tool'; }
        unset($r);
        return $myRes;
    }

    public function getPendingPenalties($userId) {
        $stmt = $this->db->prepare("SELECT tp.*, r.tool_id, t.name AS tool_name FROM tool_penalties tp JOIN tool_reservations r ON tp.reservation_id=r.id JOIN tools t ON r.tool_id=t.id WHERE tp.user_id=? AND tp.status='pending'");
        $stmt->execute([$userId]);
        $penalties = $stmt->fetchAll();
        foreach ($penalties as &$p) { $p['tool_name'] = $p['tool_name'] ?? 'Unknown Tool'; }
        unset($p);
        return $penalties;
    }

    public function getAllDamageReports() {
        $damageRep = $this->db->query("SELECT dr.*, t.name AS tool_name, u.full_name AS reporter FROM damage_reports dr JOIN tools t ON dr.tool_id=t.id JOIN users u ON dr.reported_by=u.id ORDER BY dr.reported_at DESC")->fetchAll();
        foreach ($damageRep as &$dr) {
            $dr['tool_name'] = $dr['tool_name'] ?? 'Unknown Tool';
            $dr['reporter'] = $dr['reporter'] ?? 'Unknown User';
        }
        unset($dr);
        return $damageRep;
    }

    public function getStateLogGrouped() {
        $rows = $this->db->query(
            "SELECT sl.*, u.full_name AS changed_by_name
             FROM tool_state_log sl
             LEFT JOIN users u ON sl.changed_by = u.id
             ORDER BY sl.changed_at DESC"
        )->fetchAll();
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['tool_id']][] = $row;
        }
        return $grouped;
    }
}
