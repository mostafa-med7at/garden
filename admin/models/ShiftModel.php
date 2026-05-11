<?php
require_once __DIR__ . '/../core/Model.php';

class ShiftModel extends Model {
    public function createShift($title, $date, $start, $end, $assignTo) {
        $this->db->prepare("INSERT INTO shifts (title,shift_date,start_time,end_time,assigned_to) VALUES (?,?,?,?,?)")
                 ->execute([$title, $date, $start, $end, $assignTo]);
        return $this->db->lastInsertId();
    }

    public function checkPendingSwap($shiftId, $requesterId) {
        $chk = $this->db->prepare("SELECT id FROM shift_swap_requests WHERE shift_id=? AND requester_id=? AND status='pending'");
        $chk->execute([$shiftId, $requesterId]);
        return $chk->fetch();
    }

    public function requestSwap($shiftId, $requesterId, $targetId, $expires) {
        $this->db->prepare("INSERT INTO shift_swap_requests (shift_id,requester_id,target_id,expires_at) VALUES (?,?,?,?)")
                 ->execute([$shiftId, $requesterId, $targetId, $expires]);
    }

    public function getSwapRequest($reqId, $targetId) {
        $stmt = $this->db->prepare("SELECT * FROM shift_swap_requests WHERE id=? AND target_id=?");
        $stmt->execute([$reqId, $targetId]);
        return $stmt->fetch();
    }

    public function respondToSwap($reqId, $response) {
        $this->db->prepare("UPDATE shift_swap_requests SET status=?,responded_at=NOW() WHERE id=?")
                 ->execute([$response, $reqId]);
    }

    public function swapShiftAssignment($shiftId, $newAssignee) {
        $this->db->prepare("UPDATE shifts SET assigned_to=? WHERE id=?")->execute([$newAssignee, $shiftId]);
    }

    public function getAllShifts() {
        return $this->db->query("SELECT s.*, u.full_name AS assignee FROM shifts s JOIN users u ON s.assigned_to=u.id ORDER BY s.shift_date DESC")->fetchAll();
    }

    public function getActiveMembers() {
        return $this->db->query("SELECT id,full_name FROM users WHERE is_active=1 ORDER BY full_name")->fetchAll();
    }

    public function getIncomingSwapRequests($targetId) {
        $stmt = $this->db->prepare("SELECT r.*, s.title AS shift_title, s.shift_date, u.full_name AS requester FROM shift_swap_requests r JOIN shifts s ON r.shift_id=s.id JOIN users u ON r.requester_id=u.id WHERE r.target_id=? AND r.status='pending'");
        $stmt->execute([$targetId]);
        return $stmt->fetchAll();
    }
}
