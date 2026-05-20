<?php
/**
 * ReliaWork2 AnnouncementModel
 */

class AnnouncementModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT a.*, u.name AS created_by_name
             FROM announcements a
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.id = ?",
            [$id]
        );
    }

    public function findAll(array $filters = []): array
    {
        $sql    = "SELECT a.*, u.name AS created_by_name
                   FROM announcements a
                   LEFT JOIN users u ON u.id = a.created_by
                   WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $sql .= " AND a.type = ?";
            $params[] = $filters['type'];
        }

        $sql .= " ORDER BY a.created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO announcements (title, content, type, status, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())",
            [
                $data['title'],
                $data['content'],
                $data['type'] ?? 'general',
                $data['status'] ?? 'draft',
                $data['created_by'],
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sets   = [];
        $params = [];
        $allowed = ['title', 'content', 'type', 'status'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]   = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($sets)) return false;
        $params[] = $id;
        $this->db->execute("UPDATE announcements SET " . implode(', ', $sets) . " WHERE id = ?", $params);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->db->execute("DELETE FROM announcements WHERE id = ?", [$id]);
        return true;
    }

    public function paginate(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $all   = $this->findAll($filters);
        $total = count($all);
        $data  = array_slice($all, ($page - 1) * $perPage, $perPage);
        return [
            'data'      => $data,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => (int)ceil($total / $perPage),
        ];
    }

    public function getPublished(int $limit = 5): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM announcements WHERE status = 'published' ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }
}
