<?php
/**
 * ReliaWork2 ApplicantModel
 */

class ApplicantModel
{
    private Database $db;

    // All NSRP Form 1 fields allowed for create/update
    private const NSRP_FIELDS = [
        'surname', 'firstname', 'middlename', 'suffix',
        'date_of_birth', 'place_of_birth', 'sex', 'religion', 'civil_status',
        'present_address', 'height', 'tin',
        'email', 'landline', 'cellphone',
        'gsis_sss_no', 'pag_ibig_no', 'philhealth_no', 'disability',
        'employment_status', 'actively_looking', 'willing_immediate',
        'is_4ps', 'household_id',
        'preferred_occupation', 'preferred_location', 'expected_salary',
        'passport_no', 'educational_bg', 'trainings', 'eligibility',
        'work_experience', 'other_skills',
        // Validation workflow
        'validation_status', 'validated_by', 'validated_at', 'validator_remarks',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT a.*, u.name AS user_name, u.email AS user_email
             FROM applicants a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.id = ?",
            [$id]
        );
    }

    public function findByUserId(int $userId): array|false
    {
        return $this->db->fetch(
            "SELECT a.*, u.name AS user_name, u.email AS user_email
             FROM applicants a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.user_id = ?",
            [$userId]
        );
    }

    public function findAll(array $filters = []): array
    {
        $sql    = "SELECT a.*, u.name AS user_name, u.email AS user_email
                   FROM applicants a
                   LEFT JOIN users u ON u.id = a.user_id
                   WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (a.surname LIKE ? OR a.firstname LIKE ? OR u.email LIKE ?)";
            $like = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$like, $like, $like]);
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
        $cols   = ['user_id'];
        $vals   = [$data['user_id']];
        $placeholders = ['?'];

        foreach (self::NSRP_FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                $cols[]         = $field;
                $vals[]         = $data[$field];
                $placeholders[] = '?';
            }
        }

        // Legacy: map disability_status → disability
        if (array_key_exists('disability_status', $data) && !array_key_exists('disability', $data)) {
            $cols[]         = 'disability';
            $vals[]         = $data['disability_status'];
            $placeholders[] = '?';
        }

        $cols[]         = 'created_at';
        $vals[]         = date('Y-m-d H:i:s');
        $placeholders[] = 'NOW()';
        $cols[]         = 'updated_at';
        $vals[]         = date('Y-m-d H:i:s');
        $placeholders[] = 'NOW()';

        // Remove the manual date strings we added; use SQL NOW() instead
        array_pop($vals); // updated_at placeholder
        array_pop($vals); // created_at placeholder

        $colList   = implode(', ', $cols);
        $phList    = implode(', ', $placeholders);

        $this->db->execute(
            "INSERT INTO applicants ({$colList}) VALUES ({$phList})",
            $vals
        );
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sets   = [];
        $params = [];

        foreach (self::NSRP_FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]   = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        // Legacy
        if (array_key_exists('disability_status', $data) && !array_key_exists('disability', $data)) {
            $sets[]   = "disability = ?";
            $params[] = $data['disability_status'];
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
