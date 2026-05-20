<?php
/**
 * ReliaWork2 JobFairRequestModel
 */

class JobFairRequestModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT r.*, 
                    u1.name AS requested_by_name, u1.email AS requested_by_email,
                    u2.name AS reviewed_by_name
             FROM job_fair_requests r
             LEFT JOIN users u1 ON u1.id = r.requested_by
             LEFT JOIN users u2 ON u2.id = r.reviewed_by
             WHERE r.id = ?",
            [$id]
        );
    }

    public function findAll(array $filters = []): array
    {
        $sql    = "SELECT r.*, u1.name AS requested_by_name, u2.name AS reviewed_by_name
                   FROM job_fair_requests r
                   LEFT JOIN users u1 ON u1.id = r.requested_by
                   LEFT JOIN users u2 ON u2.id = r.reviewed_by
                   WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND r.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['requested_by'])) {
            $sql .= " AND r.requested_by = ?";
            $params[] = $filters['requested_by'];
        }

        $sql .= " ORDER BY r.created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO job_fair_requests (title, requested_date, venue, description, status, requested_by, created_at, updated_at)
             VALUES (?, ?, ?, ?, 'pending', ?, NOW(), NOW())",
            [
                $data['title'],
                $data['requested_date'],
                $data['venue'] ?? null,
                $data['description'] ?? null,
                $data['requested_by'],
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sets   = [];
        $params = [];
        $allowed = ['title', 'requested_date', 'venue', 'description', 'status', 'remarks', 'reviewed_by'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]   = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($sets)) return false;
        $sets[]   = "updated_at = NOW()";
        $params[] = $id;
        $this->db->execute("UPDATE job_fair_requests SET " . implode(', ', $sets) . " WHERE id = ?", $params);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->db->execute("DELETE FROM job_fair_requests WHERE id = ?", [$id]);
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

    public function countByStatus(string $status): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM job_fair_requests WHERE status = ?",
            [$status]
        );
    }

    public function countApproved(): int
    {
        return $this->countByStatus('approved');
    }
}
