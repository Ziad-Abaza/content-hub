<?php
/**
 * Content Post Model
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Media.php';

class Post {
    private PDO $db;
    private Media $mediaModel;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->mediaModel = new Media();
    }

    public function getAll(array $filters = []): array {
        $sql = "
            SELECT p.*, c.title as campaign_title 
            FROM content_posts p 
            LEFT JOIN campaigns c ON p.campaign_id = c.id 
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['campaign_id'])) {
            $sql .= " AND p.campaign_id = ?";
            $params[] = $filters['campaign_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $sql .= " AND (p.title LIKE ? OR p.primary_caption LIKE ? OR p.hashtags LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll();

        foreach ($posts as &$post) {
            $post['channel_captions'] = json_decode($post['channel_captions'] ?? '{}', true) ?: [];
            $post['hashtags'] = json_decode($post['hashtags'] ?? '[]', true) ?: [];
            $post['media'] = $this->mediaModel->getByPostId((int)$post['id']);
        }

        return $posts;
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.title as campaign_title 
            FROM content_posts p 
            LEFT JOIN campaigns c ON p.campaign_id = c.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $post = $stmt->fetch();

        if ($post) {
            $post['channel_captions'] = json_decode($post['channel_captions'] ?? '{}', true) ?: [];
            $post['hashtags'] = json_decode($post['hashtags'] ?? '[]', true) ?: [];
            $post['media'] = $this->mediaModel->getByPostId($id);
            return $post;
        }
        return null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO content_posts (
                campaign_id, title, primary_caption, channel_captions, hashtags, status, scheduled_for
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $campId = !empty($data['campaign_id']) ? (int)$data['campaign_id'] : null;
        $channelCaptions = is_array($data['channel_captions'] ?? null) 
            ? json_encode($data['channel_captions'], JSON_UNESCAPED_UNICODE) 
            : json_encode((object)[]);
        
        $hashtags = is_array($data['hashtags'] ?? null) 
            ? json_encode(array_values(array_filter($data['hashtags'])), JSON_UNESCAPED_UNICODE) 
            : json_encode([]);

        $stmt->execute([
            $campId,
            $data['title'],
            $data['primary_caption'],
            $channelCaptions,
            $hashtags,
            $data['status'] ?? 'ready',
            $data['scheduled_for'] ?? null
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE content_posts 
            SET campaign_id = ?, title = ?, primary_caption = ?, channel_captions = ?, 
                hashtags = ?, status = ?, scheduled_for = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $campId = !empty($data['campaign_id']) ? (int)$data['campaign_id'] : null;
        $channelCaptions = is_array($data['channel_captions'] ?? null) 
            ? json_encode($data['channel_captions'], JSON_UNESCAPED_UNICODE) 
            : json_encode((object)[]);
        
        $hashtags = is_array($data['hashtags'] ?? null) 
            ? json_encode(array_values(array_filter($data['hashtags'])), JSON_UNESCAPED_UNICODE) 
            : json_encode([]);

        return $stmt->execute([
            $campId,
            $data['title'],
            $data['primary_caption'],
            $channelCaptions,
            $hashtags,
            $data['status'] ?? 'ready',
            $data['scheduled_for'] ?? null,
            $id
        ]);
    }

    public function incrementCopyCount(int $id): int {
        $stmt = $this->db->prepare("
            UPDATE content_posts 
            SET copy_count = copy_count + 1 
            WHERE id = ?
        ");
        $stmt->execute([$id]);

        $stmtCount = $this->db->prepare("SELECT copy_count FROM content_posts WHERE id = ?");
        $stmtCount->execute([$id]);
        return (int)$stmtCount->fetchColumn();
    }

    public function delete(int $id): bool {
        $mediaList = $this->mediaModel->getByPostId($id);
        foreach ($mediaList as $media) {
            $this->mediaModel->delete((int)$media['id']);
        }
        $stmt = $this->db->prepare("DELETE FROM content_posts WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
