<?php
/**
 * ReliaWork2 BedoController
 * BEDO Officer — compiles job fair info and posts advertisements to the public landing page.
 */

class BedoController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // GET /bedo/dashboard
    public function dashboard(): void
    {
        requireRole('bedo');

        $myPosts = $this->db->fetchAll(
            "SELECT p.*, jfr.title AS fair_title, jfr.requested_date, jfr.venue AS fair_venue
             FROM job_fair_posts p
             JOIN job_fair_requests jfr ON jfr.id = p.job_fair_request_id
             ORDER BY p.created_at DESC LIMIT 10"
        );

        $stats = [
            'published' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM job_fair_posts WHERE status='published'"),
            'draft'     => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM job_fair_posts WHERE status='draft'"),
            'total_vacancies' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM job_vacancies WHERE status='open'"),
            'upcoming_fairs'  => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM job_fair_requests WHERE status='approved' AND requested_date >= CURDATE()"
            ),
        ];

        $pageTitle = 'BEDO Officer Dashboard';
        include VIEW_PATH . '/bedo/dashboard.php';
    }

    // GET /bedo/compose — compose a new job fair advertisement
    public function compose(): void
    {
        requireRole('bedo');

        // Get all approved job fairs with their vacancies
        $jobFairs = $this->db->fetchAll(
            "SELECT jfr.*,
                    COUNT(DISTINCT pa.id) AS agency_count,
                    COUNT(DISTINCT jv.id) AS vacancy_count
             FROM job_fair_requests jfr
             LEFT JOIN participating_agencies pa ON pa.job_fair_request_id = jfr.id AND pa.status = 'confirmed'
             LEFT JOIN job_vacancies jv ON jv.participating_agency_id = pa.id AND jv.status = 'open'
             WHERE jfr.status = 'approved'
             GROUP BY jfr.id
             ORDER BY jfr.requested_date ASC"
        );

        $success = getFlash('success');
        $error   = getFlash('error');
        $pageTitle = 'Post Job Fair Advertisement';
        include VIEW_PATH . '/bedo/compose.php';
    }

    // GET /bedo/compose/preview/{jobFairId} — AJAX: get full info for a job fair
    public function previewJobFair(int $id): void
    {
        requireRole('bedo');
        header('Content-Type: application/json');

        $fair = $this->db->fetch(
            "SELECT * FROM job_fair_requests WHERE id = ? AND status = 'approved'",
            [$id]
        );
        if (!$fair) { echo json_encode(['error' => 'Not found']); exit; }

        // Agencies + vacancies
        $agencies = $this->db->fetchAll(
            "SELECT pa.agency_name, pa.email, pa.phone,
                    GROUP_CONCAT(CONCAT(jv.position, ' (', jv.available_slots, ' slots)') SEPARATOR ' | ') AS vacancies_summary,
                    COUNT(jv.id) AS vacancy_count,
                    SUM(jv.available_slots) AS total_slots
             FROM participating_agencies pa
             LEFT JOIN job_vacancies jv ON jv.participating_agency_id = pa.id AND jv.status = 'open'
             WHERE pa.job_fair_request_id = ? AND pa.status = 'confirmed'
             GROUP BY pa.id",
            [$id]
        );

        // All vacancies detail
        $vacancies = $this->db->fetchAll(
            "SELECT jv.*, pa.agency_name
             FROM job_vacancies jv
             JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             WHERE pa.job_fair_request_id = ? AND jv.status = 'open'
             ORDER BY pa.agency_name, jv.position",
            [$id]
        );

        echo json_encode([
            'fair'      => $fair,
            'agencies'  => $agencies,
            'vacancies' => $vacancies,
        ]);
        exit;
    }

    // POST /bedo/posts/store — publish the advertisement
    public function store(): void
    {
        requireRole('bedo');
        verifyCsrf();

        $jobFairId   = (int)($_POST['job_fair_request_id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $venue       = trim($_POST['venue'] ?? '');
        $eventDate   = trim($_POST['event_date'] ?? '');
        $eventTime   = trim($_POST['event_time'] ?? '');
        $status      = $_POST['status'] === 'published' ? 'published' : 'draft';

        if (!$jobFairId || empty($title)) {
            flash('error', 'Job fair and title are required.');
            redirect(APP_URL . '/bedo/compose');
        }

        $this->db->execute(
            "INSERT INTO job_fair_posts
             (job_fair_request_id, title, description, venue, event_date, event_time, status, created_by, published_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $jobFairId,
                $title,
                $description ?: null,
                $venue ?: null,
                $eventDate ?: null,
                $eventTime ?: null,
                $status,
                currentUser()['id'],
                $status === 'published' ? date('Y-m-d H:i:s') : null,
            ]
        );

        auditLog('bedo_post', 'bedo', "BEDO posted job fair ad: {$title} (status: {$status})");
        flash('success', $status === 'published'
            ? "Advertisement published to the landing page!"
            : "Advertisement saved as draft.");
        redirect(APP_URL . '/bedo/posts');
    }

    // GET /bedo/posts — list BEDO's posts
    public function posts(): void
    {
        requireRole('bedo');

        $posts = $this->db->fetchAll(
            "SELECT p.*, jfr.title AS fair_title
             FROM job_fair_posts p
             JOIN job_fair_requests jfr ON jfr.id = p.job_fair_request_id
             ORDER BY p.created_at DESC"
        );

        $success = getFlash('success');
        $pageTitle = 'My Advertisements';
        include VIEW_PATH . '/bedo/posts.php';
    }

    // POST /bedo/posts/{id}/publish
    public function publish(int $id): void
    {
        requireRole('bedo');
        verifyCsrf();
        $this->db->execute(
            "UPDATE job_fair_posts SET status='published', published_at=NOW() WHERE id=?",
            [$id]
        );
        flash('success', 'Post published to landing page.');
        redirect(APP_URL . '/bedo/posts');
    }

    // POST /bedo/posts/{id}/delete
    public function deletePost(int $id): void
    {
        requireRole('bedo');
        verifyCsrf();
        $this->db->execute("DELETE FROM job_fair_posts WHERE id=?", [$id]);
        flash('success', 'Post deleted.');
        redirect(APP_URL . '/bedo/posts');
    }

    // ── PUBLIC LANDING PAGE ───────────────────────────────────────────────────

    // GET / (public landing page)
    public static function landingPage(): void
    {
        $db = Database::getInstance();

        // Published job fair posts with all details
        $posts = $db->fetchAll(
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
             ORDER BY p.published_at DESC"
        );

        // Recent announcements
        $announcements = $db->fetchAll(
            "SELECT * FROM announcements WHERE status='published' ORDER BY created_at DESC LIMIT 5"
        );

        // All open vacancies for the wall
        $vacancies = $db->fetchAll(
            "SELECT jv.*, pa.agency_name,
                    jfr.title AS job_fair_title, jfr.requested_date, jfr.venue
             FROM job_vacancies jv
             JOIN participating_agencies pa ON pa.id = jv.participating_agency_id
             JOIN job_fair_requests jfr ON jfr.id = pa.job_fair_request_id
             WHERE jv.status = 'open' AND jfr.status = 'approved'
             ORDER BY jv.created_at DESC
             LIMIT 20"
        );

        // Stats for hero section
        $stats = [
            'upcoming_fairs' => (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM job_fair_requests WHERE status='approved' AND requested_date >= CURDATE()"
            ),
            'total_vacancies' => (int)$db->fetchColumn("SELECT COUNT(*) FROM job_vacancies WHERE status='open'"),
            'total_companies' => (int)$db->fetchColumn("SELECT COUNT(*) FROM participating_agencies WHERE status='confirmed'"),
        ];

        include VIEW_PATH . '/landing.php';
    }
}
