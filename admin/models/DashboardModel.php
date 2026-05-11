<?php
require_once __DIR__ . '/../core/Model.php';

class DashboardModel extends Model {
    public function getQuickStats() {
        return [
            'totalPlots'     => $this->db->query("SELECT COUNT(*) FROM plots")->fetchColumn(),
            'availablePlots' => $this->db->query("SELECT COUNT(*) FROM plots WHERE status='available'")->fetchColumn(),
            'totalMembers'   => $this->db->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn(),
            'openIncidents'  => $this->db->query("SELECT COUNT(*) FROM incidents WHERE status='open'")->fetchColumn(),
            'lowStock'       => $this->db->query("SELECT COUNT(*) FROM consumables WHERE stock_level <= reorder_threshold")->fetchColumn(),
            'activeTrades'   => $this->db->query("SELECT COUNT(*) FROM flash_trades WHERE status='active' AND expires_at > NOW()")->fetchColumn(),
            'openTasks'      => $this->db->query("SELECT COUNT(*) FROM tasks WHERE status='open'")->fetchColumn(),
            'waitlistCount'  => $this->db->query("SELECT COUNT(*) FROM waitlist WHERE status='waiting'")->fetchColumn(),
        ];
    }

    public function getMyLease($userId, $role) {
        if ($role === 'plot_owner') {
            $stmt = $this->db->prepare("SELECT l.*, p.plot_code FROM leases l JOIN plots p ON l.plot_id=p.id WHERE l.user_id=? AND l.status='active' LIMIT 1");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        }
        return null;
    }

    public function getRecentBroadcasts() {
        return $this->db->query("SELECT b.*, u.full_name FROM broadcasts b JOIN users u ON b.admin_id=u.id ORDER BY b.sent_at DESC LIMIT 3")->fetchAll();
    }
}
