<?php
require_once __DIR__ . '/../core/Model.php';

class ConsumableModel extends Model {
    public function addConsumable($name, $unit, $stockLevel, $threshold) {
        $this->db->prepare("INSERT INTO consumables (name,unit,stock_level,reorder_threshold) VALUES (?,?,?,?)")
                 ->execute([$name, $unit, $stockLevel, $threshold]);
    }

    public function restockConsumable($id, $amount) {
        $this->db->prepare("UPDATE consumables SET stock_level=stock_level+?, alert_sent=0 WHERE id=?")
                 ->execute([$amount, $id]);
    }

    public function getConsumableById($id) {
        $stmt = $this->db->prepare("SELECT * FROM consumables WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateStockAndUse($id, $newLevel, $userId, $amount) {
        $this->db->prepare("UPDATE consumables SET stock_level=? WHERE id=?")->execute([$newLevel, $id]);
        $this->db->prepare("INSERT INTO consumable_usage_log (consumable_id,used_by,amount_used) VALUES (?,?,?)")->execute([$id, $userId, $amount]);
    }

    public function markAlertSent($id) {
        $this->db->prepare("UPDATE consumables SET alert_sent=1 WHERE id=?")->execute([$id]);
    }

    public function getAllConsumables() {
        return $this->db->query("SELECT * FROM consumables ORDER BY (stock_level <= reorder_threshold) DESC, name")->fetchAll();
    }

    public function getRecentUsageLog($limit = 20) {
        $limit = (int)$limit;
        return $this->db->query("SELECT ul.*, c.name AS item_name, c.unit, u.full_name FROM consumable_usage_log ul JOIN consumables c ON ul.consumable_id=c.id JOIN users u ON ul.used_by=u.id ORDER BY ul.used_at DESC LIMIT {$limit}")->fetchAll();
    }
}
