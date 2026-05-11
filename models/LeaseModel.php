<?php
require_once __DIR__ . '/../core/Model.php';

class LeaseModel extends Model {
    public function expireOverdueLeases() {
        $expired = $this->db->query("UPDATE leases SET status='expired' WHERE status='active' AND end_date < CURDATE()")->rowCount();
        $this->db->query("UPDATE plots p JOIN leases l ON l.plot_id=p.id SET p.status='available' WHERE l.status='expired'");
        return $expired;
    }

    public function getLeaseByIdAndUser($leaseId, $userId) {
        $stmt = $this->db->prepare("SELECT l.*, p.area_sqm, p.soil_quality FROM leases l JOIN plots p ON l.plot_id=p.id WHERE l.id=? AND l.user_id=?");
        $stmt->execute([$leaseId, $userId]);
        return $stmt->fetch();
    }

    public function renewLease($leaseId, $userId, $feeAmount, $method, $newEnd) {
        $this->db->prepare("INSERT INTO billing_transactions (lease_id,user_id,amount,payment_method,status) VALUES (?,?,?,'$method','paid')")
                 ->execute([$leaseId, $userId, $feeAmount]);
        $this->db->prepare("UPDATE leases SET end_date=?, status='active' WHERE id=?")->execute([$newEnd, $leaseId]);
    }

    public function terminateLease($leaseId) {
        $this->db->prepare("UPDATE leases SET status='terminated' WHERE id=?")->execute([$leaseId]);
        $this->db->prepare("UPDATE plots p JOIN leases l ON l.plot_id=p.id SET p.status='available' WHERE l.id=?")->execute([$leaseId]);
    }

    public function getAllLeases() {
        return $this->db->query("
            SELECT l.*, p.plot_code, p.area_sqm, p.soil_quality, u.full_name, u.email,
                   DATEDIFF(l.end_date, CURDATE()) AS days_left
            FROM leases l JOIN plots p ON l.plot_id=p.id JOIN users u ON l.user_id=u.id
            ORDER BY l.status, l.end_date ASC
        ")->fetchAll();
    }

    public function getUserLeases($userId) {
        $stmt = $this->db->prepare("
            SELECT l.*, p.plot_code, p.area_sqm, p.soil_quality, u.full_name, u.email,
                   DATEDIFF(l.end_date, CURDATE()) AS days_left
            FROM leases l JOIN plots p ON l.plot_id=p.id JOIN users u ON l.user_id=u.id
            WHERE l.user_id=? ORDER BY l.end_date DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getAvailablePlots() {
        return $this->db->query("SELECT * FROM plots WHERE status='available' ORDER BY plot_code")->fetchAll();
    }

    public function getPlotByIdAvailable($plotId) {
        $stmt = $this->db->prepare("SELECT * FROM plots WHERE id=? AND status='available'");
        $stmt->execute([$plotId]);
        return $stmt->fetch();
    }

    public function getMembersForAdmin() {
        return $this->db->query("SELECT id, full_name, email, membership_status FROM users WHERE role_id IN (3,4) ORDER BY full_name")->fetchAll();
    }

    public function getUserById($userId) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function createLease($plotId, $userId, $start, $end, $baseFee, $multiplier, $discountPct, $total, $method) {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("INSERT INTO leases (plot_id,user_id,start_date,end_date,base_fee,soil_multiplier,membership_discount,total_fee,status)
                          VALUES (?,?,?,?,?,?,?,?,'active')")
               ->execute([$plotId, $userId, $start, $end, $baseFee, $multiplier, $discountPct, $total]);
            $leaseId = (int)$this->db->lastInsertId();

            $this->db->prepare("INSERT INTO billing_transactions (lease_id,user_id,amount,payment_method,status) VALUES (?,?,?,?,'paid')")
               ->execute([$leaseId, $userId, $total, $method]);

            $this->db->prepare("UPDATE plots SET status='occupied' WHERE id=?")->execute([$plotId]);
            $this->db->prepare("UPDATE waitlist SET status='accepted' WHERE user_id=?")->execute([$userId]);

            $this->db->commit();
            return $leaseId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
