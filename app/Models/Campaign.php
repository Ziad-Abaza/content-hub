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
            INSERT INTO campaigns (title, description, status, tags)
            VALUES (?, ?, ?, ?)
        ");
        $tagsJson = is_array($data['tags'] ?? null) ? json_encode($data['tags']) : json_encode([]);
        $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['status'] ?? 'active',
            $tagsJson
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE campaigns 
            SET title = ?, description = ?, status = ?, tags = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $tagsJson = is_array($data['tags'] ?? null) ? json_encode($data['tags']) : json_encode([]);
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['status'] ?? 'active',
            $tagsJson,
            $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM campaigns WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
