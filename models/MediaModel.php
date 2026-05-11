<?php
// models/MediaModel.php

require_once __DIR__ . '/../core/Model.php';

class MediaModel extends Model {

    public function __construct() {
        parent::__construct();
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }

        $this->db->exec("CREATE TABLE IF NOT EXISTS media_files (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            file_size INT NOT NULL,
            description TEXT,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
    }

    public function addFile($userId, $fileName, $originalName, $mimeType, $fileSize, $description) {
        $this->db->prepare("INSERT INTO media_files (user_id, file_name, original_name, mime_type, file_size, description) VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([$userId, $fileName, $originalName, $mimeType, $fileSize, $description]);
        return (int)$this->db->lastInsertId();
    }

    public function getFileById($id) {
        $stmt = $this->db->prepare("SELECT * FROM media_files WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function deleteFile($id) {
        return $this->db->prepare("DELETE FROM media_files WHERE id = ?")->execute([$id]);
    }

    public function getAllFiles() {
        return $this->db->query("SELECT m.*, u.full_name FROM media_files m JOIN users u ON m.user_id = u.id ORDER BY m.uploaded_at DESC")->fetchAll();
    }

    public function getFilesByUser($userId) {
        $stmt = $this->db->prepare("SELECT m.*, u.full_name FROM media_files m JOIN users u ON m.user_id = u.id WHERE m.user_id = ? ORDER BY m.uploaded_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
