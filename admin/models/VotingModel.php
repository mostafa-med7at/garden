<?php
require_once __DIR__ . '/../core/Model.php';

class VotingModel extends Model {
    public function createProposal($title, $desc, $createdBy, $endsAt) {
        $this->db->prepare("INSERT INTO proposals (title,description,created_by,voting_ends_at) VALUES (?,?,?,?)")
                 ->execute([$title, $desc, $createdBy, $endsAt]);
    }

    public function getOpenProposal($propId) {
        $stmt = $this->db->prepare("SELECT * FROM proposals WHERE id=? AND status='open' AND voting_ends_at > NOW()");
        $stmt->execute([$propId]);
        return $stmt->fetch();
    }

    public function castVote($propId, $userId) {
        $this->db->prepare("INSERT INTO votes (proposal_id,user_id) VALUES (?,?)")->execute([$propId, $userId]);
    }

    public function getVoteCount($propId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM votes WHERE proposal_id=?");
        $stmt->execute([$propId]);
        return (int)$stmt->fetchColumn();
    }

    public function closeVoting($propId, $winnerId) {
        $this->db->prepare("UPDATE proposals SET status='decided', winner_id=? WHERE id=?")->execute([$winnerId, $propId]);
    }

    public function getAllProposals($userId) {
        return $this->db->query("
            SELECT p.*, u.full_name AS creator,
                   COUNT(v.id) AS vote_count,
                   (SELECT COUNT(*) FROM votes WHERE proposal_id=p.id AND user_id={$userId}) AS i_voted
            FROM proposals p
            LEFT JOIN users u ON p.created_by=u.id
            LEFT JOIN votes v ON v.proposal_id=p.id
            GROUP BY p.id
            ORDER BY p.status='open' DESC, p.created_at DESC
        ")->fetchAll();
    }

    public function getTotalActiveVoters() {
        return $this->db->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn();
    }
}
