<?php
/**
 * ReliaWork2 AgencyModel (participating_agencies)
 */

class AgencyModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT pa.*, jfr.title AS job_fair_title
             FROM participating_agencies pa
             LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             WHERE pa.id = ?",
            [$id]
        );
    }

    public function findAll(array $filters = []): array
    {
        $sql    = "SELECT pa.*, jfr.title AS job_fair_title
                   FROM participating_agencies pa
                   LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
                   WHERE 1=1";
        $params = [];

        if (!empty($filters['job_fair_request_id'])) {
            $sql .= " AND pa.job_fair_request_id = ?";
            $params[] = $filters['job_fair_request_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND pa.status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY pa.invited_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        $cols = ['job_fair_request_id'];
        $placeholders = ['?'];
        $params = [$data['job_fair_request_id']];

        if (array_key_exists('user_id', $data) && $data['user_id'] !== null) {
            $cols[] = 'user_id';
            $placeholders[] = '?';
            $params[] = $data['user_id'];
        }
        if (array_key_exists('company_id', $data) && $data['company_id'] !== null) {
            $cols[] = 'company_id';
            $placeholders[] = '?';
            $params[] = $data['company_id'];
        }

        $cols = array_merge($cols, ['agency_name', 'contact_person', 'email', 'phone', 'address', 'status', 'invited_at']);
        $placeholders = array_merge($placeholders, ['?', '?', '?', '?', '?', "'invited'", 'NOW()']);

        $sql = "INSERT INTO participating_agencies (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";

        $values = [
            $data['agency_name'],
            $data['contact_person'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null,
        ];

        // Merge params (job_fair_request_id, optional user/company ids) with values for named columns
        $execParams = array_merge($params, $values);

        $this->db->execute($sql, $execParams);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Create a participating_agency record from a company master record.
     */
    public function createFromCompany(int $requestId, array $company, ?int $userId = null): int
    {
        $data = [
            'job_fair_request_id' => $requestId,
            'company_id' => $company['id'],
            'agency_name' => $company['name'],
            'contact_person' => $company['contact_person'] ?? null,
            'email' => $company['email'] ?? null,
            'phone' => $company['phone'] ?? null,
            'address' => $company['address'] ?? null,
            'user_id' => $userId,
        ];

        return $this->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $sets   = [];
        $params = [];
        $allowed = ['agency_name', 'contact_person', 'email', 'phone', 'address', 'status', 'responded_at', 'user_id', 'company_id'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]   = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($sets)) return false;
        $params[] = $id;
        $this->db->execute("UPDATE participating_agencies SET " . implode(', ', $sets) . " WHERE id = ?", $params);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->db->execute("DELETE FROM participating_agencies WHERE id = ?", [$id]);
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

    public function countTotal(): int
    {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM participating_agencies");
    }

    public function countConfirmed(): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM participating_agencies WHERE status = 'confirmed'"
        );
    }
}
