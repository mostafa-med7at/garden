<?php
require_once __DIR__ . '/../core/Model.php';

class TaskModel extends Model {
    public function addTask($title, $desc, $difficulty, $pts, $creatorId) {
        $this->db->prepare("INSERT INTO tasks (title,description,difficulty_score,points_reward,created_by) VALUES (?,?,?,?,?)")
                 ->execute([$title, $desc, $difficulty, $pts, $creatorId]);
    }

    public function claimTask($taskId, $userId) {
        $this->db->prepare("UPDATE tasks SET status='in_progress', assigned_to=? WHERE id=? AND status='open'")
                 ->execute([$userId, $taskId]);
    }

    public function getTask($taskId, $userId) {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id=? AND assigned_to=?");
        $stmt->execute([$taskId, $userId]);
        return $stmt->fetch();
    }

    public function completeTask($taskId, $userId, $compType, $pts, $stat) {
        $this->db->prepare("UPDATE tasks SET status=?, completed_at=NOW() WHERE id=?")->execute([$stat, $taskId]);
        $this->db->prepare("INSERT INTO task_completions (task_id,user_id,completion_type,points_awarded) VALUES (?,?,?,?)")
                 ->execute([$taskId, $userId, $compType, $pts]);
    }

    public function addCommunityPoints($userId, $pts) {
        $this->db->prepare("UPDATE users SET community_points=community_points+? WHERE id=?")->execute([$pts, $userId]);
    }

    public function logServiceHours($userId, $hours, $desc, $month) {
        $this->db->prepare("INSERT INTO service_hours (user_id,hours_logged,activity_description,month_year,status) VALUES (?,?,?,?,'pending')")
                 ->execute([$userId, $hours, $desc, $month]);
    }

    public function reviewServiceHours($hId, $action, $adminId) {
        $this->db->prepare("UPDATE service_hours SET status=?,reviewed_by=? WHERE id=?")
                 ->execute([$action, $adminId, $hId]);
    }

    public function getAllTasks() {
        return $this->db->query("SELECT t.*, u.full_name AS assignee FROM tasks t LEFT JOIN users u ON t.assigned_to=u.id ORDER BY t.status='open' DESC, t.difficulty_score DESC")->fetchAll();
    }

    public function getApprovedHours($userId, $month) {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(hours_logged),0) FROM service_hours WHERE user_id=? AND month_year=? AND status='approved'");
        $stmt->execute([$userId, $month]);
        return (float)$stmt->fetchColumn();
    }

    public function getPendingHours() {
        return $this->db->query("SELECT sh.*, u.full_name FROM service_hours sh JOIN users u ON sh.user_id=u.id WHERE sh.status='pending' ORDER BY sh.logged_at DESC")->fetchAll();
    }

    public function getMyHourLog($userId, $limit = 10) {
        $limit = (int)$limit;
        $stmt = $this->db->prepare("SELECT * FROM service_hours WHERE user_id=? ORDER BY logged_at DESC LIMIT {$limit}");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
