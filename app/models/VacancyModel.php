<?php
/**
 * ReliaWork2 VacancyModel (job_vacancies)
 */

class VacancyModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT jv.*, pa.agency_name, pa.job_fair_request_id,
                    jfr.title AS job_fair_title
             FROM job_vacancies jv
             LEFT JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             WHERE jv.id = ?",
            [$id]
        );
    }

    public function findAll(array $filters = []): array
    {
        $sql    = "SELECT jv.*, pa.agency_name, pa.job_fair_request_id,
                          jfr.title AS job_fair_title
                   FROM job_vacancies jv
                   LEFT JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
                   LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
                   WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND jv.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['participating_agency_id'])) {
            $sql .= " AND jv.participating_agency_id = ?";
            $params[] = $filters['participating_agency_id'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (jv.position LIKE ? OR jv.company_name LIKE ?)";
            $like = '%' . $filters['search'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY jv.created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO job_vacancies 
             (participating_agency_id, company_name, company_location, mobile_number, gmail_address,
              position, available_slots, qualifications, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $data['participating_agency_id'],
                $data['company_name'],
                $data['company_location'] ?? null,
                $data['mobile_number'] ?? null,
                $data['gmail_address'] ?? null,
                $data['position'],
                $data['available_slots'] ?? 1,
                $data['qualifications'] ?? null,
                $data['status'] ?? 'open',
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sets   = [];
        $params = [];
        $allowed = ['company_name', 'company_location', 'mobile_number', 'gmail_address',
                    'position', 'available_slots', 'qualifications', 'status', 'remarks', 'remarks_by'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]   = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($sets)) return false;
        $params[] = $id;
        $this->db->execute("UPDATE job_vacancies SET " . implode(', ', $sets) . " WHERE id = ?", $params);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->db->execute("DELETE FROM job_vacancies WHERE id = ?", [$id]);
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

    public function countOpen(): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM job_vacancies WHERE status = 'open'"
        );
    }

    public function countTotal(): int
    {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM job_vacancies");
    }
}
