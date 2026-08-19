<?php
/**
 * Media Controller
 */

require_once __DIR__ . '/../Models/Media.php';
require_once __DIR__ . '/../Helpers/ImageHelper.php';
require_once __DIR__ . '/../Helpers/ZipHelper.php';

class MediaController {
    private Media $mediaModel;

    public function __construct() {
        $this->mediaModel = new Media();
    }

    public function upload(): void {
        header('Content-Type: application/json');

        $postId = !empty($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
        if (!$postId) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Post ID is required for media upload']);
            exit;
        }

        if (empty($_FILES['media_files'])) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'No files uploaded']);
            exit;
        }

        $uploaded = [];
        $files = $_FILES['media_files'];

        // Normalize multiple or single file uploads
        if (is_array($files['name'])) {
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $fileItem = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];
                    $processed = ImageHelper::processUploadedFile($fileItem);
                    if ($processed) {
                        $processed['post_id'] = $postId;
                        $mediaId = $this->mediaModel->create($processed);
                        $processed['id'] = $mediaId;
                        $uploaded[] = $processed;
                    }
                }
            }
        } else {
            $processed = ImageHelper::processUploadedFile($files);
            if ($processed) {
                $processed['post_id'] = $postId;
                $mediaId = $this->mediaModel->create($processed);
                $processed['id'] = $mediaId;
                $uploaded[] = $processed;
            }
        }

        echo json_encode(['status' => 'success', 'message' => count($uploaded) . ' media files saved', 'data' => $uploaded]);
        exit;
    }

    public function download(int $id): void {
        $asset = $this->mediaModel->getById($id);
        if (!$asset) {
            http_response_code(404);
            die("Asset not found.");
        }

        $filePath = __DIR__ . '/../../' . $asset['file_path'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            die("Physical asset file missing on server.");
        }

        $downloadName = $asset['original_name'] ?: basename($asset['file_path']);
        
        header('Content-Description: File Transfer');
        header('Content-Type: ' . ($asset['mime_type'] ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . addslashes($downloadName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
        exit;
    }

    public function batchDownload(): void {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;
        
        $assetIds = $data['asset_ids'] ?? [];
        if (is_string($assetIds)) {
            $assetIds = explode(',', $assetIds);
        }

        $assetIds = array_filter(array_map('intval', (array)$assetIds));

        if (empty($assetIds)) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No asset IDs provided']);
            exit;
        }

        $assets = $this->mediaModel->getByIds($assetIds);
        if (empty($assets)) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No valid media assets found']);
            exit;
        }

        try {
            $zipPath = ZipHelper::createZipFromAssets($assets, 'content_hub_export.zip');
            
            header('Content-Description: File Transfer');
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="content_hub_assets_' . date('Y-m-d_His') . '.zip"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($zipPath));
            
            readfile($zipPath);
            @unlink($zipPath);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function delete(int $id): void {
        header('Content-Type: application/json');
        $success = $this->mediaModel->delete($id);
        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Asset deleted']);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Asset not found']);
        }
        exit;
    }
}
