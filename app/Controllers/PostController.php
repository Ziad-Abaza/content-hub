<?php
/**
 * Content Post Controller
 */

require_once __DIR__ . '/../Models/Post.php';
require_once __DIR__ . '/../Models/Media.php';
require_once __DIR__ . '/../Helpers/ImageHelper.php';

class PostController {
    private Post $postModel;
    private Media $mediaModel;

    public function __construct() {
        $this->postModel = new Post();
        $this->mediaModel = new Media();
    }

    public function index(): void {
        header('Content-Type: application/json');
        $filters = [
            'campaign_id' => !empty($_GET['campaign_id']) ? (int)$_GET['campaign_id'] : null,
            'status' => !empty($_GET['status']) ? trim($_GET['status']) : null,
            'search' => !empty($_GET['search']) ? trim($_GET['search']) : null
        ];

        $posts = $this->postModel->getAll($filters);
        echo json_encode(['status' => 'success', 'data' => $posts]);
        exit;
    }

    public function show(int $id): void {
        header('Content-Type: application/json');
        $post = $this->postModel->getById($id);
        if (!$post) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Post not found']);
            exit;
        }
        echo json_encode(['status' => 'success', 'data' => $post]);
        exit;
    }

    public function store(): void {
        header('Content-Type: application/json');

        // Can receive JSON or multipart/form-data with files
        $postData = $_POST;
        if (empty($postData) && empty($_FILES)) {
            $raw = file_get_contents('php://input');
            $postData = json_decode($raw, true) ?? [];
        }

        if (empty($postData['title']) || empty($postData['primary_caption'])) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Post title and primary caption are required']);
            exit;
        }

        // Process channel captions
        $channelCaptions = $postData['channel_captions'] ?? [];
        if (is_string($channelCaptions)) {
            $channelCaptions = json_decode($channelCaptions, true) ?: [];
        }

        // Process hashtags
        $hashtags = $postData['hashtags'] ?? [];
        if (is_string($hashtags)) {
            // Check if JSON array string or space/comma separated tags
            $decoded = json_decode($hashtags, true);
            if (is_array($decoded)) {
                $hashtags = $decoded;
            } else {
                // Split by spaces or commas
                preg_match_all('/#?([\p{L}\p{N}_]+)/u', $hashtags, $matches);
                $hashtags = array_map(function($t) { return str_starts_with($t, '#') ? $t : '#' . $t; }, $matches[0] ?? []);
            }
        }

        $newPost = [
            'campaign_id' => !empty($postData['campaign_id']) ? (int)$postData['campaign_id'] : null,
            'title' => trim($postData['title']),
            'primary_caption' => trim($postData['primary_caption']),
            'channel_captions' => $channelCaptions,
            'hashtags' => $hashtags,
            'status' => $postData['status'] ?? 'ready',
            'scheduled_for' => !empty($postData['scheduled_for']) ? $postData['scheduled_for'] : null
        ];

        $postId = $this->postModel->create($newPost);

        // Process any uploaded media files attached directly to this post
        if (!empty($_FILES['media_files'])) {
            $files = $_FILES['media_files'];
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
                            $this->mediaModel->create($processed);
                        }
                    }
                }
            } else if ($files['error'] === UPLOAD_ERR_OK) {
                $processed = ImageHelper::processUploadedFile($files);
                if ($processed) {
                    $processed['post_id'] = $postId;
                    $this->mediaModel->create($processed);
                }
            }
        }

        $createdPost = $this->postModel->getById($postId);

        http_response_code(201);
        echo json_encode(['status' => 'success', 'message' => 'Post created successfully', 'data' => $createdPost]);
        exit;
    }

    public function update(int $id): void {
        header('Content-Type: application/json');

        $post = $this->postModel->getById($id);
        if (!$post) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Post not found']);
            exit;
        }

        $postData = $_POST;
        if (empty($postData) && empty($_FILES)) {
            $raw = file_get_contents('php://input');
            $postData = json_decode($raw, true) ?? [];
        }

        if (empty($postData['title']) || empty($postData['primary_caption'])) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Post title and primary caption are required']);
            exit;
        }

        $channelCaptions = $postData['channel_captions'] ?? $post['channel_captions'];
        if (is_string($channelCaptions)) {
            $channelCaptions = json_decode($channelCaptions, true) ?: [];
        }

        $hashtags = $postData['hashtags'] ?? $post['hashtags'];
        if (is_string($hashtags)) {
            $decoded = json_decode($hashtags, true);
            if (is_array($decoded)) {
                $hashtags = $decoded;
            } else {
                preg_match_all('/#?([\p{L}\p{N}_]+)/u', $hashtags, $matches);
                $hashtags = array_map(function($t) { return str_starts_with($t, '#') ? $t : '#' . $t; }, $matches[0] ?? []);
            }
        }

        $updateData = [
            'campaign_id' => !empty($postData['campaign_id']) ? (int)$postData['campaign_id'] : null,
            'title' => trim($postData['title']),
            'primary_caption' => trim($postData['primary_caption']),
            'channel_captions' => $channelCaptions,
            'hashtags' => $hashtags,
            'status' => $postData['status'] ?? $post['status'],
            'scheduled_for' => !empty($postData['scheduled_for']) ? $postData['scheduled_for'] : null
        ];

        $this->postModel->update($id, $updateData);

        // Upload additional media if supplied
        if (!empty($_FILES['media_files'])) {
            $files = $_FILES['media_files'];
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
                            $processed['post_id'] = $id;
                            $this->mediaModel->create($processed);
                        }
                    }
                }
            }
        }

        $updated = $this->postModel->getById($id);
        echo json_encode(['status' => 'success', 'message' => 'Post updated successfully', 'data' => $updated]);
        exit;
    }

    public function trackCopy(int $id): void {
        header('Content-Type: application/json');
        $newCount = $this->postModel->incrementCopyCount($id);
        echo json_encode(['status' => 'success', 'copy_count' => $newCount]);
        exit;
    }

    public function delete(int $id): void {
        header('Content-Type: application/json');
        $success = $this->postModel->delete($id);
        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Post and assets deleted']);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Post not found']);
        }
        exit;
    }
}
