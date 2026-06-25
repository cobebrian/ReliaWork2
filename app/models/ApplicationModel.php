<?php
/**
 * ReliaWork2 ApplicationModel — Extended for full job fair application workflow
 */

class ApplicationModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT app.*,
                    a.surname, a.firstname, a.middlename, a.cellphone,
                    a.preferred_occupation, a.educational_bg, a.work_experience,
                    a.other_skills, a.validation_status AS applicant_validation,
                    u.email AS applicant_email,
                    jv.position, jv.company_name, jv.company_location,
                    jv.available_slots, jv.qualifications,
                    pa.agency_name, pa.job_fair_request_id,
                    jfr.title AS fair_title
             FROM applications app
             LEFT JOIN applicants a  ON a.id  = app.applicant_id
             LEFT JOIN users u       ON u.id  = a.user_id
             LEFT JOIN job_vacancies jv ON jv.id = app.job_vacancy_id
             LEFT JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             WHERE app.id = ?",
            [$id]
        );
    }

    public function findAll(array $filters = []): array
    {
        $sql    = "SELECT app.*,
                          a.surname, a.firstname, a.middlename,
                          u.email AS applicant_email,
                          jv.position, jv.company_name,
                          pa.agency_name,
                          jfr.title AS fair_title
                   FROM applications app
                   LEFT JOIN applicants a  ON a.id  = app.applicant_id
                   LEFT JOIN users u       ON u.id  = a.user_id
                   LEFT JOIN job_vacancies jv ON jv.id = app.job_vacancy_id
                   LEFT JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
                   LEFT JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
                   WHERE 1=1";
        $params = [];

        if (!empty($filters['applicant_id'])) {
            $sql .= " AND app.applicant_id = ?";
            $params[] = $filters['applicant_id'];
        }
        if (!empty($filters['job_vacancy_id'])) {
            $sql .= " AND app.job_vacancy_id = ?";
            $params[] = $filters['job_vacancy_id'];
        }
        if (!empty($filters['job_fair_post_id'])) {
            $sql .= " AND app.job_fair_post_id = ?";
            $params[] = $filters['job_fair_post_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND app.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['validation_status'])) {
            $sql .= " AND app.validation_status = ?";
            $params[] = $filters['validation_status'];
        }
        if (!empty($filters['agency_id'])) {
            $sql .= " AND pa.id = ?";
            $params[] = $filters['agency_id'];
        }

        $sql .= " ORDER BY app.applied_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO applications
             (applicant_id, job_vacancy_id, job_fair_post_id, job_fair_request_id,
              status, validation_status, notes, applied_at, updated_at)
             VALUES (?, ?, ?, ?, 'pending', 'pending_validation', ?, NOW(), NOW())",
            [
                $data['applicant_id'],
                $data['job_vacancy_id'],
                $data['job_fair_post_id']    ?? null,
                $data['job_fair_request_id'] ?? null,
                $data['notes']               ?? null,
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sets   = [];
        $params = [];
        $allowed = ['status', 'validation_status', 'validated_by', 'validated_at',
                    'validator_remarks', 'interview_id', 'notes'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]   = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($sets)) return false;
        $sets[]   = "updated_at = NOW()";
        $params[] = $id;
        $this->db->execute(
            "UPDATE applications SET " . implode(', ', $sets) . " WHERE id = ?",
            $params
        );
        return true;
    }

    public function delete(int $id): bool
    {
        $this->db->execute("DELETE FROM applications WHERE id = ?", [$id]);
        return true;
    }

    public function alreadyApplied(int $applicantId, int $vacancyId): bool
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM applications WHERE applicant_id = ? AND job_vacancy_id = ?",
            [$applicantId, $vacancyId]
        ) > 0;
    }

    public function countByApplicant(int $applicantId): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM applications WHERE applicant_id = ?",
            [$applicantId]
        );
    }

    public function getDocuments(int $applicationId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM application_documents WHERE application_id = ? ORDER BY doc_type, uploaded_at",
            [$applicationId]
        );
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
}
