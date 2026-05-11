<?php
require_once __DIR__ . '/../core/Model.php';

class AuditModel extends Model {
    public function changeUserRole($userId, $newRoleId) {
        $this->db->prepare("UPDATE users SET role_id=? WHERE id=?")->execute([$newRoleId, $userId]);
    }

    public function toggleUserActive($userId) {
        $this->db->prepare("UPDATE users SET is_active = NOT is_active WHERE id=?")->execute([$userId]);
    }

    public function getAuditLogs($action, $userId, $date, $module, $limit = 200) {
        $where  = ['1=1'];
        $params = [];
        
        if ($action) { 
            $where[] = 'al.action_type LIKE ?'; 
            $params[] = "%$action%"; 
        }
        if ($userId) { 
            $where[] = 'al.user_id = ?';        
            $params[] = (int)$userId; 
        }
        if ($date) { 
            $where[] = 'DATE(al.logged_at) = ?';
            $params[] = $date; 
        }
        if ($module) { 
            $where[] = 'al.module = ?';          
            $params[] = $module; 
        }

        $whereStr = implode(' AND ', $where);
        $limit = (int)$limit;
        
        $stmt = $this->db->prepare("
            SELECT al.*, u.full_name FROM audit_log al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE $whereStr
            ORDER BY al.logged_at DESC LIMIT $limit
        ");
        $stmt->execute($params); 
        return $stmt->fetchAll();
    }

    public function getAllUsersWithRoles() {
        return $this->db->query("
            SELECT u.*, r.name AS role_name 
            FROM users u JOIN roles r ON u.role_id=r.id 
            ORDER BY u.is_active DESC, r.id, u.full_name
        ")->fetchAll();
    }

    public function getAllRoles() {
        return $this->db->query("SELECT * FROM roles ORDER BY id")->fetchAll();
    }

    public function getAuditStats() {
        $totalLogs    = $this->db->query("SELECT COUNT(*) FROM audit_log")->fetchColumn();
        $logsToday    = $this->db->query("SELECT COUNT(*) FROM audit_log WHERE DATE(logged_at)=CURDATE()")->fetchColumn();
        $failedLogins = $this->db->query("SELECT COUNT(*) FROM access_log WHERE is_valid=0")->fetchColumn();
        
        return [
            'totalLogs' => (int)$totalLogs,
            'logsToday' => (int)$logsToday,
            'failedLogins' => (int)$failedLogins
        ];
    }
}
