<?php
// models/SeedModel.php

require_once __DIR__ . '/../core/Model.php';

class SeedModel extends Model {

    public function addSeed($name, $variety, $quantity_packets, $stored_date, $expiry_months, $allergen_category, $parent_plant_notes, $added_by) {
        $stmt = $this->db->prepare("INSERT INTO seeds (name,variety,quantity_packets,stored_date,expiry_months,allergen_category,parent_plant_notes,added_by) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $name, $variety, $quantity_packets,
            $stored_date, $expiry_months,
            $allergen_category, $parent_plant_notes, $added_by
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getAllSeeds() {
        return $this->db->query("SELECT s.*, u.full_name AS added_by_name FROM seeds s LEFT JOIN users u ON s.added_by=u.id ORDER BY s.status, s.name")->fetchAll();
    }

    public function runViabilityCheck() {
        $allSeeds = $this->db->query("SELECT * FROM seeds")->fetchAll();
        $updated  = 0;
        foreach ($allSeeds as $s) {
            $storedDate  = new DateTime($s['stored_date']);
            $now         = new DateTime();
            $ageMonths   = (int)$storedDate->diff($now)->days / 30.44;
            $expiryPct   = $ageMonths / $s['expiry_months'];

            if ($expiryPct >= 1) {
                $newStatus = 'expired';
            } elseif ($expiryPct >= 0.8) {
                $newStatus = 'nearing_expiry';
            } else {
                $newStatus = 'viable';
            }
            if ($newStatus !== $s['status'] && !in_array($s['status'],['flagged_for_testing','recommended_planting'])) {
                $this->db->prepare("UPDATE seeds SET status=? WHERE id=?")->execute([$newStatus, $s['id']]);
                $updated++;
            }
        }
        return $updated;
    }

    public function passGermination($seedId) {
        return $this->db->prepare("UPDATE seeds SET status='recommended_planting' WHERE id=?")->execute([$seedId]);
    }

    public function getSeedById($id) {
        $stmt = $this->db->prepare("SELECT * FROM seeds WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function withdrawSeeds($seedId, $qty, $userId) {
        $seed = $this->getSeedById($seedId);
        if ($seed && $seed['quantity_packets'] >= $qty) {
            $this->db->prepare("UPDATE seeds SET quantity_packets=quantity_packets-? WHERE id=?")->execute([$qty, $seedId]);
            // Award seed bank credits
            $this->db->prepare("UPDATE users SET seed_bank_credits=seed_bank_credits+? WHERE id=?")->execute([$qty*2, $userId]);
            return true;
        }
        return false;
    }
}
