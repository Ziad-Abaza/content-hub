<?php
/**
 * Media Model
 */

require_once __DIR__ . '/../../config/database.php';

class Media {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getByPostId(int $postId): array {
        $stmt = $this->db->prepare("SELECT * FROM media_assets WHERE post_id = ? ORDER BY id ASC");
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM media_assets WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function getByIds(array $ids): array {
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT * FROM media_assets WHERE id IN ($placeholders)");
        $stmt->execute(array_values($ids));
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO media_assets (
                post_id, file_name, original_name, mime_type, file_size, 
                file_path, thumbnail_path, width, height, aspect_ratio, file_type
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['post_id'],
            $data['file_name'],
            $data['original_name'],
            $data['mime_type'],
            $data['file_size'],
            $data['file_path'],
            $data['thumbnail_path'] ?? null,
            $data['width'] ?? null,
            $data['height'] ?? null,
            $data['aspect_ratio'] ?? '1:1',
            $data['file_type'] ?? 'image'
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function delete(int $id): bool {
        $asset = $this->getById($id);
        if ($asset) {
            $realPath = __DIR__ . '/../../' . $asset['file_path'];
            if (file_exists($realPath)) {
                @unlink($realPath);
            }
            if (!empty($asset['thumbnail_path'])) {
                $thumbPath = __DIR__ . '/../../' . $asset['thumbnail_path'];
                if (file_exists($thumbPath)) {
                    @unlink($thumbPath);
                }
            }
            $stmt = $this->db->prepare("DELETE FROM media_assets WHERE id = ?");
            return $stmt->execute([$id]);
        }
        return false;
    }
}
