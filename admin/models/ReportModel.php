<?php
// models/ReportModel.php

require_once __DIR__ . '/../core/Model.php';

class ReportModel extends Model {

    public function getMembersReport() {
        return $this->db->query("SELECT u.id, u.full_name, u.email, u.phone, r.name AS role_name, u.membership_status, u.community_points, u.karma_points, u.is_active, u.created_at FROM users u JOIN roles r ON u.role_id=r.id ORDER BY r.id, u.full_name")->fetchAll();
    }

    public function getLeasesReport($from, $to) {
        $stmt = $this->db->prepare("SELECT l.*, p.plot_code, p.area_sqm, u.full_name, u.email FROM leases l JOIN plots p ON l.plot_id=p.id JOIN users u ON l.user_id=u.id WHERE l.start_date <= ? AND l.end_date >= ? ORDER BY l.end_date");
        $stmt->execute([$to, $from]);
        return $stmt->fetchAll();
    }

    public function getBillingReport($from, $to) {
        $stmt = $this->db->prepare("SELECT bt.*, u.full_name, u.email, p.plot_code FROM billing_transactions bt JOIN users u ON bt.user_id=u.id JOIN leases l ON bt.lease_id=l.id JOIN plots p ON l.plot_id=p.id WHERE bt.payment_date BETWEEN ? AND ? ORDER BY bt.payment_date DESC");
        $stmt->execute([$from.' 00:00:00', $to.' 23:59:59']);
        return $stmt->fetchAll();
    }

    public function getToolsReport() {
        return $this->db->query("SELECT t.*, COUNT(r.id) AS total_res, SUM(CASE WHEN r.status='completed' THEN 1 ELSE 0 END) AS completed_res, SUM(CASE WHEN r.status='overdue' THEN 1 ELSE 0 END) AS overdue_res FROM tools t LEFT JOIN tool_reservations r ON t.id=r.tool_id GROUP BY t.id ORDER BY t.total_usage_hours DESC")->fetchAll();
    }

    public function getIncidentsReport($from, $to) {
        $stmt = $this->db->prepare("SELECT i.*, u.full_name AS reporter, u2.full_name AS resolver FROM incidents i JOIN users u ON i.reported_by=u.id LEFT JOIN users u2 ON i.resolved_by=u2.id WHERE i.reported_at BETWEEN ? AND ? ORDER BY i.reported_at DESC");
        $stmt->execute([$from.' 00:00:00', $to.' 23:59:59']);
        return $stmt->fetchAll();
    }

    public function getVolunteersReport($from, $to) {
        $stmt = $this->db->prepare("SELECT u.full_name, u.email, SUM(sh.hours_logged) AS total_hours, COUNT(sh.id) AS entries, GROUP_CONCAT(DISTINCT sh.month_year ORDER BY sh.month_year SEPARATOR ', ') AS months FROM service_hours sh JOIN users u ON sh.user_id=u.id WHERE sh.logged_at BETWEEN ? AND ? AND sh.status='approved' GROUP BY u.id ORDER BY total_hours DESC");
        $stmt->execute([$from.' 00:00:00', $to.' 23:59:59']);
        return $stmt->fetchAll();
    }

    public function getWaitlistReport() {
        return $this->db->query("SELECT w.*, u.full_name, u.email, u.community_points, u.residency_months FROM waitlist w JOIN users u ON w.user_id=u.id ORDER BY w.priority_score DESC")->fetchAll();
    }

    public function getAuditReport($from, $to) {
        $stmt = $this->db->prepare("SELECT al.*, u.full_name FROM audit_log al LEFT JOIN users u ON al.user_id=u.id WHERE al.logged_at BETWEEN ? AND ? ORDER BY al.logged_at DESC LIMIT 500");
        $stmt->execute([$from.' 00:00:00', $to.' 23:59:59']);
        return $stmt->fetchAll();
    }

    // Export Specific Getters (returning flat arrays)
    public function getMembersExport() {
        return $this->db->query("SELECT u.id, u.full_name, u.email, u.phone, r.name, u.membership_status, u.community_points, u.karma_points, IF(u.is_active,'Active','Inactive'), u.created_at FROM users u JOIN roles r ON u.role_id=r.id ORDER BY r.id, u.full_name")->fetchAll(PDO::FETCH_NUM);
    }
    
    public function getLeasesExport($from, $to) {
        $stmt = $this->db->prepare("SELECT p.plot_code, u.full_name, u.email, p.area_sqm, l.start_date, l.end_date, l.base_fee, l.total_fee, l.status FROM leases l JOIN plots p ON l.plot_id=p.id JOIN users u ON l.user_id=u.id WHERE l.start_date<=? AND l.end_date>=? ORDER BY l.end_date");
        $stmt->execute([$to, $from]);
        return $stmt->fetchAll(PDO::FETCH_NUM);
    }

    public function getBillingExport($from, $to) {
        $stmt = $this->db->prepare("SELECT bt.payment_date, u.full_name, u.email, p.plot_code, bt.amount, bt.payment_method, bt.status, bt.notes FROM billing_transactions bt JOIN users u ON bt.user_id=u.id JOIN leases l ON bt.lease_id=l.id JOIN plots p ON l.plot_id=p.id WHERE bt.payment_date BETWEEN ? AND ? ORDER BY bt.payment_date DESC");
        $stmt->execute([$from.' 00:00:00', $to.' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_NUM);
    }

    public function getToolsExport() {
        return $this->db->query("SELECT t.name, t.status, t.total_usage_hours, t.maintenance_threshold_hours, COUNT(r.id), SUM(CASE WHEN r.status='completed' THEN 1 ELSE 0 END), SUM(CASE WHEN r.status='overdue' THEN 1 ELSE 0 END), IF(t.needs_maintenance,'Yes','No') FROM tools t LEFT JOIN tool_reservations r ON t.id=r.tool_id GROUP BY t.id ORDER BY t.total_usage_hours DESC")->fetchAll(PDO::FETCH_NUM);
    }

    public function getIncidentsExport($from, $to) {
        $stmt = $this->db->prepare("SELECT i.reported_at, i.title, i.location, i.severity, u.full_name, i.status, u2.full_name, i.resolved_at FROM incidents i JOIN users u ON i.reported_by=u.id LEFT JOIN users u2 ON i.resolved_by=u2.id WHERE i.reported_at BETWEEN ? AND ? ORDER BY i.reported_at DESC");
        $stmt->execute([$from.' 00:00:00', $to.' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_NUM);
    }

    public function getVolunteersExport($from, $to, $monthlyHours) {
        $stmt = $this->db->prepare("SELECT u.full_name, u.email, SUM(sh.hours_logged), COUNT(sh.id), IF(SUM(sh.hours_logged)>=?,'Met','Short') FROM service_hours sh JOIN users u ON sh.user_id=u.id WHERE sh.logged_at BETWEEN ? AND ? AND sh.status='approved' GROUP BY u.id ORDER BY SUM(sh.hours_logged) DESC");
        $stmt->execute([$monthlyHours, $from.' 00:00:00', $to.' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_NUM);
    }

    public function getWaitlistExport() {
        $stmt = $this->db->query("SELECT u.full_name, u.email, w.priority_score, u.community_points, u.residency_months, w.status, w.joined_at FROM waitlist w JOIN users u ON w.user_id=u.id ORDER BY w.priority_score DESC");
        $all = $stmt->fetchAll(PDO::FETCH_NUM);
        foreach ($all as $i => $r) {
            array_unshift($r, $i + 1); // prepend position
            $all[$i] = $r;
        }
        return $all;
    }

    public function getAuditExport($from, $to) {
        $stmt = $this->db->prepare("SELECT al.logged_at, COALESCE(u.full_name,'System'), al.action_type, al.module, al.target_table, al.target_id, al.description, al.ip_address FROM audit_log al LEFT JOIN users u ON al.user_id=u.id WHERE al.logged_at BETWEEN ? AND ? ORDER BY al.logged_at DESC LIMIT 5000");
        $stmt->execute([$from.' 00:00:00', $to.' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_NUM);
    }
}
