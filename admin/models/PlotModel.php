<?php
require_once __DIR__ . '/../core/Model.php';

class PlotModel extends Model {
    public function getAllPlots() {
        $plots = $this->db->query("
            SELECT p.*, l.user_id AS owner_id, u.full_name AS owner_name, l.status AS lease_status, l.end_date
            FROM plots p
            LEFT JOIN leases l ON l.plot_id = p.id AND l.status = 'active'
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY p.grid_y, p.grid_x
        ")->fetchAll();

        foreach ($plots as &$plotRef) {
            $plotRef['status'] = $plotRef['status'] ?? 'available';
            if (!empty($plotRef['lease_status']) && $plotRef['lease_status'] === 'active') {
                $plotRef['status'] = 'occupied';
            }
            $plotRef['compliance_status'] = $plotRef['compliance_status'] ?? 'compliant';
            $plotRef['plot_code'] = $plotRef['plot_code'] ?? 'Unknown';
            $plotRef['sunlight_level'] = $plotRef['sunlight_level'] ?? 'medium';
            $plotRef['soil_quality'] = $plotRef['soil_quality'] ?? 'medium';
        }
        unset($plotRef);
        return $plots;
    }

    public function isUserOnWaitlist($userId) {
        if (!$userId) return false;
        $wchk = $this->db->prepare("SELECT id FROM waitlist WHERE user_id=? AND status IN ('waiting','notified')");
        $wchk->execute([$userId]);
        return (bool)$wchk->fetch();
    }

    public function getPlotById($id) {
        $stmt = $this->db->prepare("SELECT * FROM plots WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getExistingGridPlots() {
        return $this->db->query("SELECT plot_code, status, grid_x, grid_y FROM plots WHERE grid_x IS NOT NULL AND grid_y IS NOT NULL")->fetchAll();
    }

    public function isPlotCodeTaken($code) {
        $chk = $this->db->prepare("SELECT id FROM plots WHERE plot_code = ?");
        $chk->execute([$code]);
        return (bool)$chk->fetch();
    }

    public function isGridCellTaken($gridX, $gridY) {
        $gchk = $this->db->prepare("SELECT id FROM plots WHERE grid_x = ? AND grid_y = ?");
        $gchk->execute([$gridX, $gridY]);
        return (bool)$gchk->fetch();
    }

    public function createPlot($code, $area, $sunlight, $soil, $gridX, $gridY) {
        $this->db->prepare("INSERT INTO plots (plot_code, area_sqm, sunlight_level, soil_quality, grid_x, grid_y) VALUES (?,?,?,?,?,?)")
           ->execute([$code, $area, $sunlight, $soil, $gridX, $gridY]);
        return (int)$this->db->lastInsertId();
    }

    public function getPlotDetail($id) {
        $stmt = $this->db->prepare("SELECT p.*, l.user_id AS owner_id, l.end_date, l.total_fee, l.status AS lease_status, u.full_name AS owner_name
                      FROM plots p LEFT JOIN leases l ON l.plot_id=p.id AND l.status='active' LEFT JOIN users u ON l.user_id=u.id WHERE p.id=?");
        $stmt->execute([$id]);
        $plot = $stmt->fetch();
        if ($plot) {
            $plot['status'] = $plot['status'] ?? 'available';
            if (!empty($plot['lease_status']) && $plot['lease_status'] === 'active') {
                $plot['status'] = 'occupied';
            }
            $plot['compliance_status'] = $plot['compliance_status'] ?? 'compliant';
            $plot['plot_code'] = $plot['plot_code'] ?? 'Unknown';
            $plot['area_sqm'] = $plot['area_sqm'] ?? 0;
            $plot['sunlight_level'] = $plot['sunlight_level'] ?? 'medium';
            $plot['soil_quality'] = $plot['soil_quality'] ?? 'medium';
        }
        return $plot;
    }

    public function getSoilEvents($plotId, $limit=5) {
        $limit = (int)$limit;
        $stmt = $this->db->prepare("SELECT se.*, u.full_name FROM soil_events se JOIN users u ON se.user_id=u.id WHERE se.plot_id=? ORDER BY se.recorded_at DESC LIMIT {$limit}");
        $stmt->execute([$plotId]);
        return $stmt->fetchAll();
    }

    public function getPestReports($plotId, $limit=5) {
        $limit = (int)$limit;
        $stmt = $this->db->prepare("SELECT * FROM pest_reports WHERE plot_id=? ORDER BY reported_at DESC LIMIT {$limit}");
        $stmt->execute([$plotId]);
        return $stmt->fetchAll();
    }

    public function getInspections($plotId, $limit=3) {
        $limit = (int)$limit;
        $stmt = $this->db->prepare("SELECT i.*, u.full_name AS warden FROM inspections i JOIN users u ON i.warden_id=u.id WHERE i.plot_id=? ORDER BY i.inspected_at DESC LIMIT {$limit}");
        $stmt->execute([$plotId]);
        return $stmt->fetchAll();
    }
}
