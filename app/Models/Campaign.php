<?php
/**
 * Campaign Model
 */

require_once __DIR__ . '/../../config/database.php';

class Campaign {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(): array {
        $sql = "
            SELECT c.*, COUNT(p.id) as post_count 
            FROM campaigns c 
            LEFT JOIN content_posts p ON c.id = p.campaign_id 
            GROUP BY c.id 
            ORDER BY c.created_at DESC
        ";
        $stmt = $this->db->query($sql);
        $campaigns = $stmt->fetchAll();
        
        foreach ($campaigns as &$c) {
            $c['tags'] = json_decode($c['tags'] ?? '[]', true) ?: [];
        }
        return $campaigns;
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM campaigns WHERE id = ?");
        $stmt->execute([$id]);
        $camp = $stmt->fetch();
        if ($camp) {
            $camp['tags'] = json_decode($camp['tags'] ?? '[]', true) ?: [];
            return $camp;
        }
        return null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO campaigns (title, description, status, tags, color)
            VALUES (?, ?, ?, ?, ?)
        ");
        $tags = $data['tags'] ?? [];
        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            if (is_array($decoded)) {
                $tags = $decoded;
            } else {
                $tags = array_filter(array_map('trim', explode(',', $tags)));
            }
        }
        $tagsJson = json_encode(array_values((array)$tags), JSON_UNESCAPED_UNICODE);

        $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['status'] ?? 'active',
            $tagsJson,
            $data['color'] ?? '#6366f1'
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE campaigns 
            SET title = ?, description = ?, status = ?, tags = ?, color = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $tags = $data['tags'] ?? [];
        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            if (is_array($decoded)) {
                $tags = $decoded;
            } else {
                $tags = array_filter(array_map('trim', explode(',', $tags)));
            }
        }
        $tagsJson = json_encode(array_values((array)$tags), JSON_UNESCAPED_UNICODE);

        return $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['status'] ?? 'active',
            $tagsJson,
            $data['color'] ?? '#6366f1',
            $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM campaigns WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
