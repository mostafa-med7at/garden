<?php
// controllers/MediaController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/MediaModel.php';

class MediaController extends Controller {

    private $model;

    public function __construct() {
        $this->model = new MediaModel();
    }

    public function index($user) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['upload_file'])) {
                $description = trim($_POST['description'] ?? '');
                
                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath   = $_FILES['file']['tmp_name'];
                    $fileName      = $_FILES['file']['name'];
                    $fileSize      = $_FILES['file']['size'];
                    $fileType      = $_FILES['file']['type'];
                    
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'];
                    
                    if (in_array($fileExtension, $allowedExtensions)) {
                        $newFileName = uniqid('media_', true) . '.' . $fileExtension;
                        $destPath = UPLOAD_DIR . $newFileName;
                        
                        if (move_uploaded_file($fileTmpPath, $destPath)) {
                            $insertedId = $this->model->addFile($user['id'], $newFileName, $fileName, $fileType, $fileSize, $description);
                            auditLog('file_uploaded', 'media', 'media_files', $insertedId, "Uploaded $fileName");
                            setFlash('success', 'File successfully uploaded.');
                        } else {
                            setFlash('danger', 'There was an error moving the uploaded file. Check directory permissions.');
                        }
                    } else {
                        setFlash('danger', 'Upload failed. Allowed file types: ' . implode(', ', $allowedExtensions));
                    }
                } else {
                    setFlash('danger', 'No file uploaded or there was an upload error (Code: ' . ($_FILES['file']['error'] ?? 'unknown') . ').');
                }
                header('Location: index.php'); exit;
            }
            
            if (isset($_POST['delete_file'])) {
                $fileId = (int)$_POST['file_id'];
                $file = $this->model->getFileById($fileId);
                
                if ($file && ($user['role_name'] === 'admin' || $file['user_id'] === $user['id'])) {
                    $filePath = UPLOAD_DIR . $file['file_name'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    $this->model->deleteFile($fileId);
                    auditLog('file_deleted', 'media', 'media_files', $fileId, "Deleted {$file['original_name']}");
                    setFlash('success', 'File deleted successfully.');
                } else {
                    setFlash('danger', 'File not found or permission denied.');
                }
                header('Location: index.php'); exit;
            }
        }

        if ($user['role_name'] === 'admin' || $user['role_name'] === 'warden') {
            $files = $this->model->getAllFiles();
        } else {
            $files = $this->model->getFilesByUser($user['id']);
        }

        $this->view('media_index', [
            'user' => $user,
            'pageTitle' => 'File Manager',
            'files' => $files
        ]);
    }
}
