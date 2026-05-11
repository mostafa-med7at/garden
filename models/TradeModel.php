<?php
require_once __DIR__ . '/../core/Model.php';

class TradeModel extends Model {
    public function createTrade($sellerId, $title, $desc, $qty, $allergen, $cat, $expires) {
        $this->db->prepare("INSERT INTO flash_trades (seller_id,title,description,quantity,allergen_flag,allergen_category,expires_at) VALUES (?,?,?,?,?,?,?)")
                 ->execute([$sellerId, $title, $desc, $qty, $allergen, $cat, $expires]);
        return $this->db->lastInsertId();
    }

    public function getTradeToClaim($tradeId, $userId) {
        $stmt = $this->db->prepare("SELECT * FROM flash_trades WHERE id=? AND status='active' AND expires_at > NOW() AND seller_id != ?");
        $stmt->execute([$tradeId, $userId]);
        return $stmt->fetch();
    }

    public function claimTrade($tradeId, $userId) {
        $this->db->prepare("UPDATE flash_trades SET status='claimed', claimed_by=?, claimed_at=NOW() WHERE id=?")
                 ->execute([$userId, $tradeId]);
    }

    public function cancelTrade($tradeId, $sellerId) {
        $this->db->prepare("UPDATE flash_trades SET status='cancelled' WHERE id=? AND seller_id=?")
                 ->execute([$tradeId, $sellerId]);
    }

    public function donateProduce($userId, $produce, $qty, $karma, $spoiled, $reason = '') {
        if ($spoiled) {
            $this->db->prepare("INSERT INTO donations (donor_id,produce_name,quantity,karma_points_awarded,is_rejected,rejection_reason) VALUES (?,?,?,0,1,?)")
                     ->execute([$userId, $produce, $qty, $reason]);
        } else {
            $this->db->prepare("INSERT INTO donations (donor_id,produce_name,quantity,karma_points_awarded) VALUES (?,?,?,?)")
                     ->execute([$userId, $produce, $qty, $karma]);
        }
    }

    public function addKarma($userId, $karma) {
        $this->db->prepare("UPDATE users SET karma_points=karma_points+? WHERE id=?")
                 ->execute([$karma, $userId]);
    }

    public function checkTradeClaim($tradeId, $userId) {
        $chk = $this->db->prepare("SELECT id FROM flash_trades WHERE id=? AND claimed_by=?");
        $chk->execute([$tradeId, $userId]);
        return $chk->fetch();
    }

    public function rateTrade($tradeId, $userId, $rating, $notes) {
        $this->db->prepare("INSERT INTO produce_ratings (trade_id,rater_id,rating,notes) VALUES (?,?,?,?)")
                 ->execute([$tradeId, $userId, $rating, $notes]);
    }

    public function expireOldTrades() {
        $this->db->query("UPDATE flash_trades SET status='expired' WHERE status='active' AND expires_at <= NOW()");
    }

    public function getActiveTrades() {
        return $this->db->query("
            SELECT t.*, u.full_name AS seller_name,
                   TIMESTAMPDIFF(MINUTE, NOW(), t.expires_at) AS mins_left
            FROM flash_trades t JOIN users u ON t.seller_id=u.id
            WHERE t.status='active' AND t.expires_at > NOW()
            ORDER BY t.expires_at ASC
        ")->fetchAll();
    }

    public function getMyTrades($sellerId, $limit = 10) {
        $limit = (int)$limit;
        $stmt = $this->db->prepare("
            SELECT t.*, c.full_name AS claimer_name,
                   (SELECT AVG(rating) FROM produce_ratings WHERE trade_id=t.id) AS avg_rating
            FROM flash_trades t LEFT JOIN users c ON t.claimed_by=c.id
            WHERE t.seller_id=? ORDER BY t.created_at DESC LIMIT {$limit}
        ");
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll();
    }

    public function getTradesClaimedByMe($claimerId, $limit = 5) {
        $limit = (int)$limit;
        $stmt = $this->db->prepare("
            SELECT t.*, u.full_name AS seller_name,
                   (SELECT id FROM produce_ratings WHERE trade_id=t.id AND rater_id=?) AS already_rated
            FROM flash_trades t JOIN users u ON t.seller_id=u.id
            WHERE t.claimed_by=? AND t.status='claimed'
            ORDER BY t.claimed_at DESC LIMIT {$limit}
        ");
        $stmt->execute([$claimerId, $claimerId]);
        return $stmt->fetchAll();
    }

    public function getMyDonations($donorId, $limit = 10) {
        $limit = (int)$limit;
        $stmt = $this->db->prepare("SELECT * FROM donations WHERE donor_id=? ORDER BY donated_at DESC LIMIT {$limit}");
        $stmt->execute([$donorId]);
        return $stmt->fetchAll();
    }

    public function getMyDonationStats($donorId) {
        $stmt = $this->db->prepare("SELECT COUNT(*),COALESCE(SUM(karma_points_awarded),0) FROM donations WHERE donor_id=? AND is_rejected=0");
        $stmt->execute([$donorId]);
        return $stmt->fetch(\PDO::FETCH_NUM);
    }
}
