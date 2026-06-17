<?php
/**
 * ReliaWork2 JobFairPostModel
 * Manages job_fair_posts (BEDO advertisements) and job_fair_registrations.
 */

class JobFairPostModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Get a single published/active post with counts */
    public function find(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT p.*,
                    jfr.requested_date, jfr.venue AS fair_venue, jfr.title AS fair_title,
                    COUNT(DISTINCT pa.id) AS company_count,
                    COUNT(DISTINCT jv.id) AS vacancy_count,
                    SUM(jv.available_slots) AS total_slots
             FROM job_fair_posts p
             JOIN job_fair_requests jfr ON jfr.id = p.job_fair_request_id
             LEFT JOIN participating_agencies pa ON pa.job_fair_request_id = jfr.id AND pa.status = 'confirmed'
             LEFT JOIN job_vacancies jv ON jv.participating_agency_id = pa.id AND jv.status = 'open' AND jv.sl_status = 'accepted'
             WHERE p.id = ?
             GROUP BY p.id",
            [$id]
        );
    }

    /** All published posts for the job seeker to browse */
    public function getPublished(): array
    {
        return $this->db->fetchAll(
            "SELECT p.*,
                    jfr.requested_date, jfr.venue AS fair_venue,
                    COUNT(DISTINCT pa.id) AS company_count,
                    COUNT(DISTINCT jv.id) AS vacancy_count,
                    SUM(jv.available_slots) AS total_slots
             FROM job_fair_posts p
             JOIN job_fair_requests jfr ON jfr.id = p.job_fair_request_id
             LEFT JOIN participating_agencies pa ON pa.job_fair_request_id = jfr.id AND pa.status = 'confirmed'
             LEFT JOIN job_vacancies jv ON jv.participating_agency_id = pa.id AND jv.status = 'open'
             WHERE p.status = 'published'
             GROUP BY p.id
             ORDER BY p.event_date ASC, p.published_at DESC"
        );
    }

    /** Get companies + vacancies for a specific job fair post */
    public function getCompaniesAndVacancies(int $postId): array
    {
        $post = $this->db->fetch(
            "SELECT job_fair_request_id FROM job_fair_posts WHERE id = ?",
            [$postId]
        );
        if (!$post) return [];

        $fairId = $post['job_fair_request_id'];

        $companies = $this->db->fetchAll(
            "SELECT pa.id AS agency_id, pa.agency_name, pa.location, pa.email, pa.phone,
                    GROUP_CONCAT(CONCAT(jv.position,' (',jv.available_slots,' slots)') SEPARATOR ', ') AS vacancies_list,
                    COUNT(jv.id) AS vacancy_count,
                    SUM(jv.available_slots) AS total_slots
             FROM participating_agencies pa
             LEFT JOIN job_vacancies jv ON jv.participating_agency_id = pa.id AND jv.status = 'open'
             WHERE pa.job_fair_request_id = ? AND pa.status = 'confirmed'
             GROUP BY pa.id
             ORDER BY pa.agency_name",
            [$fairId]
        );

        $vacancies = $this->db->fetchAll(
            "SELECT jv.*, pa.agency_name, pa.location AS agency_location
             FROM job_vacancies jv
             JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             WHERE pa.job_fair_request_id = ? AND jv.status = 'open'
             ORDER BY pa.agency_name, jv.position",
            [$fairId]
        );

        return ['companies' => $companies, 'vacancies' => $vacancies];
    }

    /** Check if applicant already registered for this post */
    public function isRegistered(int $postId, int $applicantId): bool
    {
        return (bool)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM job_fair_registrations
             WHERE job_fair_post_id = ? AND applicant_id = ?",
            [$postId, $applicantId]
        );
    }

    /** Register applicant for job fair */
    public function register(int $postId, int $applicantId, int $userId): int
    {
        $this->db->execute(
            "INSERT INTO job_fair_registrations (job_fair_post_id, applicant_id, user_id, registered_at)
             VALUES (?, ?, ?, NOW())",
            [$postId, $applicantId, $userId]
        );
        return (int)$this->db->lastInsertId();
    }

    /** Get all registrations for a job fair post (for SL report) */
    public function getRegistrations(int $postId): array
    {
        return $this->db->fetchAll(
            "SELECT jfr.*, a.surname, a.firstname, a.middlename, a.suffix,
                    a.gsis_sss_no, a.pag_ibig_no, a.philhealth_no, a.disability,
                    a.date_of_birth, a.place_of_birth, a.sex, a.civil_status,
                    a.present_address, a.cellphone, a.email AS applicant_email,
                    a.preferred_occupation, a.educational_bg, a.work_experience,
                    a.other_skills, a.employment_status,
                    u.email AS user_email, u.name AS user_name
             FROM job_fair_registrations jfr
             JOIN applicants a ON a.id = jfr.applicant_id
             LEFT JOIN users u ON u.id = jfr.user_id
             WHERE jfr.job_fair_post_id = ?
             ORDER BY a.surname ASC, a.firstname ASC",
            [$postId]
        );
    }

    /** Get a single registration detail (for PDF) */
    public function getRegistrationDetail(int $postId, int $applicantId): array|false
    {
        return $this->db->fetch(
            "SELECT jfr.*, a.*,
                    p.title AS post_title, p.venue AS post_venue, p.event_date, p.event_time,
                    pfr.requested_date, pfr.venue AS fair_venue, pfr.title AS fair_title
             FROM job_fair_registrations jfr
             JOIN applicants a ON a.id = jfr.applicant_id
             JOIN job_fair_posts p ON p.id = jfr.job_fair_post_id
             JOIN job_fair_requests pfr ON pfr.id = p.job_fair_request_id
             WHERE jfr.job_fair_post_id = ? AND jfr.applicant_id = ?",
            [$postId, $applicantId]
        );
    }

    /** Count total registrations for a post */
    public function countRegistrations(int $postId): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM job_fair_registrations WHERE job_fair_post_id = ?",
            [$postId]
        );
    }
}
