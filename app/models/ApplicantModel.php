<?php
/**
 * ReliaWork2 ApplicantModel
 */

class ApplicantModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT a.*, u.name, u.email
             FROM applicants a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.id = ?",
            [$id]
        );
    }

    public function findByUserId(int $userId): array|false
    {
        return $this->db->fetch(
            "SELECT a.*, u.name, u.email
             FROM applicants a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.user_id = ?",
            [$userId]
        );
    }

    public function findAll(array $filters = []): array
    {
        $sql    = "SELECT a.*, u.name AS user_name, u.email
                   FROM applicants a
                   LEFT JOIN users u ON u.id = a.user_id
                   WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (a.surname LIKE ? OR a.firstname LIKE ? OR u.email LIKE ?)";
            $like = '%' . $filters['search'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if (!empty($filters['job_fair_request_id'])) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM applications app
                JOIN job_vacancies jv ON jv.id = app.job_vacancy_id
                JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
                WHERE app.applicant_id = a.id AND pa.job_fair_request_id = ?
            )";
            $params[] = $filters['job_fair_request_id'];
        }

        $sql .= " ORDER BY a.surname ASC, a.firstname ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO applicants 
             (user_id, surname, firstname, middlename, gsis_sss_no, pag_ibig_no, philhealth_no, disability_status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $data['user_id'],
                $data['surname'],
                $data['firstname'],
                $data['middlename'] ?? null,
                $data['gsis_sss_no'] ?? null,
                $data['pag_ibig_no'] ?? null,
                $data['philhealth_no'] ?? null,
                $data['disability_status'] ?? 'none',
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sets   = [];
        $params = [];
        $allowed = ['surname', 'firstname', 'middlename', 'gsis_sss_no', 'pag_ibig_no', 'philhealth_no', 'disability_status'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]   = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($sets)) return false;
        $sets[]   = "updated_at = NOW()";
        $params[] = $id;
        $this->db->execute("UPDATE applicants SET " . implode(', ', $sets) . " WHERE id = ?", $params);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->db->execute("DELETE FROM applicants WHERE id = ?", [$id]);
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
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM applicants");
    }
}
