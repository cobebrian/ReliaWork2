<?php
/**
 * ReliaWork2 ResourceModel (barangay_resources + resource_allocations)
 */

class ResourceModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT * FROM barangay_resources WHERE id = ?",
            [$id]
        );
    }

    public function findAll(array $filters = []): array
    {
        $sql    = "SELECT * FROM barangay_resources WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY name ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO barangay_resources (name, description, quantity, unit, status, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())",
            [
                $data['name'],
                $data['description'] ?? null,
                $data['quantity'] ?? 0,
                $data['unit'] ?? null,
                $data['status'] ?? 'available',
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sets   = [];
        $params = [];
        $allowed = ['name', 'description', 'quantity', 'unit', 'status'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]   = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($sets)) return false;
        $params[] = $id;
        $this->db->execute("UPDATE barangay_resources SET " . implode(', ', $sets) . " WHERE id = ?", $params);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->db->execute("DELETE FROM barangay_resources WHERE id = ?", [$id]);
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

    // ── Allocations ───────────────────────────────────────────────────────────

    public function allocate(array $data): int
    {
        $this->db->execute(
            "INSERT INTO resource_allocations 
             (job_fair_request_id, resource_id, quantity_allocated, notes, allocated_by, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())",
            [
                $data['job_fair_request_id'],
                $data['resource_id'],
                $data['quantity_allocated'] ?? 1,
                $data['notes'] ?? null,
                $data['allocated_by'],
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    public function getAllocations(int $jobFairRequestId): array
    {
        return $this->db->fetchAll(
            "SELECT ra.*, br.name AS resource_name, br.unit, u.name AS allocated_by_name
             FROM resource_allocations ra
             LEFT JOIN barangay_resources br ON br.id = ra.resource_id
             LEFT JOIN users u ON u.id = ra.allocated_by
             WHERE ra.job_fair_request_id = ?
             ORDER BY ra.created_at DESC",
            [$jobFairRequestId]
        );
    }

    public function countAvailable(): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM barangay_resources WHERE status = 'available'"
        );
    }
}
