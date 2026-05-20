<?php
/**
 * ReliaWork2 CompanyModel
 * Master list of companies/agencies that can be invited to job fairs.
 */

class CompanyModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): array|false
    {
        return $this->db->fetch("SELECT * FROM companies WHERE id = ?", [$id]);
    }

    public function findAll(array $filters = []): array
    {
        $sql    = "SELECT * FROM companies WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (name LIKE ? OR industry LIKE ? OR contact_person LIKE ?)";
            $like = '%' . $filters['search'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY name ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO companies (name, industry, contact_person, email, phone, address, status, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $data['name'],
                $data['industry']        ?? null,
                $data['contact_person']  ?? null,
                $data['email']           ?? null,
                $data['phone']           ?? null,
                $data['address']         ?? null,
                $data['status']          ?? 'active',
                $data['created_by']      ?? null,
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sets   = [];
        $params = [];
        $allowed = ['name','industry','contact_person','email','phone','address','status'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $sets[]   = "$f = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($sets)) return false;
        $params[] = $id;
        $this->db->execute("UPDATE companies SET " . implode(', ', $sets) . " WHERE id = ?", $params);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->db->execute("DELETE FROM companies WHERE id = ?", [$id]);
        return true;
    }

    public function countActive(): int
    {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM companies WHERE status = 'active'");
    }

    /**
     * Get companies NOT yet invited to a specific job fair request.
     */
    public function getNotInvited(int $requestId): array
    {
        return $this->db->fetchAll(
            "SELECT c.* FROM companies c
             WHERE c.status = 'active'
             AND c.id NOT IN (
                 SELECT pa.company_id FROM participating_agencies pa
                 WHERE pa.job_fair_request_id = ? AND pa.company_id IS NOT NULL
             )
             ORDER BY c.name ASC",
            [$requestId]
        );
    }
}
